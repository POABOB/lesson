<?php
if ( ! defined('PPP')) exit('非法入口');
use core\common\auth;
use core\lib\IP;
//DOCS: https://github.com/bramus/router

if(!isset($_SESSION['user'])) $_SESSION['user'] = false;

// $router->mount('/api', function() use ($router) {
    
    $router->get("/doc", function() { require(PPP . '/static/doc/index.php'); });

    $router->get("/swagger", function() {
        $openapi = \OpenApi\Generator::scan([APP . '/controller']);
        header('Content-Type: application/json');
        echo $openapi->toJSON();
    });

    $router->options('/.*', function() {});
    // 登入登出
    $router->post('/login', 'loginController@login_');
    $router->get('/logout', 'loginController@logout_');

    // ADMIN
    $router->before('GET|POST|PATCH|DELETE', '/clinic.*', function() { auth::factory()->admin('Session 過期，請重新再登入'); });
    $router->get('/clinic', 'clinicController@index_');
    $router->post('/clinic', 'clinicController@insert_');
    $router->patch('/clinic', 'clinicController@update_');
    $router->delete('/clinic', 'clinicController@delete_');
    $router->patch('/clinic/back', 'clinicController@delete_back');


    // AUTH
    $router->before('GET|POST|PATCH|DELETE', '/lesson.*', function() { auth::factory()->users('Session 過期，請重新再登入'); });
    $router->before('GET|POST|PATCH|DELETE', '/users.*', function() { auth::factory()->users('Session 過期，請重新再登入'); });
    $router->before('GET|POST|PATCH|DELETE', '/logs.*', function() { auth::factory()->users('Session 過期，請重新再登入'); });
    
    
    // ADMIN || USER
    $router->get('/users/(\d+)', 'userController@index_');
    $router->post('/users/(\d+)', 'userController@insert_');
    $router->patch('/users/(\d+)', 'userController@update_');
    $router->delete('/users/(\d+)', 'userController@delete_');
    $router->patch('/users/back/(\d+)', 'userController@delete_back');
    $router->patch('/users/password/(\d+)', 'userController@update_password');
    $router->get('/users/info', 'userController@info_');

    // LOG
    $router->get('/logs', 'logController@index_');

    // ADMIN || USER
    $router->get('/lesson', 'lessonController@index_');
    $router->post('/lesson', 'lessonController@insert_');
    $router->patch('/lesson', 'lessonController@update_');
    $router->delete('/lesson', 'lessonController@delete_');
    $router->patch('/lesson/back', 'lessonController@delete_back');


// });

