<?php
$router->group(['prefix'=>'incidencias'],function() use($router){
    
    $router->get('','IncidenciasController@getDiasAsueto');
    
    // $router->post('','IncidenciasController@crateDiasAsueto');

    // $router->delete('/{id:[0-9]+}','IncidenciasController@deleteDiaAsueto');

    // $router->delete('/dia/{id:[0-9]+}','IncidenciasController@deleteDiaHorario');
    $router->get('/colaboradores-area/{id:[0-9]+}','IncidenciasController@getColaboradoresArea');
    $router->get('/all-colaboradores-area/{id:[0-9]+}','IncidenciasController@getAllColaboradoresArea');
    $router->get('/tipo-incidencia','IncidenciasController@getTipoIncidencia');
    $router->post('','IncidenciasController@createIncidencia');
    $router->post('/modifica','IncidenciasController@updateIncidencia');
    
});