<?php

namespace App\Controllers;

use App\DAO\CelebracionDAO;
use App\DAO\ColaboradorDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class CelebracionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getCelebraciones(){
        return response()->json(CelebracionDAO::getCelebraciones())->setEncodingOptions(JSON_NUMERIC_CHECK);      
    }
    public function getCelebracion($id){
        return response()->json(CelebracionDAO::getCelebracion($id))->setEncodingOptions(JSON_NUMERIC_CHECK);      
    }
    public function createCelebracion(Request $req){
        $reglas = [
            "celebracion" => "required", 
            "fecha" => "required"
            ];

        $this->validate($req, $reglas);
        return response()->json(CelebracionDAO::createCelebracion((object)$req->all()))->setEncodingOptions(JSON_NUMERIC_CHECK);      
    }
    public function deleteCelebracion(Request $req,$id){
        $reglas = ["motivo" => "required"];
        $this->validate($req, $reglas);
        return response()->json(CelebracionDAO::deleteCelebracion($id,(object)$req->all()))->setEncodingOptions(JSON_NUMERIC_CHECK);      
    }

}