<?php

namespace App\Controllers;

use App\DAO\ReporteIncidenciasDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ReporteIncidenciasController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        ini_set('max_execution_time', '100'); 

    }

    public function getReporteIncidencias(Request $req)
    {
        
        return response()->json(ReporteIncidenciasDAO::getReporteIncidencias((object)$req->all()))->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function getDepartamentos(){
   
        return ReporteIncidenciasDAO::getDepartamentos();
    }

    public function crateDiasAsueto(Request $req){
   
        return ReporteIncidenciasDAO::crateDiasAsueto($req->all());
    }

    public function deleteDiaAsueto($id){
        return ReporteIncidenciasDAO::deleteDiaAsueto($id);
    }
    public function deleteFullHorario($id){
        return ReporteIncidenciasDAO::deleteFullHorario($id);
    }
    
    public function getColaboradoresArea($id){
        return ReporteIncidenciasDAO::getColaboradoresArea($id);
    }

    public function getTipoIncidencia(){
        return ReporteIncidenciasDAO::getTipoIncidencia();
    }
    
    public function createIncidencia(Request $req){
        return ReporteIncidenciasDAO::createIncidencia((object)$req->all());
    }

    public function ReporteIncidenciasColaboradoresByDia(Request $req)
    {
        $this->validate($req, ["fecha"=>"required|date"]);
        $fecha=$req->input("fecha");
        return response()->json(ReporteIncidenciasDAO::ReporteIncidenciasColaboradoresByDia($fecha))->setEncodingOptions(JSON_NUMERIC_CHECK); 
    }
    
    
    public function ReporteIncidenciasColaboradorAuto(Request $req)
    {
        $this->validate($req, ["fecha"=>"required|date","id_colaborador"=>"required|integer"]);
        $fecha=$req->input("fecha");
        $id_colaborador=$req->input("id_colaborador");
        return response()->json(ReporteIncidenciasDAO::ReporteIncidenciasColaboradorAuto($fecha,$id_colaborador))->setEncodingOptions(JSON_NUMERIC_CHECK); 
    }
}
