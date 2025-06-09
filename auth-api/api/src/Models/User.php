<?php

namespace App\Models;

use App\Models\Database;
use PDOException;
use Exception;
use PDO;

class User extends Database
{
    public static function save(array $data) 
    {
        $pdo = self::getConnection();

        $statment = $pdo->prepare(
            "INSERT INTO users (name, lastName, email, password) 
            VALUES (:name, :lastName, :email, :password) RETURNING user_id, name, lastName, email"
        );
        $statment->bindParam(':name', $data['name']);
        $statment->bindParam(':lastName', $data['lastName']);
        $statment->bindParam(':email', $data['email']);
        $statment->bindParam(':password', $data['password']);

        try {
            if ($statment->execute()) {
                return $statment->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = $statment->errorInfo();
                throw new PDOException($error[2], $error[1]);
            }
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function authentication(array $data)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("
           SELECT
                *
            FROM
                users
            WHERE
                email = ?
        ");

        $stmt->execute([$data['email']]);

        if ($stmt->rowCount() < 1) return false;

        $user = $stmt->fetch(mode: PDO::FETCH_ASSOC);

        if (!password_verify($data['password'], $user['password'])) {
            return false;
        }

        return [
            'id' => $user['user_id'],
            'name' => $user['name'],
            'lastName' => $user['lastName'] ?? '',
            'email' => $user['email'],
        ];
    }

    public static function find(int|string $id)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare('
            SELECT 
                user_id, name, lastName, email
            FROM 
                users
            WHERE 
                user_id = ?
        ');

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update(int|string $id, array $data)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare('
            UPDATE 
                users 
            SET 
                name = :name, lastName = :lastName, email = :email
            WHERE 
                user_id = :id
        ');

        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':lastName', $data['lastName']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return self::find($id);
        }

        return false;
    }

    public static function delete(int|string $id)
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare('
            DELETE FROM 
                users 
            WHERE 
                user_id = ?
        ');

        if ($stmt->execute([$id])) {
            return true;
        }

        return false;
    }
}
