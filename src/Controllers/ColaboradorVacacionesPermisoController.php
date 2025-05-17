<?php

namespace App\Controllers;

use App\DAO\ColaboradorVacacionesPermisoDAO;
use App\DAO\ColaboradorDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ColaboradorVacacionesPermisoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getVacacionesByColaborador($id){
        return ColaboradorVacacionesPermisoDAO::getVacacionesByColaborador($id);        
    }
    public function getPermisosByColaborador($id){
        return ColaboradorVacacionesPermisoDAO::getPermisosByColaborador($id);        
    }

    public function createVacacionesByColaborador(Request $req){
        $reglas = [            
            "id_colaborador_encargado" => "required", 
            "id_colaborador" => "required", 
            // "fecha_vacaciones" => "required|date|after_or_equal:today", 
            "fecha_vacaciones" => "required", 
            "descripcion" => "required", 
            ];

        $this->validate($req, $reglas);
        return ColaboradorVacacionesPermisoDAO::createVacacionesByColaborador((object)$req->all());
    }

    public function createPermisosByColaborador(Request $req){
        $reglas = [            
            "id_colaborador_encargado" => "required", 
            "id_colaborador" => "required", 
            "fecha_permiso" => "required|date|after_or_equal:today", 
            "descripcion" => "required", 
            "tipo" => "required", 
            ];

        $this->validate($req, $reglas);
        return ColaboradorVacacionesPermisoDAO::createPermisosByColaborador((object)$req->all());
    }


    public function deleteVacacion($id)
    {
        return ColaboradorVacacionesPermisoDAO::deleteVacacion($id);
    }

    public function deletePermiso($id)
    {
        return ColaboradorVacacionesPermisoDAO::deletePermiso($id);
    }


    public function diaDisabled($id)
    {
        return ColaboradorVacacionesPermisoDAO::diaDisabled($id);
    }

    public function  getVacacionesDisponibles($id)
    {
        return ColaboradorVacacionesPermisoDAO::getVacacionesDisponibles($id);
    }

    

}