<?php
$router->group(['prefix'=>'colaboradores'],function() use($router){
    
    $router->get('','ColaboradorController@getEmpleados');

    $router->get('/{id:[0-9]+}','ColaboradorController@getEmpleadoById');
   
    $router->delete('/{id:[0-9]+}','ColaboradorController@bajaColaborador');
    
    $router->put('/reingreso/{id:[0-9]+}','ColaboradorController@reingresoColaborador');

    $router->get('/find','ColaboradorController@getAccionByNameOrNomina');
    
    $router->post('/','ColaboradorController@setColaborador');

    $router->put('/{id:[0-9]+}','ColaboradorController@updateColaborador');

    $router->get('/departamentos','ColaboradorController@getDepartamentos');

    $router->get('/areas/{id:[0-9]+}','ColaboradorController@getAreaByDepartamento');

    $router->put('/jefe-departamento','ColaboradorController@setJefeDepartamento');


    $router->get('/beneficiario/{id_colaborador:[0-9]+}','ColaboradorController@getBeneficiario');

    $router->post('/beneficiario','ColaboradorController@createBeneficiario');

    $router->get('/escolaridad/{id_colaborador:[0-9]+}','ColaboradorController@getEscolaridad');
    
    $router->post('/escolaridad','ColaboradorController@createEscolaridad');
    
    $router->get('/parentesco','ColaboradorController@getParentesco');

    $router->get('/evidencia-download','ColaboradorController@getFilesEvidencia');
    
    $router->get('/expediente/{id:[0-9]+}','ColaboradorController@getExpediente');
    
    $router->post('/expediente','ColaboradorController@createExpediente');
    
    $router->delete('/expediente','ColaboradorController@deleteExpediente');
    
    $router->get('/historico/{id:[0-9]+}','ColaboradorController@getHistoricoColaboradorPermanencia');
    
    $router->get('/reporte-altas-bajas','ColaboradorController@ReporteAltasBajasColaborador');
    
});
