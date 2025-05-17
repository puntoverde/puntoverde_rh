<?php
$router->group(['prefix'=>'vacaciones-permiso'],function() use($router){
    
    $router->get('/dias-disabled/{id:[0-9]+}','ColaboradorVacacionesPermisoController@diaDisabled');
    
    $router->get('/vacaciones/{id:[0-9]+}','ColaboradorVacacionesPermisoController@getVacacionesByColaborador');
    $router->get('/permisos/{id:[0-9]+}','ColaboradorVacacionesPermisoController@getPermisosByColaborador');
    
    $router->post('/vacaciones','ColaboradorVacacionesPermisoController@createVacacionesByColaborador');
    $router->post('/permisos','ColaboradorVacacionesPermisoController@createPermisosByColaborador');
    
    $router->get('/vacaciones-disponibles/{id:[0-9]+}','ColaboradorVacacionesPermisoController@getVacacionesDisponibles');
    $router->delete('/vacaciones/{id:[0-9]+}','ColaboradorVacacionesPermisoController@deleteVacacion');
    $router->delete('/permisos/{id:[0-9]+}','ColaboradorVacacionesPermisoController@deletePermiso');
});