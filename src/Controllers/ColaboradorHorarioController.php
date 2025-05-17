<?php

namespace App\Controllers;

use App\DAO\ColaboradorHorarioDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ColaboradorHorarioController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function getHorarioByEmpleado($id)
    {
        return ColaboradorHorarioDAO::getHorarioByEmpleado($id);
    }

    public function setHorario($id,Request $req){
        return ColaboradorHorarioDAO::setHorario($id,$req->all());
    }

    public function deleteDiaHorario($id){
        return ColaboradorHorarioDAO::deleteDiaHorario($id);
    }
    public function deleteFullHorario($id){
        return ColaboradorHorarioDAO::deleteFullHorario($id);
    }
}
