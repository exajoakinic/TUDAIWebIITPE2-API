<?php
require_once "./app/controllers/generic.controller.php";
require_once "./app/models/genre.model.php";
require_once "./app/models/book.model.php";

class GenreController extends GenericApiController {
 
    function __construct() {
        parent::__construct(new GenreModel(),
                        ["genre", "note"],
                        ['id' => 'genres.id',
                        'genre' => 'genres.genre',
                        'note' => 'genres.note',
                        ]
                        );
    }
    
    protected function getAndValidateBeforeDelete($id) {
        //Traigo el elemento utilizando la clase padre y su primera validación de existencia
        $genre = parent::getAndValidateBeforeDelete($id);
        $referencedBooks =(new BookModel())->getByGenre($id);
        if (count($referencedBooks)>0) {
            //MUESTRO PÁGINA DE ERROR PORQUE NO SE PUEDE BORRAR EL AUTOR
            $this->view->response("Imposible eliminar el género '$genre->genre' porque tiene libros referenciados", 400);
            die;
        }
        return $genre;
    }

}