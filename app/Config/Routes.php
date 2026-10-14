<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('playground', 'PlaygroundController::index');
$routes->get('docs', 'Home::docs');
$routes->post('api/kalkulator/simulasi', 'PlaygroundController::hitung');

$routes->get('demo/user', 'Home::demoUser');
$routes->get('demo/admin', 'Home::demoAdmin');
$routes->get('demo/logout', 'Home::logout');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('langganan', 'LanggananController::index');
    $routes->get('langganan/(:num)', 'LanggananController::detail/$1');
    $routes->get('langganan/(:num)/ganti-paket', 'LanggananController::formGanti/$1');
    $routes->post('langganan/(:num)/ganti-paket', 'LanggananController::prosesGanti/$1');
    $routes->get('tagihan', 'TagihanController::index');
    $routes->get('tagihan/(:num)', 'TagihanController::detail/$1');
});

$routes->group('api', ['filter' => ['auth', 'throttle']], static function ($routes) {
    $routes->post('langganan/(:num)/ganti-paket', 'Api\LanggananApiController::gantiPaket/$1');
    $routes->get('tagihan/(:num)', 'Api\TagihanApiController::detail/$1');
    $routes->post('tagihan/(:num)/bayar', 'Api\TagihanApiController::bayar/$1');
});

$routes->post('webhook/pembayaran', 'Api\WebhookController::pembayaran', ['filter' => 'throttle']);

$routes->group('admin', ['filter' => ['auth', 'admin']], static function ($routes) {
    $routes->resource('paket', ['controller' => 'PaketController']);
    $routes->get('laporan/mrr', 'LaporanController::mrr');
});
