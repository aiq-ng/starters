#!/usr/bin/env python3
from decouple import config as ENV
from fabric import Connection
from invoke import Responder

"""""" """""" """""" """""" """""" """""" """""" """""" """""" """""" """"""
"""""" """""" """""" """"" CONFIGURATIONS """ """""" """""" """""" """"""
"""""" """""" """""" """""" """""" """""" """""" """""" """""" """""" """"""

REMOTE_DIR = "/root/"
GIT_URL = ENV("GIT_URL")
GIT_TOKEN = ENV("GIT_TOKEN")
GIT_USER = ENV("GIT_USER")
ENVIRONMENT = ENV("ENVIRONMENT")
DEPLOYMENT = ENV("DEPLOYMENT", default="make")

prefix = f"https://{GIT_USER}:{GIT_TOKEN}@"
suffix = GIT_URL.split("https://")[-1]
AUTH_GIT_URL = prefix + suffix

GIT_DIR = REMOTE_DIR + GIT_URL.split("/")[-1].split(".")[0]
GIT_SUBDIR = GIT_DIR + ""
REMOTE_USER = ENV("REMOTE_USER")
REMOTE_HOST = ENV("REMOTE_HOST")
# DOT_ENV = ENV("DOT_ENV")
# SSL_CRT = ENV("SSL_CRT")
# SSL_KEY = ENV("SSL_KEY")
# LOAD_BALANCER_DNS = ENV("LOAD_BALANCER_DNS")

"""""" """""" """""" """""" """""" """""" """""" """""" """""" """""" """"""


def install_dependencies(conn):
    """Install required dependencies on the remote host."""
    result = conn.run("which git", warn=True, hide=True)
    if result.stdout.strip():
        print("------------Git already installed--------------")
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

    print("--------------Dependencies installed---------------")


def install_docker(conn):
    """Install Docker on the remote host."""
    result = conn.run("which docker", warn=True, hide=True)
    if result.stdout.strip():
        print("------------Docker already installed--------------")
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
    print("--------------Docker installed---------------")


def clone_repo(conn):
    """Clone the repository and prepare the environment."""
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
        print("--------------Cloning the repository---------------")
        conn.run(f"git clone {AUTH_GIT_URL}", pty=True, watchers=[promptpass])

    conn.run(f"git config --global --add safe.directory {GIT_DIR}")
    conn.run(f"sudo chown -R $(whoami) {GIT_DIR}")
    with conn.cd(GIT_SUBDIR):
        conn.run("git fetch origin && git reset --hard origin/main")
    print("--------------Repository cloned & Up-To-Date---------------")

    # conn.run(f'echo "{DOT_ENV}" > {GIT_SUBDIR}.env')
    # print("--------------.env file created---------------")


def setup_ssl(conn):
    """Setup SSL certificate on the remote host."""
    conn.run(f'echo "{SSL_CRT}" > {GIT_SUBDIR}nginx/selfsigned.crt')
    conn.run(f'echo "{SSL_KEY}" > {GIT_SUBDIR}nginx/selfsigned.key')
    print("--------------SSL certificate installed---------------")


def deploy(conn, profile=None):
    """Deploy the application with an optional Docker Compose profile."""
    with conn.cd(GIT_SUBDIR):
        conn.run("export COMPOSE_BAKE=true")

        if DEPLOYMENT == "make":
            conn.run(f"sudo make {ENVIRONMENT}")
        elif DEPLOYMENT == "profile":

            if profile:
                conn.run(
                    f"sudo docker compose --profile {profile} up --build -d"
                )
            else:
                conn.run("sudo docker compose up --build -d")
            conn.run("sudo docker image prune -af")
    print("--------------Application deployed---------------")


def handle_connection(host):
    conn = Connection(
        host=host,
        user=REMOTE_USER,
    )
    result = conn.run("hostname", hide=True)
    print(
        f"== == == == == == == == == == == == == == == == == == == ==\n"
        f"Connected to {host}, hostname: {result.stdout.strip()}\n"
        f"== == == == == == == == == == == == == == == ==  == == == =="
    )
    install_dependencies(conn)
    install_docker(conn)
    clone_repo(conn)
    # setup_ssl(conn)
    deploy(conn, profile="prod")


if __name__ == "__main__":
    handle_connection(REMOTE_HOST)
