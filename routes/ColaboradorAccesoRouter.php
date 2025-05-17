<?php
$router->group(['prefix'=>'colaboradores-accesos'],function() use($router){
    
    $router->get('/colaborador/{id:[0-9]+}','ColaboradorAccesoController@getColaboradorByNomina');

    $router->post('/upload-txt','ColaboradorAccesoController@saveAccesoTxt');
    
    $router->get('/{id:[0-9]+}','ColaboradorAccesoController@getAsistencias');
    
    $router->get('/revision/{id:[0-9]+}','ColaboradorAccesoController@getAsistenciaColaboradorRevisar');
    
    $router->post('/revision','ColaboradorAccesoController@registroAccesoNuevoRevision');
    
    $router->put('/revision/{id:[0-9]+}','ColaboradorAccesoController@actualizarAccesoNuevoRevision');
        
    $router->get('/full','ColaboradorAccesoController@getAsistenciasFullColaboradores');
    

    // $router->post('/{id:[0-9]+}','ColaboradorHorarioController@setHorario');

    // $router->delete('/{id:[0-9]+}','ColaboradorHorarioController@deleteFullHorario');

    // $router->delete('/dia/{id:[0-9]+}','ColaboradorHorarioController@deleteDiaHorario');
    
});