<?php
$router->group(['prefix'=>'colaboradores-horario'],function() use($router){
    
    $router->get('','ColaboradorHorarioController@getPermisos');
    
    $router->get('/{id:[0-9]+}','ColaboradorHorarioController@getHorarioByEmpleado');

    $router->post('/{id:[0-9]+}','ColaboradorHorarioController@setHorario');

    $router->delete('/{id:[0-9]+}','ColaboradorHorarioController@deleteFullHorario');

    $router->delete('/dia/{id:[0-9]+}','ColaboradorHorarioController@deleteDiaHorario');
    
});