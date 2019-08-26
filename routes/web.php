<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Usuario
$router->get('/get', 'UserController@get');
$router->post('/add', 'UserController@post');
$router->post('/login', 'UserController@login');
$router->delete('/delete', 'UserController@delete');
$router->put('/put', 'UserController@put');

$router->get('/company/myCompany', 'CompanyController@myCompany');
// Empresas

$router->get('/company/sector', 'CompanyController@getSector');
$router->post('/company/add', 'CompanyController@post');
