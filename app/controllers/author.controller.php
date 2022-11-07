<?php
require_once "./app/controllers/generic.controller.php";
require_once "./app/models/author.model.php";
require_once "./app/models/book.model.php";

class AuthorController extends GenericApiController {
 
    function __construct() {
        parent::__construct(new AuthorModel(),
                        ["author", "note"],
                        ['id' => 'authors.id',
                        'author' => 'authors.author',
                        'note' => 'authors.note',
                        ]);
    }

    protected function getAndValidateBeforeRemove($id) {
        //Traigo el elemento utilizando la clase padre y su primera validación de existencia
        $author = parent::getAndValidateBeforeRemove($id);
        $referencedBooks =(new BookModel())->getByAuthor($id);
        if (count($referencedBooks)>0) {
            //MUESTRO PÁGINA DE ERROR PORQUE NO SE PUEDE BORRAR EL AUTOR
            $this->view->response("Imposible eliminar el autor porque tiene libros referenciados", 400);
            die;
        }
        return $author;
    }

}