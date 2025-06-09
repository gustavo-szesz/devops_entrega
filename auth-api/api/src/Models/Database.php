<?php

namespace App\Models;

use PDO;

class Database
{
    public static function getConnection()
    {
        $pdo = new PDO(
            "pgsql:host=auth-db;port=5432;dbname=authdb",
            "authuser",
            "authpass"
        );
        return $pdo;

    }
}