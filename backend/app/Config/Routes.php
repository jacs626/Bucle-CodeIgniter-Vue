<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

$routes->options('(:any)', function () {
    return service('response')->setStatusCode(200);
});

$routes->group('api', ['namespace' => 'App\Modules\Categories\Controllers'], function ($routes) {
    $routes->get('categories', 'CategoryController::index');
    $routes->get('categories/(:num)', 'CategoryController::show/$1');
    $routes->post('categories', 'CategoryController::create');
    $routes->put('categories/(:num)', 'CategoryController::update/$1');
    $routes->delete('categories/(:num)', 'CategoryController::delete/$1');
});

$routes->group('api', ['namespace' => 'App\Modules\Entities\Controllers'], function ($routes) {
    $routes->get('entities', 'EntityController::index');
    $routes->get('entities/(:num)', 'EntityController::show/$1');
    $routes->get('entities/category/(:num)', 'EntityController::getByCategory/$1');
    $routes->post('entities', 'EntityController::create');
    $routes->put('entities/(:num)', 'EntityController::update/$1');
    $routes->delete('entities/(:num)', 'EntityController::delete/$1');
});

$routes->group('api', ['namespace' => 'App\Modules\Blocks\Controllers'], function ($routes) {
    $routes->get('blocks', 'BlockController::index');
    $routes->get('blocks/(:num)', 'BlockController::show/$1');
    $routes->get('blocks/entity/(:num)', 'BlockController::getByEntity/$1');
    $routes->post('blocks', 'BlockController::create');
    $routes->put('blocks/(:num)', 'BlockController::update/$1');
    $routes->delete('blocks/(:num)', 'BlockController::delete/$1');
});

$routes->group('api', ['namespace' => 'App\Modules\History\Controllers'], function ($routes) {
    $routes->get('history', 'HistoryController::index');
    $routes->get('history/(:num)', 'HistoryController::show/$1');
    $routes->post('history', 'HistoryController::create');
    $routes->delete('history/(:num)', 'HistoryController::delete/$1');
});

$routes->group('api', ['namespace' => 'App\Modules\Documents\Controllers'], function ($routes) {
    $routes->get('documents', 'DocumentController::index');
    $routes->get('documents/(:num)', 'DocumentController::show/$1');
    $routes->post('documents', 'DocumentController::create');
    $routes->put('documents/(:num)', 'DocumentController::update/$1');
    $routes->delete('documents/(:num)', 'DocumentController::delete/$1');
});