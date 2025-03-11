<?php

namespace Services;

class RedisService
{
    private $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->connect();
    }

    private function connect()
    {
        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = getenv('REDIS_PORT') ?: 6379;
        $password = getenv('REDIS_PASSWORD');

        $this->redis->connect($host, $port);

        if ($password) {
            $this->redis->auth($password);
        }
    }

    public function set(string $key, array $value)
    {
        try {
            $jsonValue = json_encode($value);

            error_log("Redis set key: $key, value: $jsonValue");
            $response = $this->redis->rpush($key, $jsonValue);

            error_log("Redis response: $response");

            return true;
        } catch (\Exception $e) {
            error_log("Redis set error: " . $e->getMessage());
            return false;
        }
    }

    public function get(string $key): ?array
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }

    public function delete(string $key)
    {
        return $this->redis->del($key);
    }

    public function flush()
    {
        return $this->redis->flushDB();
    }
}
