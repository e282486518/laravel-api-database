<?php

namespace e282486518\LaravelApiDatabase;

use RuntimeException;

class Str
{
    /**
     * @param array $params
     * @param bool $pluralizeArrQueryParams
     * @param string[] $pluralizeExcept
     * @return string|false
     */
    public static function httpBuildQuery(array $params, $pluralizeArrQueryParams = false, array $pluralizeExcept = [])
    {
        $query = '';
        $paramIx = 0;
        foreach ($params as $key => $values) {
            if (is_array($values)) {
                if (empty($values)) {
                    return false;
                }
                if ($pluralizeArrQueryParams && !in_array($key, $pluralizeExcept)) {
                    $key .= self::endsWith($key, 's') ? 'es' : 's';
                }
                $key .= '[]';
            } else {
                $values = [$values];
            }

            foreach ($values as $value) {
                if ($paramIx++) {
                    $query .= '&';
                }

                $query .= "$key=";
                if (!is_string($value) && !is_integer($value)) {
                    throw new RuntimeException('Value should be a string or an integer');
                }
                $query .= urlencode($value);
            }
        }

        return $query;
    }

    /**
     * @param string $query
     * @return array
     */
    public static function parseQuery($query)
    {
        $params = [];
        foreach (explode('&', $query) as $queryPart) {
            $parts = explode('=', $queryPart);
            if (count($parts) === 2) {
                $key = $parts[0];
                $value = urldecode($parts[1]);
                if (self::endsWith($key, '[]')) {
                    $params[substr($key, 0, -2)][] = $value;
                } else {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length) {
            return (substr($haystack, -$length) === $needle);
        }

        return false;
    }

    /**
     * ---------------------------------------
     * 用于CURL和GuzzleHttp的header格式相互转化
     *
     * @param $headers
     * @param string $format 转化目标格式
     * @return array
     * @author hlf <phphome@qq.com> 2025/3/26
     * ---------------------------------------
     */
    public static function convertHeaders($headers, $format = 'array') {
        if (empty($headers)) {
            return [];
        }
        // 取第数组第一个元素的值
        $firstElement = reset($headers);
        $result = [];
        if (strpos($firstElement, ': ') !== false) {
            // headers是字符串格式
            if ($format == 'array') {
                // 字符串格式 => 为数组格式
                foreach ($headers as $header) {
                    list($key, $value) = explode(': ', $header, 2);
                    $result[$key] = $value;
                }
                return $result;
            } else {
                return $headers;
            }
        } else {
            // headers是数组格式
            if ($format == 'array') {
                return $headers;
            } else {
                // 数组格式 => 字符串格式
                foreach ($headers as $key => $value) {
                    $result[] = $key. ': '. $value;
                }
                return $result;
            }
        }
    }
}