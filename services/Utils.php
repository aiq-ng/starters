<?php

namespace Services;

class Utils
{
    public static function filterOutNull($data): array
    {
        try {
            if (!is_array($data)) {
                $data = (array) $data;
            }

            return array_map(function ($item) {
                return array_filter($item, function ($value) {
                    return !is_null($value);
                });
            }, $data);

        } catch (\InvalidArgumentException $e) {
            error_log('Invalid argument: ' . $e->getMessage());
            return ['error' => 'Invalid argument: ' . $e->getMessage()];

        } catch (\Exception $e) {
            error_log('An error occurred: ' . $e->getMessage());
            return ['error' => 'An error occurred: ' . $e->getMessage()];
        }
    }
}
