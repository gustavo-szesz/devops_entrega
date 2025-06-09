<?php

namespace App\Core;

use Predis\Client;

class Cache
{
    private static ?Client $instance = null;
    private const DEFAULT_TTL = 3600; // 1 hora em segundos

    public static function getInstance(): Client
    {
        if (self::$instance === null) {
            self::$instance = new Client([
                'scheme' => 'tcp',
                'host'   => 'auth-redis',
                'port'   => 6379,
            ]);
        }

        return self::$instance;
    }

    public static function get(string $key)
    {
        $redis = self::getInstance();
        $value = $redis->get($key);
        
        return $value ? json_decode($value, true) : null;
    }

    public static function set(string $key, $value, int $ttl = self::DEFAULT_TTL): void
    {
        $redis = self::getInstance();
        $redis->setex($key, $ttl, json_encode($value));
    }

    public static function delete(string $key): void
    {
        $redis = self::getInstance();
        $redis->del($key);
    }

    public static function exists(string $key): bool
    {
        $redis = self::getInstance();
        return (bool) $redis->exists($key);
    }

    public static function clear(): void
    {
        $redis = self::getInstance();
        $redis->flushall();
    }
} 