<?php

require_once('./app/models/generic.model.php');

class UserModel extends GenericApiModel {

    function __construct(){
        parent::__construct("users",
                            ["user", "password"]);
    }
}