<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    /**
     * The default CORS configuration.
     *
     * @var array{
     *      allowedOrigins: list<string>,
     *      allowedOriginsPatterns: list<string>,
     *      supportsCredentials: bool,
     *      allowedHeaders: list<string>,
     *      exposedHeaders: list<string>,
     *      allowedMethods: list<string>,
     *      maxAge: int,
     *  }
     */
    public array $default = [
        'allowedOrigins' => [
            'http://localhost:5173',   // Vue frontend
            'http://localhost:3000',   // Vue alternate
            'http://localhost:8081',   // Expo web
            'http://localhost:8082',   // Expo alternate
            'exp://localhost:8081',    // Expo app (Android)
            'exp://localhost:8082',    // Expo app alternate
            'http://127.0.0.1:8081',   // Expo localhost
            'http://127.0.0.1:8082',   // Expo alternate
        ],
        // Para desarrollo: permite cualquier origen localhost
        'allowedOriginsPatterns' => [
            'http://localhost:*',
            'http://127.0.0.1:*',
            'exp://*',
        ],
        'supportsCredentials' => true,
        'allowedHeaders' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposedHeaders' => [],
        'allowedMethods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'maxAge' => 7200,
    ];
}
