<?php

namespace App\Services;
use App\Utils\Validator;
use PDOException;
use Exception;
use App\Models\User;
use App\Http\JWT;

class UserService
{
        public static function create(array $data)
    {
        try {
            $fields = Validator::validate([
                'name'     => $data['name']     ?? '',
                'lastName' => $data['lastName'] ?? '',
                'email'    => $data['email']    ?? '',
                'password' => $data['password'] ?? '',
            ]);

            $fields['password'] = password_hash($fields['password'], PASSWORD_DEFAULT);

            $user = User::save($fields);

            if (!$user) {
                return [
                    'error' => true,
                    'message' => 'Sorry, we could not create your account.',
                    'data' => null
                ];
            }

            return [
                'error' => false,
                'message' => 'User created successfully!',
                'data' => $user
            ];

        } 
        catch (PDOException $e) {
            $code = $e->errorInfo[0] ?? null;
            if ($code === '08006') {
                return [
                    'error' => true,
                    'message' => 'Sorry, we could not connect to the database.',
                    'data' => null
                ];
            }
            if ($code === '23505') {
                return [
                    'error' => true,
                    'message' => 'Sorry, user already exists.',
                    'data' => null
                ];
            }
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
        catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public static function auth(array $data)
    {
        try {
            $fields = Validator::validate([
                'email'    => $data['email']    ?? '',
                'password' => $data['password'] ?? '',
            ]);
            
            $user = User::authentication($fields);

            if (!$user) {
                return [
                    'error' => true,
                    'message' => 'Sorry, we could not authenticate you.',
                    'data' => null
                ];
            }   

            return JWT::generate($user);

        } 
        catch (PDOException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
        catch (Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public static function fetch(mixed $authorization)
    {
        try {
            if (isset($authorization['error'])) {
                return ['unauthorized'=> $authorization['error']];
            }

            $userFromJWT = JWT::verify($authorization);

            if (!$userFromJWT) return ['unauthorized'=> "Please, login to access this resource."];

            $user = User::find($userFromJWT['id']);

            if (!$user) return ['error'=> 'Sorry, we could not find your account.'];

            return $user;
        } 
        catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') return ['error' => 'Sorry, we could not connect to the database.'];
            return ['error' => $e->errorInfo[0]];
        }
        catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function update(mixed $authorization, array $data)
    {
        try {
            if (isset($authorization['error'])) {
                return ['unauthorized'=> $authorization['error']];
            }

            $userFromJWT = JWT::verify($authorization);

            if (!$userFromJWT) return ['unauthorized'=> "Please, login to access this resource."];

            // Valida nome, sobrenome e email, precisa desses campos para atualizar o usuário
            $fields = Validator::validate([
                'name' => $data['name'] ?? '',
                'lastName' => $data['lastName'] ?? '',
                'email' => $data['email'] ?? '',
            ]);

            $user = User::update($userFromJWT['id'], $fields);

            if (!$user) return ['error'=> 'Sorry, we could not update your account.'];

            return "User updated successfully!";
        } 
        catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') return ['error' => 'Sorry, we could not connect to the database.'];
            return ['error' => $e->getMessage()];
        }
        catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function delete(mixed $authorization, int|string $id)
    {
        try {
            if (isset($authorization['error'])) {
                return ['unauthorized'=> $authorization['error']];
            }

            $userFromJWT = JWT::verify($authorization);

            if (!$userFromJWT) return ['unauthorized'=> "Please, login to access this resource."];

            $user = User::delete($id);

            if (!$user) return ['error'=> 'Sorry, we could not delete your account.'];

            return "User deleted successfully!";
        } 
        catch (PDOException $e) {
            if ($e->errorInfo[0] === '08006') return ['error' => 'Sorry, we could not connect to the database.'];
            return ['error' => $e->getMessage()];
        }
        catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
     
}