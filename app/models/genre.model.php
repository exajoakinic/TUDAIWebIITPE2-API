<?php

require_once('./app/models/generic.model.php');

class GenreModel extends GenericApiModel {

    function __construct(){
        parent::__construct("genres",
                            ["genre", "note"],
                            ["orderByField" => "genre"]);
    }
}