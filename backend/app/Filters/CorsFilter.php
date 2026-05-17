<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    private function getAllowedOrigins(): array
    {
        $origins = env('app.corsOrigins');
        return array_map('trim', explode(',', $origins));
    }

    private function getAllowedOrigin(?string $requestOrigin): string
    {
        $allowedOrigins = $this->getAllowedOrigins();
        
        if ($requestOrigin && in_array($requestOrigin, $allowedOrigins)) {
            return $requestOrigin;
        }
        
        return $allowedOrigins[0] ?? '*';
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');
        $requestOrigin = $request->getHeaderLine('Origin');
        
        $response->setHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($requestOrigin));
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Max-Age', '86400');

        if ($request->getMethod() === 'OPTIONS') {
            return $response->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $requestOrigin = $request->getHeaderLine('Origin');
        $response->setHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($requestOrigin));
    }
}