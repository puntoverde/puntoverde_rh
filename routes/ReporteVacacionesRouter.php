<?php

$router->group(["prefix"=>"reporte-vacaciones"],function() use ($router){

      $router->get('','ReporteVacacionesController@getColaboradorVacacionesRestantes');

      $router->get('/by-anio','ReporteVacacionesController@getPrevieColaboradorVacacionesAnio');
      
      $router->get('/by-anio-detalle','ReporteVacacionesController@getdetalleVacacionesByAnio');
      
      $router->get('/area','ReporteVacacionesController@getAreasByVacaciones');

});
