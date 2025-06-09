<?php

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Services\UserService;
use App\Core\Cache;

class UserController
{
    public function store(Request $request, Response $response)
    {
        $body = $request::body();

        $userService = UserService::create($body);

        if (isset($userService['error']) && $userService['error']) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['message'], // <-- CERTO!
                'data'    => $userService['data'] ?? null
            ], 400);
        }

        // Limpa o cache de usuários após criar um novo
        Cache::delete('users_list');

        return $response::json([
            'error'   => false,
            'success' => true,
            'message' => $userService['message'],
            'data'    => $userService['data']
        ], 201);
    }


    public function login(Request $request, Response $response)
    {
        $body = $request::body();

        $userService = UserService::auth($body);

        if (isset($userService['error']) && $userService['error']) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['message'], // <-- CERTO!
                'data'    => $userService['data'] ?? null
            ], 400);
        }

        return $response::json([
            'error'   => false,
            'success' => true,
            'jwt'     => $userService
        ], 200);
        return;

    }

    public function fetch(Request $request, Response $response)
    {
        $authorization = $request::authorization();
        $cacheKey = 'user_' . md5($authorization);

        // Tenta obter do cache primeiro
        $cachedData = Cache::get($cacheKey);
        if ($cachedData !== null) {
            return $response::json([
                'error'   => false,
                'success' => true,
                'data'    => $cachedData,
                'cached'  => true
            ], 200);
        }

        $userService = UserService::fetch($authorization);

        if (isset($userService['unauthorized'])) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['unauthorized']
            ], 401);
        }

        if (isset($userService['error'])) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['error']
            ], 400);
        }

        // Salva no cache por 1 hora
        Cache::set($cacheKey, $userService);

        return $response::json([
            'error'   => false,
            'success' => true,
            'data'    => $userService
        ], 200);
    }

    public function update(Request $request, Response $response)
    {
        $authorization = $request::authorization();

        $body = $request::body();

        $userService = UserService::update($authorization, $body);

        if (isset($userService['unauthorized'])) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['unauthorized']
            ], 401);
        }

        if (isset($userService['error'])) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['error']
            ], 400);
        }

        // Limpa o cache do usuário após atualização
        Cache::delete('user_' . md5($authorization));

        return $response::json([
            'error'   => false,
            'success' => true,
            'message' => $userService
        ], 200);
    }

    public function remove(Request $request, Response $response, array $id)
    {
        $authorization = $request::authorization();

        $userService = UserService::delete($authorization, $id[0]);

        if (isset($userService['unauthorized'])) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['unauthorized']
            ], 401);
        }

        if (isset($userService['error'])) {
            return $response::json([
                'error'   => true,
                'success' => false,
                'message' => $userService['error']
            ], 400);
        }

        // Limpa o cache do usuário após deleção
        Cache::delete('user_' . md5($authorization));
        Cache::delete('users_list');

        return $response::json([
            'error'   => false,
            'success' => true,
            'message' => $userService
        ], 200);
    }
}