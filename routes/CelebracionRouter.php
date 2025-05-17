<?php
$router->group(['prefix'=>'celebracion'],function() use($router){
    
    $router->get('','CelebracionController@getCelebraciones');

    $router->get('/{id:[0-9]+}','CelebracionController@getCelebracion');

    $router->post('','CelebracionController@createCelebracion');
    
    $router->put('/{id:[0-9]+}','CelebracionController@deleteCelebracion');
    

});