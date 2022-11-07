<?php

class AuthHelper {
    /**
     * INICIA LA SESIÓN, EN CASO DE QUE NO ESTÉ YA ACTIVA
     */
    public static function openSession() {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }  
    }

    /**
     * Devuelve nombre del usuario logueado
     */
    public static function getUser(){
        if (AuthHelper::isAdmin()){
            return $_SESSION['USER_USER'];
        }
        return null;
    }
    /**
     * Devuelve booleano informando si es admin o no
     */
    public static function isAdmin() {
        AuthHelper::openSession();
        return isset($_SESSION['USER_ID']);
    }

     /**
     * Verifica que el user este logueado y si no lo está
     * lo redirige al login.
     */
    public static function checkLoggedIn() {
        If (!AuthHelper::isAdmin()){
            header("Location: " . BASE_URL . 'login');
            die();
        };
    }

    
}