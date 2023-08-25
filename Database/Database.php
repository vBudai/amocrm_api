<?php

namespace Database;

use PDO;

class Database
{
    private DbConnect $db;

    public function __construct()
    {
        $this->db = new DbConnect();
    }

    public function query($sql) : array|null
    {
        $stmt = $this->db->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}