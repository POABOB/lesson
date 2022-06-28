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
    $router->before('GET|POST', '/admin.*', function() {
        auth::factory()->admin('Session 過期，請重新再登入');
        IP::factory()->check_ip(); 
    });

    $router->get('/admin/clinic', 'adminController@index_');
    $router->post('/admin/clinic', 'adminController@insert_');
    $router->patch('/admin/clinic', 'adminController@update_');
    $router->delete('/admin/clinic', 'adminController@delete_');
    $router->patch('/admin/clinic/back', 'adminController@delete_back');
    $router->patch('/admin/clinic/password', 'adminController@update_password');


    // 以下需要JWT驗證
    $router->before('GET|POST', '/clinic.*', function() { 
        auth::factory()->clinic(); 
        IP::factory()->check_ip(); 
    });


    //info OK
    $router->get('/info', function() { auth::factory()->user_info('Session 過期，請重新再登入'); });

// });

