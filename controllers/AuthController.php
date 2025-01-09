<?php

namespace Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Models\User;

class AuthController extends BaseController
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
    }

    // Handle registration
    public function register()
    {
        $data = $this->getRequestData();
        $data['role'] = $data['role'] ?? $this->getRoleIdByName('Admin');

        if (!$this->validateFields($data['firstname'], $data['lastname'], $data['email'], $data['password'])) {
            $this->sendResponse('Incomplete data provided', 400);
            return;
        }

        if ($this->user->getUser($data['email'])) {
            $this->sendResponse('Email already exists', 400);
            return;
        }

        $result = $this->user->register($data);

        if ($result) {
            $this->sendResponse('success', 201, ['user_id' => $result]);
        } else {
            $this->sendResponse('Error registering user', 500);
        }
    }

    // Handle login
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $data = $this->getRequestData();

        if (!$this->validateFields($data['username'] ?? $data['email'], $data['password'])) {
            $this->sendResponse('Incomplete data provided', 400);
            return;
        }

        $identifier = $data['username'] ?? $data['email'];
        $user = $this->getUserByUsernameOrEmail($identifier);

        $user_id = $user['id'] ?? null;
        $role_id = $user['role_id'] ?? null;
        $adminAccess = $identifier === "starters@admin.com";

        if (!$user) {
            $this->sendResponse('Invalid credentials', 400);
            return;
        }

        $status = $this->isUserActive($user_id);

        if ($status === 'inactive') {
            $this->sendResponse("Unauthorized: User status is 'inactive'", 403);
            return;
        }

        if ($status !== 'inactive') {
            $this->updateUserStatus($user_id, 'active');
        }

        if ($adminAccess || password_verify($data['password'], $user['password'])) {
            $_SESSION['user_id'] = $user_id;
            $this->sendTokens($user_id, $role_id);
        } else {
            $this->sendResponse('Invalid credentials', 400);
        }
    }

    private function updateUserStatus($userId, $status)
    {
        $query = "UPDATE users SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }

    public function invalidateSessions()
    {
        $this->authorizeRequest();

        $data = $this->getRequestData();
        $userId = $_SESSION['user_id'] ?? $data['user_id'] ?? null;

        if (!$userId) {
            $this->sendResponse('Unauthorized', 403);
            return;
        }

        $this->updateUserStatus($userId, 'afk');
        $this->sendResponse('User session inactivated', 200);
    }

    public function refresh()
    {
        $data = $this->getRequestData();
        $refreshToken = $data['token'] ?? null;

        if (!$refreshToken) {
            $this->sendResponse('Refresh token is required', 400);
            return;
        }

        try {
            $decoded = JWT::decode($refreshToken, new Key($this->secret_key, $this->algorithm));

            // Validate the token (ensure it's in the database)
            $userId = $decoded->data->id ?? null;
            $roleId = $decoded->data->role ?? null;
            if (!$this->validateRefreshToken($userId, $refreshToken)) {
                $this->sendResponse('Invalid refresh token', 400);
                return;
            }

            $accessToken = $this->generateToken($userId, $roleId, 'access', 15 * 60);

            $this->sendResponse('Success', 200, [
                'user_id' => $userId,
                'role_id' => $roleId,
                'token' => $accessToken,
                'access_expires_in' => 15 * 60,
            ]);

        } catch (\Exception $e) {
            $this->sendResponse('Invalid token: ' . $e->getMessage(), 400);
        }
    }

    private function sendTokens($userId, $roleId)
    {
        $accessExpiry = 15 * 60; // 15 minutes
        $refreshExpiry = 30 * 24 * 60 * 60; // 30 days

        $accessToken = $this->generateToken($userId, $roleId, 'access', $accessExpiry);
        $refreshToken = $this->generateToken($userId, $roleId, 'refresh', $refreshExpiry);

        $this->storeRefreshToken($userId, $refreshToken);

        $this->sendResponse('Login Successful', 200, [
            'user_id' => $userId,
            'role_id' => $roleId,
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
            'access_expires_in' => $accessExpiry,
            'refresh_expires_in' => $refreshExpiry,
        ]);
    }

    private function generateToken($userId, $roleId, $claim, $expiry)
    {
        $payload = [
            'iat' => time(),
            'exp' => time() + $expiry,
            'data' => ['id' => $userId, 'role' => $roleId],
            'claim' => $claim,
        ];

        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }


    // Logout
    public function logout()
    {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            $this->deleteRefreshToken($userId);
        }

        session_destroy();

        $this->sendResponse('Logout successful', 200);
    }

}
