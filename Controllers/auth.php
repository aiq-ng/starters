<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Model/user.php';
require_once __DIR__ . '/../config.php';
use Firebase\JWT\JWT;

class AuthController {
    private $user;
    private $config;

    public function __construct() {
        $this->user = new User();
        $this->config = include(__DIR__ . '/../config.php');
    }

    // Handle registration
    public function register(){
        $json = file_get_contents('php://input');
        $data = json_decode($json, true); 

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $data['username'] ?? null;
            $email = $data['email'] ?? null;
            $password = $data['password'] ?? null;


            // Validate required fields
            if ($username && $email && $password) {
                // Register the user
                if ($this->user->register($username, $email, $password)) {
                    // Send a JSON response
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'User registered successfully'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Error registering user'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Incomplete data provided'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
        }
    }

    // Handle login
     public function login() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $data['username'] ?? null;
            $password = $data['password'] ?? null;

            if ($username && $password) {
                // Authenticate the user
                if ($user = $this->user->login($username, $password)) {
                    // Generate a JWT token
                    $token = [
                        'iss' => $this->config['iss'],
                        'aud' => $this->config['aud'],
                        'iat' => $this->config['iat'],
                        'exp' => $this->config['exp'],
                        'data' => [
                            'id' => $user['id'],
                            'username' => $user['username']
                        ]
                        ]; 
                        $jwt = JWT::encode($token, $this->config['secret_key'], 'HS256');


                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    
                    // Send a JSON response
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'user_id' => $user['id'],
                        'token' => $jwt
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid username or password'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Incomplete data provided'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid request method'
            ]);
        }
    }

    // Logout
    public function logout() {
        session_start();
        session_destroy();

        echo json_encode([
            'status' => 'success',
            'message' => 'Logout successful'
        ]);
    }
}
