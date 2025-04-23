#!/usr/bin/env python3
import os
from pathlib import Path

from fabric import Connection
from invoke import Responder

"""""" """""" """""" """""" """""" """""" """""" """""" """""" """""" """"""
"""""" """""" """""" """"" CONFIGURATIONS """ """""" """""" """""" """"""
"""""" """""" """""" """""" """""" """""" """""" """""" """""" """""" """"""
GIT_URL = os.getenv("GIT_URL", "")
GIT_TOKEN = os.getenv("GIT_TOKEN", "")
GIT_USER = os.getenv("GIT_USER", "")
ENVIRONMENT = os.getenv("ENVIRONMENT", "dev")
DEPLOYMENT = os.getenv("DEPLOYMENT", "make")
REMOTE_USER = os.getenv("REMOTE_USER", "root")
REMOTE_HOST = os.getenv("REMOTE_HOST", "127.0.0.1")
REMOTE_DIR = os.getenv("REMOTE_DIR", "/root/")
SSH_KEY_PATH = os.getenv("SSH_KEY_PATH")

prefix = f"https://{GIT_USER}:{GIT_TOKEN}@"
suffix = GIT_URL.split("https://")[-1]
AUTH_GIT_URL = prefix + suffix

PROJECT_NAME = GIT_URL.split("/")[-1].split(".")[0]
GIT_DIR = os.path.join(REMOTE_DIR, PROJECT_NAME)
GIT_SUBDIR = os.path.join(GIT_DIR, "")

"""""" """""" """""" """""" """""" """""" """""" """""" """""" """""" """"""


def push_env_files():
    project_root = Path.cwd()
    project_name = project_root.name
    remote_base = f"/etc/{project_name}"

    conn_kwargs = {
        "host": REMOTE_HOST,
        "user": REMOTE_USER,
    }
    if SSH_KEY_PATH:
        conn_kwargs["connect_kwargs"] = {"key_filename": SSH_KEY_PATH}

    conn = Connection(**conn_kwargs)

    print(f"============== Syncing env files to {remote_base} ==============")
    conn.run(f"sudo mkdir -p {remote_base}")
    conn.run(f"sudo chown -R $(whoami) {remote_base}")

    def sync_env_file(local_env_path: Path, relative_to: Path):
        remote_path = Path(remote_base) / local_env_path.parent.relative_to(
            relative_to
        )
        conn.run(f"mkdir -p {remote_path}")
        print(f"======= Pushing {local_env_path} to {remote_path}/.env")
        conn.put(str(local_env_path), remote=f"{remote_path}/.env")

    def sync_profiles(profiles_path: Path, relative_to: Path):
        is_root_profiles = profiles_path.parent == relative_to
        base_remote = Path(remote_base) / (
            "profiles"
            if is_root_profiles
            else profiles_path.parent.relative_to(relative_to) / "profiles"
        )

        print(f"======= Processing profiles in {profiles_path}")
        for profile in profiles_path.iterdir():
            if profile.is_dir():
                remote_profile_path = base_remote / profile.name
                conn.run(f"mkdir -p {remote_profile_path}")
                for env_file in profile.glob("*.env.*"):
                    print(f"=== Pushing {env_file} to {remote_profile_path}/")
                    conn.put(
                        str(env_file),
                        remote=f"{remote_profile_path}/{env_file.name}",
                    )

    for local_dir in [project_root] + list(project_root.rglob("*")):
        if not local_dir.is_dir():
            continue

        local_env = local_dir / ".env"
        if local_env.exists():
            sync_env_file(local_env, project_root)

        profiles_dir = local_dir / "profiles"
        if profiles_dir.is_dir():
            sync_profiles(profiles_dir, project_root)

    print("================= Env files pushed successfully =================")


def install_dependencies(conn):
    result = conn.run("which git", warn=True, hide=True)
    if result.stdout.strip():
        print("======= Git already installed =======")
        return

    INSTALL = "sudo apt-get install -y"
    UPDATE = "sudo apt-get update"

    conn.run(f"{UPDATE}")
    conn.run(f"{INSTALL} git")
    conn.run(f"{INSTALL} python3-pip")
    conn.run(f"{INSTALL} python3-dev")
    conn.run(f"{INSTALL} build-essential")
    conn.run(f"{INSTALL} libssl-dev")
    conn.run(f"{INSTALL} libffi-dev")
    conn.run(f"{INSTALL} make")

    print("======= Dependencies installed =======")


def install_docker(conn):
    result = conn.run("which docker", warn=True, hide=True)
    if result.stdout.strip():
        print("======= Docker already installed =======")
        return

    INSTALL = "sudo apt-get install -y"
    UPDATE = "sudo apt-get update"

    conn.run(f"{UPDATE}")
    conn.run(
        f"{INSTALL} apt-transport-https ca-certificates curl "
        f"software-properties-common"
    )

    conn.run(
        "curl -fsSL https://download.docker.com/linux/ubuntu/gpg | "
        "sudo apt-key add -"
    )
    conn.run(
        'sudo add-apt-repository "deb [arch=amd64] '
        'https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"'
    )
    conn.run(f"{UPDATE}")
    conn.run(f"{INSTALL} docker-ce")

    conn.run(
        "sudo curl -L "
        '"https://github.com/docker/compose/releases/download/1.29.2/'
        'docker-compose-$(uname -s)-$(uname -m)" '
        "-o /usr/local/bin/docker-compose"
    )
    conn.run("sudo chmod +x /usr/local/bin/docker-compose")
    conn.run("sudo usermod -aG docker ${USER}")
    conn.run("sudo systemctl enable docker")
    conn.run("sudo systemctl start docker")
    print("======= Docker installed =======")


def clone_repo(conn):
    promptpass = Responder(
        pattern=r"Are you sure you want to continue connecting "
        r"\(yes/no/\[fingerprint\]\)\?",
        response="yes\n",
    )

    result = conn.run(
        f'test -d {GIT_DIR} && echo "exists" || echo "not exists"',
        hide=True,
    )

    if "not exists" in result.stdout:
        print("======= Cloning the repository =======")
        conn.run(f"git clone {AUTH_GIT_URL}", pty=True, watchers=[promptpass])

    conn.run(f"git config --global --add safe.directory {GIT_DIR}")
    conn.run(f"sudo chown -R $(whoami) {GIT_DIR}")

    with conn.cd(GIT_SUBDIR):
        if ENVIRONMENT in ["prod", "production"]:
            result = conn.run("git branch -r", hide=True)
            remote_branches = result.stdout.strip().splitlines()

            if "origin/main" in remote_branches:
                branch_name = "main"
            elif "origin/master" in remote_branches:
                branch_name = "master"
            else:
                raise Exception(
                    "Neither 'origin/main' nor 'origin/master' found in repo"
                )
        else:
            branch_name = ENVIRONMENT

        current_branch = conn.run(
            "git rev-parse --abbrev-ref HEAD", hide=True
        ).stdout.strip()
        print(f"=== Current branch: {current_branch} ==")

        if current_branch != branch_name:
            print(f"Switching to branch {branch_name}...")
            conn.run(f"git checkout {branch_name}")

        conn.run(f"git fetch origin && git reset --hard origin/{branch_name}")

    print(
        f"=== Repository cloned & checked out to {branch_name} branch ======="
    )


def symlink_env(conn):
    project_name = PROJECT_NAME
    remote_profiles_base = f"/etc/{project_name}"
    root_env_file = f"{remote_profiles_base}/.env"

    print(f"Creating symlinks from {remote_profiles_base} to {GIT_SUBDIR}/")

    if conn.run(f"test -f {root_env_file}", warn=True).ok:
        print(f"Creating symlink for {root_env_file} to {GIT_SUBDIR}/.env")
        conn.run(f"ln -sfn {root_env_file} {GIT_SUBDIR}/.env")

    result = conn.run(
        f"find {remote_profiles_base} -type f -name '*.env.*'",
        hide=True,
    )
    env_files = result.stdout.strip().splitlines()

    for env_file in env_files:
        relative_path = Path(env_file).relative_to(remote_profiles_base)
        local_target_path = Path(GIT_SUBDIR) / relative_path

        print(f"Creating symlink for {env_file} → {local_target_path}")

        conn.run(f"mkdir -p {local_target_path.parent}")

        conn.run(f"ln -sfn {env_file} {local_target_path}")

    print("======= Symlinks created successfully =======")


def deploy(conn, profile=None):
    with conn.cd(GIT_SUBDIR):
        conn.run("export COMPOSE_BAKE=true")

        if DEPLOYMENT == "make":
            conn.run(f"sudo make {ENVIRONMENT}")
        elif DEPLOYMENT == "profile":
            if profile:
                conn.run(
                    f"sudo docker compose --profile {profile} " "up --build -d"
                )
            else:
                conn.run("sudo docker compose up --build -d")
            conn.run("sudo docker image prune -f")
        else:
            conn.run("sudo docker compose up --build -d")

    print("======= Application deployed =======")


def handle_connection(host):
    conn = Connection(
        host=host,
        user=REMOTE_USER,
    )
    result = conn.run("hostname", hide=True)
    print(
        f"======= Connected to {host}, "
        f"hostname: {result.stdout.strip()} ======="
    )
    install_dependencies(conn)
    install_docker(conn)
    clone_repo(conn)
    symlink_env(conn)
    deploy(conn, profile="prod")


if __name__ == "__main__":
    import sys

    if "push-env" in sys.argv:
        push_env_files()
    else:
        handle_connection(REMOTE_HOST)
