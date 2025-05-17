<?php

namespace App\Controllers;

use App\DAO\ReporteVacacionesDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ReporteVacacionesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getColaboradorVacacionesRestantes(Request $req)
    {
        return ReporteVacacionesDAO::getColaboradorVacacionesRestantes((object)$req->all());
    }


    public function getPrevieColaboradorVacacionesAnio(Request $req)
    {
        $id_colaborador=$req->input("id_colaborador");
        return ReporteVacacionesDAO::getPrevieColaboradorVacacionesAnio($id_colaborador);
    }


    public function getdetalleVacacionesByAnio(Request $req)
    {
        $id_colaborador=$req->input("id_colaborador");
        $anio=$req->input("anio");
        return ReporteVacacionesDAO::getdetalleVacacionesByAnio($id_colaborador,$anio);
    }

    public function getAreasByVacaciones(){
        return ReporteVacacionesDAO::getAreasByVacaciones();
    }

    public function getFullVacaciones()
    {
        return ReporteVacacionesDAO::getFullVacaciones(); 
    }

  
}
