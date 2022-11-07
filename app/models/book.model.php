<?php

require_once('./app/models/generic.model.php');

class BookModel extends GenericApiModel {

    function __construct(){
        parent::__construct("books",
                ['isbn', 'title', 'id_author', 'id_genre', 'price', 'url_cover'],
                ["fieldsOnSelect" => "books.*, authors.author, genres.genre",
                 "joinSentence" => "INNER JOIN authors ON authors.id = books.id_author INNER JOIN genres ON genres.id = books.id_genre",
                "orderByField" => "author,title"]);
    }

    function countByAuthor($id){
        return $this->countBy("id_author", $id);

        $query = $this->db->prepare("SELECT COUNT(*) AS result FROM $this->table WHERE id_author = ?");
        $query->execute([$id]);

        return $query->fetch(PDO::FETCH_OBJ)->result;
    }

    function countByGenre($id){
        return $this->countBy("id_genre", $id);

        $query = $this->db->prepare("SELECT COUNT(*) AS result FROM $this->table WHERE id_genre = ?");
        $query->execute([$id]);

        return $query->fetch(PDO::FETCH_OBJ)->result;
    }

    function getByAuthor($id) {
        return $this->getAllBy("id_author", $id);
    }
    function getByGenre($id) {
        return $this->getAllBy("id_genre", $id);
    }

    /**
     * Guarda en el servidor la imagen y devuelve ruta hacia el mismo
     */
    function insertCoverFile($file){
        $filePath = "images/covers/" . uniqid("", true) . "." . strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!file_exists('images/covers')) {
            mkdir('images/covers', 0777, true); 
        }

        move_uploaded_file($file["tmp_name"], $filePath);
        return $filePath;
    }

    /**
     * Elimina archivo del servidor
     */
    function removeCoverFile($file){
        if (file_exists($file)) {  
            return unlink($file);
        }
        return false;
    }
}