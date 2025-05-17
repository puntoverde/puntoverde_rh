<?php

namespace App\Controllers;

use App\DAO\IncidenciasDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class IncidenciasController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getDiasAsueto(Request $req)
    {
        $annio=$req->input("annio");
        return IncidenciasDAO::getDiasAsueto($annio);
    }

    public function crateDiasAsueto(Request $req){
   
        return IncidenciasDAO::crateDiasAsueto($req->all());
    }

    public function deleteDiaAsueto($id){
        return IncidenciasDAO::deleteDiaAsueto($id);
    }
    public function deleteFullHorario($id){
        return IncidenciasDAO::deleteFullHorario($id);
    }
    
    public function getColaboradoresArea($id){
        return IncidenciasDAO::getColaboradoresArea($id);
    }
    
    public function getAllColaboradoresArea($id){
        return IncidenciasDAO::getAllColaboradoresArea($id);
    }

    public function getTipoIncidencia(){
        return IncidenciasDAO::getTipoIncidencia();
    }
    
    public function createIncidencia(Request $req){
        return IncidenciasDAO::createIncidencia((object)$req->all());
    }

    public function updateIncidencia(Request $req){
        return IncidenciasDAO::updateIncidencia((object)$req->all());
    }
}
