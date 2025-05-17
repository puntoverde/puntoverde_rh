<?php
$router->group(['prefix'=>'dias-asueto'],function() use($router){
    
    $router->get('','DiasAsuetoController@getDiasAsueto');
    
    $router->post('','DiasAsuetoController@crateDiasAsueto');

    $router->delete('/{id:[0-9]+}','DiasAsuetoController@deleteDiaAsueto');

    $router->delete('/dia/{id:[0-9]+}','DiasAsuetoController@deleteDiaHorario');
    
});