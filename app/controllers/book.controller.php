<?php
require_once "./app/controllers/generic.controller.php";
require_once "./app/models/book.model.php";

class BookController extends GenericApiController {


    function __construct() {
        parent::__construct(
                new BookModel(),
                ['isbn', 'title', 'id_author', 'id_genre', 'price', 'url_cover'],
                ['isbn'     =>'books.isbn', 
                 'title'    =>'books.title',
                 'id_author'=>'books.id_author', 
                 'id_genre' =>'books.id_genre', 
                 'price'    =>'books.price', 
                 'url_cover'=>'books.url_cover',
                 'genre'    =>'genres.genre',
                 'author'   =>'authors.author',
                ]
            );
    }    

    /**
     * Sobreescrive función de validación de post por necesitar más validaciones
     */
    protected function getAndValidateFromPost() {
        $book = parent::getAndValidateFromPost();
        // Verifica que exista el autor recibido
        if (!(new AuthorModel)->getById($book->id_author)) {
            $this->view->response("Campo 'id_author' no referencia a un autor existente", "400");
            die;
        }

        // Verifica que exista el género recibido
        if (!(new GenreModel)->getById($book->id_genre)) {
            $this->view->response("Campo 'id_genre' no referencia a un género existente", "400");
            die;
            }
            /* 
        // Verifica si se cargó una imagen por archivo solicitando al model que su creación si es necesario
        if (!empty($_FILES["img_file_cover"]["name"])) {
            $newUrlFile = $this->model->insertCoverFile($_FILES["img_file_cover"]);
            //Referencia url_cover a la nueva dirección
            $book->url_cover = $newUrlFile;
        } */

        // Fuerza que el precio sea un valor numérico

        //$book->price = number_format($book->price, 2, '.', '');
        $book->price = floatval($book->price);
        return $book;
    }

    /**
     * Sobreescrive validación antes de editar por necesitar eliminar la tapa del servidor
     */
    protected function getAndValidateBeforeEdit($id) {
        $book = parent::getAndValidateBeforeEdit($id);
     /*    $oldBook = $this->model->getById($id);
        if (!empty($_FILES["img_file_cover"]["name"])
            || $book->url_cover != $oldBook->url_cover) {
                        $this->model->removeCoverFile($oldBook->url_cover);
        } */
        return $book;
    }
    /**
     * Sobreescrive validación antes de borrar por necesitar eliminar la tapa del servidor
     */
    protected function getAndValidateBeforeRemove($id) {
        $book = parent::getAndValidateBeforeRemove($id);
        $this->model->removeCoverFile($book->url_cover);
        return $book;
    }
}