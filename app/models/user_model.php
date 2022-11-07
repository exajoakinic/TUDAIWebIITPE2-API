<?php

require_once('./app/models/generic_model.php');

class UserModel extends GenericModel {

    function __construct(){
        parent::__construct("users",
                            ["user", "password"]);
    }
}