<?php

namespace App\Core;

use App\Http\Request;
use App\Http\Response;

class Core
{
    public static function dispatch(array $routes)
    {
        $url = '/';

        // append na url
        isset($_GET['url']) && $url .= $_GET['url'];

        // remove a barra no final da url
        $url !== '/' && $url = rtrim($url, '/');

        $prefixController = 'App\\Controllers\\';

        $routeFound = false;

        foreach ($routes as $route) {
          // utilitário para o query string, transforma o {id} em um regex	
            $pattern = '#^'. preg_replace('/{id}/',
                     '([\w-]+)', $route['path']) .'$#';


            // verifica se a url bate com o padrão
            if (preg_match($pattern, $url, $matches)) {
              array_shift($matches);

              $routeFound = true;
              // se a rota for encontrada, adiciona os parâmetros na rota

              if ($route['method'] !== Request::method()) {
                Response::json([
                  'error' => 'true',
                  'sucess' => 'false',
                  'message' => 'Method not allowed',                 
                ], 405);
                return;
              }
              
              [$controller, $action] = explode('@', $route['action']);

              $controller = $prefixController . $controller;
              $extendController = new $controller();
              $extendController->$action(new Request(), new Response(), $matches); 
            }
        
    }

    if (!$routeFound) {
        $controller = $prefixController . 'NotFoundController';
        $extendController = new $controller();
        $extendController->index(new Request(), new Response());
    }
}
} 