<?php

namespace App\Traits;

trait FilterGuzzleStackLogs
{
    private function filterSensitiveData($data, $sensitiveKeys)
    {
        $dataArray = json_decode($data, true);
        if (is_array($dataArray)) {
            array_walk_recursive($dataArray, function (&$value, $key) use ($sensitiveKeys) {
                if (in_array($key, $sensitiveKeys, true)) {
                    $value = '***';
                }
            });
        }
        return json_encode($dataArray);
    }

    private function filterSensitiveHeaders($headers, $sensitiveKeys)
    {
        $filteredHeaders = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys, true)) {
                $filteredHeaders[$key] = '***';
            } else {
                $filteredHeaders[$key] = $value;
            }
        }
        return $filteredHeaders;
    }
}