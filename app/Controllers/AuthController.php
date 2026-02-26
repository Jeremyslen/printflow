<?php
require_once __DIR__ . '/../Models/Usuario.php';

class AuthController {

    public function showLogin() {
        return [
            'view' => __DIR__ . '/../Views/auth/login.php',
            'vars' => ['error' => null]
        ];
    }

    public function login() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            return [
                'view' => __DIR__ . '/../Views/auth/login.php',
                'vars' => ['error' => 'Completa todos los campos.']
            ];
        }

        $model   = new Usuario();
        $usuario = $model->findByUsername($username);

        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_name'] = $usuario['username'];
            header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/PrintFlow/public/index.php?controller=home&action=index');
            exit;
        }

        return [
            'view' => __DIR__ . '/../Views/auth/login.php',
            'vars' => ['error' => 'Usuario o contraseña incorrectos.']
        ];
    }

    public function logout() {
        session_destroy();
        header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/PrintFlow/public/login');
        exit;
    }
}