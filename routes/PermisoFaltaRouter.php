<?php
$router->group(['prefix'=>'permisos-faltas'],function() use($router){

    $router->get('/{id:[0-9]+}','PermisoFaltaController@getPermisoByEmpleadoId');
    
    $router->post('','PermisoFaltaController@setPermiso');

    $router->delete('/{id:[0-9]+}','PermisoFaltaController@CancelarPermiso');
    
});