<?php

require_once "./app/models/user_model.php";
require_once "./app/views/auth_view.php";


class AuthController {
    private $model;
    private $view;

    function __construct() {
        $this->view = new AuthView();
        $this->model = new UserModel();

        AuthHelper::openSession();
    }

    function login() {
        if (isset($_SESSION['USER_ID'])) { 
            header("location: " . BASE_URL);
            return;
        }
        if (isset($_POST['error'])) {
            $this->view->showLoginForm($_POST['error']);
        }
        if (isset($_POST['user']) && isset($_POST['password'])) {
            $this->validate();
            return;
        }

        $this->view->showLoginForm();
    }

    private function validate() {
        $user = $this->model->getBy("user", $_POST['user']);
        if ($user && password_verify($_POST['password'], $user->password)) {
            //session_start(); 
            $_SESSION['USER_ID'] = $user->id;
            $_SESSION['USER_USER'] = $user->user;
            $_SESSION['USER_LOGGED'] = true;

            header("Location: " . BASE_URL);
        } else {
            $this->loginFail();
        }
    }

    private function loginFail() {
        $this->view->showLoginForm("Usuario o contraseña inválido.");
    }

    public function logout() {
        //session_start();
        session_destroy();
        header("Location: " . BASE_URL);
    }
}