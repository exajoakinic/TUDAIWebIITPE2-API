<?php

class ConnectionDB {
    protected $db;
    private const typeDb = 'mysql';
    private const host = 'localhost';
    private const dbname = 'db_books';
    private const user = 'root';
    private const password = '';
    
    function __construct() {
        $this->db = new PDO(ConnectionDB::typeDb . ":host=" . ConnectionDB::host . ";dbname=" . ConnectionDB::dbname . ";charset=utf8", ConnectionDB::user, ConnectionDB::password);
    }
}
