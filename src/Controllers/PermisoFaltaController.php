<?php

namespace App\Controllers;

use App\DAO\PermisoFaltaDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class PermisoFaltaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    public function getPermisoByEmpleadoId($id)
    {
        return PermisoFaltaDAO::getPermisoByEmpleadoId($id);
    }
  

    public function setPermiso(Request $req)
    {
        $reglas = [
            "id_colaborador_encargado" => "required", 
            "id_colaborador" => "required", 
            "descripcion" => "required", 
            "tipo" => "required", 
            "estatus" => "required"
            ];

        $this->validate($req, $reglas);
        return PermisoFaltaDAO::setPermiso($req->all());
    }

    public function CancelarPermiso($id)
    {
        $flag =PermisoFaltaDAO::CancelarPermiso($id);
        if($flag==1)
        {
            return response([],204);
        }
        else {
            return response([],200);
           }
    }

}
