<?php

namespace App\Controllers;

use App\DAO\DiasAsuetoDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class DiasAsuetoController extends Controller
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
        return DiasAsuetoDAO::getDiasAsueto($annio);
    }

    public function crateDiasAsueto(Request $req){
   
        return DiasAsuetoDAO::crateDiasAsueto($req->all());
    }

    public function deleteDiaAsueto($id){
        return DiasAsuetoDAO::deleteDiaAsueto($id);
    }
    public function deleteFullHorario($id){
        return DiasAsuetoDAO::deleteFullHorario($id);
    }
}
