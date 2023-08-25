<?php

namespace Database;

use PDO;

class DbConnect
{

    public PDO $conn;

    public function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost;dbname=amocrm-test", 'root', '');
    }

}