<?php

require_once('./app/models/generic.model.php');

class AuthorModel extends GenericApiModel {

    function __construct(){
        parent::__construct("authors",
                            ["author", "note"],
                            ["orderByField" => "author"]);
    }
}