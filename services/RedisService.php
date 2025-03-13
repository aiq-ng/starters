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
            $response = $this->redis->rpush($key, $jsonValue);
            return $response;
        } catch (\Exception $e) {
            error_log("Redis set error: " . $e->getMessage());
            return false;
        }
    }

    public function expire(string $key, int $seconds): bool
    {
        try {
            return $this->redis->expire($key, $seconds);
        } catch (\Exception $e) {
            error_log("Redis expire error: " . $e->getMessage());
            return false;
        }
    }

    public function get(string $key): ?array
    {
        $value = $this->redis->get($key);
        return $value ? json_decode($value, true) : null;
    }

    public function getList(string $key): array
    {
        error_log("Redis getList key: $key");
        try {
            $values = $this->redis->lRange($key, 0, -1);
            return array_map(function ($value) {
                return json_decode($value, true);
            }, $values);
        } catch (\Exception $e) {
            error_log("Redis getList error: " . $e->getMessage());
            return [];
        }
    }

    public function delete(string $key)
    {
        return $this->redis->del($key);
    }

    public function deleteByIds(string $key, array $ids): bool
    {
        try {
            $values = $this->redis->lRange($key, 0, -1);
            if (empty($values)) {
                return true;
            }

            $decodedValues = array_map(function ($value) {
                return json_decode($value, true);
            }, $values);

            $filteredValues = array_filter($decodedValues, function ($item) use ($ids) {
                return !isset($item['id']) || !in_array($item['id'], $ids);
            });

            if (count($decodedValues) === count($filteredValues)) {
                return true;
            }

            $this->redis->del($key);

            if (!empty($filteredValues)) {
                $jsonValues = array_map(function ($value) {
                    return json_encode($value);
                }, $filteredValues);
                $this->redis->rPush($key, ...$jsonValues);
            }

            error_log("Deleted items with IDs " . json_encode($ids) . " from key: $key");
            return true;
        } catch (\Exception $e) {
            error_log("Redis deleteByIds error: " . $e->getMessage());
            return false;
        }
    }

    public function flush()
    {
        return $this->redis->flushDB();
    }
}
