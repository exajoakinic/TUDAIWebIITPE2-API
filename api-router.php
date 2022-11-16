<?php
require_once './libs/router.php';
require_once './app/controllers/book.controller.php';
require_once './app/controllers/author.controller.php';
require_once './app/controllers/genre.controller.php';

// crea el router
$router = new Router();

// defino la tabla de ruteo
$endpoints[]= ['endpoint'=>'books', 'controller' => 'BookController', 'ABM' => true];
$endpoints[]= ['endpoint'=>'authors', 'controller' => 'AuthorController', 'ABM' => true];
$endpoints[]= ['endpoint'=>'genres', 'controller' => 'GenreController', 'ABM' => true];

foreach ($endpoints as $i) {
    $router->addRoute($i['endpoint'], 'GET', $i['controller'], 'getAll');
    $router->addRoute($i['endpoint'] . '/:ID', 'GET', $i['controller'], 'get');
    if ($i['ABM']) {
        $router->addRoute($i['endpoint'], 'POST', $i['controller'], 'insert'); 
        $router->addRoute($i['endpoint'].'/:ID', 'PUT', $i['controller'], 'update');
        $router->addRoute($i['endpoint'].'/:ID', 'DELETE', $i['controller'], 'delete');
    }
}
$router->addRoute("auth/token", 'GET', 'AuthApiController', 'token');


$router->setDefaultRoute('BookController', 'defaultAction');

// ejecuta la ruta (sea cual sea)
$router->route($_GET["resource"], $_SERVER['REQUEST_METHOD']);