<?php
$router->group(['prefix'=>'reporte-incidencias'],function() use($router){
    
    // $router->get('','IncidenciasController@getDiasAsueto');
    
    // $router->post('','IncidenciasController@crateDiasAsueto');

    // $router->delete('/{id:[0-9]+}','IncidenciasController@deleteDiaAsueto');

    // $router->delete('/dia/{id:[0-9]+}','IncidenciasController@deleteDiaHorario');
    $router->get('','ReporteIncidenciasController@getReporteIncidencias');
    $router->get('/departamentos','ReporteIncidenciasController@getDepartamentos');
    // $router->get('/tipo-incidencia','IncidenciasController@getTipoIncidencia');
    // $router->post('','IncidenciasController@createIncidencia');
    $router->get('/by-dia','ReporteIncidenciasController@ReporteIncidenciasColaboradoresByDia');
    $router->get('/by-dia-auto','ReporteIncidenciasController@ReporteIncidenciasColaboradorAuto');
    
});