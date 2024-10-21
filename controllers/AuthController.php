<?php

namespace Controllers;

require_once __DIR__ . '/../config.php';
use Firebase\JWT\JWT;
use Models\User;

class AuthController extends BaseController
{
    private $user;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->user = new User();
        $this->config = include(__DIR__ . '/../config.php');
    }

    // Handle registration
    public function register()
    {
        $data = $this->getRequestData();

        if (!$this->validateFields($data['name'], $data['email'], $data['password'])) {
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

        if (!$this->validateFields($data['email'], $data['password'])) {
            $this->sendResponse('Incomplete data provided', 400);
            return;
        }

        $user = $this->user->getUser($data['email']);

        if ($user && password_verify($data['password'], $user['password'])) {

            $token = [
                'iss' => $this->config['iss'],
                'aud' => $this->config['aud'],
                'iat' => $this->config['iat'],
                'exp' => $this->config['exp'],
                'data' => [
                    'id' => $user['id'],
                    'email' => $user['email']
                ]
            ];
            $jwt = JWT::encode($token, $this->config['secret_key'], 'HS256');

            $_SESSION['user_id'] = $user['id'];

            $this->sendResponse('Login successful', 200, [
                'user_id' => $user['id'],
                'token' => $jwt
            ]);
        } else {
            $this->sendResponse('Invalid credentials', 400);
        }
    }

    // Logout
    public function logout()
    {
        session_start();
        session_destroy();

        $this->sendResponse('Logout successful', 200);
    }

    public function getRoles()
    {
        $result = $this->fetchRoles();

        $this->sendResponse('success', 200, $result);
    }
}
