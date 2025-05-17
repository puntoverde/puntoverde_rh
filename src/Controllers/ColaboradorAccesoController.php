<?php

namespace App\Controllers;

use App\DAO\ColaboradorAccesoDAO;
use App\DAO\ColaboradorDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ColaboradorAccesoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getColaboradorByNomina($id){
        return response()->json(ColaboradorAccesoDAO::getColaboradorByNomina($id));        
    }

    public function saveAccesoTxt(Request $req)
    {
        $file=$req->file('txt_accesos');
        
        /*$temp = explode(".", $file->getClientOriginalName());
        $directorio='../upload/';
        $filename = round(microtime(true)) . '.' . end($temp);
        $file->move($directorio,$filename);*/

        $archivo = fopen($file->getRealPath(),"r");
        // dd($archivo);
        $array_time=array();
        while(!feof($archivo )){
            // Leyendo una linea
            $traer = fgets($archivo );
            // Imprimiendo una linea
            if(strpos($traer,"time")===0){            
                $check=explode('"',$traer);
                array_push($array_time,["time"=>$check[1],"nomina"=>$check[3]]);                
            }
        }

        $object_acceso=array_reduce($array_time,function(array $acumulador,array $element){
              $acumulador[$element['nomina']][]=$element;
              return $acumulador;      
        },[]);

       return  ColaboradorAccesoDAO::setAccesoEmpleados($object_acceso);
        
    }

    public function getAsistencias($id,Request $req)
    {
        // dd($req->input('cve_persona'));
        return ColaboradorAccesoDAO::getAsistenciaColaborador($id,$req->input('fecha'),$req->input('cve_persona'));
    }
    
    public function getAsistenciaColaboradorRevisar($id,Request $req)
    {
       
        return ColaboradorAccesoDAO::getAsistenciaColaboradorRevisar($id,$req->input('fecha'));
    }
    
    public function registroAccesoNuevoRevision(Request $req)
    {

        $reglas = [
            "cve_rh_colaborador_acceso" => "required_without:cve_colaborador",
            "cve_colaborador" => "required_without:cve_rh_colaborador_acceso",
            "hora_acceso" => "required",
            "colaborador_registra" => "required",
            "tipo" => "required",
            ];
        $this->validate($req, $reglas);
        // dd($req->all());
        return ColaboradorAccesoDAO::registroAccesoNuevoRevision((object)$req);
    }
    
    public function actualizarAccesoNuevoRevision($id,Request $req)
    {       
        $reglas = ["hora_acceso" => "required"];
        $this->validate($req, $reglas);
        return ColaboradorAccesoDAO::actualizarAccesoNuevoRevision($id,$req->input('hora_acceso'));
    }


    public function getAsistenciasFullColaboradores(Request $req)
    {
        return ColaboradorAccesoDAO::getAsistenciasFullColaboradores($req->input('fecha'));
    }

}