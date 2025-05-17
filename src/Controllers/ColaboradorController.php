<?php

namespace App\Controllers;

use App\DAO\ColaboradorDAO;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class ColaboradorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    public function getEmpleados(Request $req)
    {
        return ColaboradorDAO::getEmpleados((object)$req->all());
    }

    public function getEmpleadoById($id)
    {
        return response()->json(ColaboradorDAO::getEmpleadoById($id));
    }

    public function getAccionByNameOrNomina(Request $req)
    {
        return ColaboradorDAO::getAccionByNameOrNomina($req->input('name_or_nomina'));
    }

    public function setColaborador(Request $req)
    {
        $reglas = [
            "nombre" => "required",
            "paterno" => "required",
            "materno" => "required",
            "genero" => "required",
            "fecha_nac" => "required",
            "curp" => "required",
            "rfc" => "required",
            "estado_civil" => "required",
            "calle" => "required",
            "num_ext" => "required",
            // "num_int" => "required", 
            "cve_colonia" => "required",
            "nomina" => "required",
            "nomina_reloj" => "required",
            "fecha_ingreso" => "required",
            // "fecha_baja" => "required",
            "id_departamento" => "required|not_in:0",
            "id_area" => "required|not_in:0"
        ];

        $this->validate($req, $reglas);
        return ColaboradorDAO::setEmpleado((object)$req->all());
    }

    public function updateColaborador($id, Request $req)
    {
        $reglas = [
            "cve_persona" => "required",
            "cve_direccion" => "required",
            "nombre" => "required",
            "paterno" => "required",
            "materno" => "required",
            "genero" => "required",
            "fecha_nac" => "required",
            "curp" => "required",
            "rfc" => "required",
            "estado_civil" => "required",
            "calle" => "required",
            "num_ext" => "required",
            // "num_int" => "required", 
            "cve_colonia" => "required",
            "nomina" => "required",
            "nomina_reloj" => "required",
            "fecha_ingreso" => "required",
            // "fecha_baja" => "required",
            "id_departamento" => "required|not_in:0",
            "id_area" => "required|not_in:0"
        ];

        $this->validate($req, $reglas);
        return ColaboradorDAO::updateEmpleado($id, (object)$req->all());
    }

    public function bajaColaborador($id)
    {
        return ColaboradorDAO::bajaColaborador($id);
    }

    public function reingresoColaborador($id)
    {
        return ColaboradorDAO::reingresoColaborador($id);
    }

    public function getDepartamentos()
    {
        return ColaboradorDAO::getDepartamentos();
    }

    public function getAreaByDepartamento($id)
    {
        return ColaboradorDAO::getAreaByDepartamento($id);
    }

    public function setJefeDepartamento(Request $req)
    {
        return ColaboradorDAO::setJefeDepartamento($req->input("id_departamento"), $req->input("id_colaborador"));
    }

    public function getBeneficiario($id_colaborador)
    {
        return ColaboradorDAO::getBeneficiario($id_colaborador);
    }

    public function createBeneficiario(Request $req)
    {
        $reglas = [
            "id_colaborador" => "required",
            "id_parentesco" => "required",
            "nombre" => "required",
            "paterno" => "required",
            "materno" => "required",
            "contacto" => "required",
            "domicilio" => "required",
        ];

        $this->validate($req, $reglas);
        return ColaboradorDAO::createBeneficiario((object)$req->all());
    }

    public function getEscolaridad($id_colaborador)
    {
        return ColaboradorDAO::getEscolaridad($id_colaborador);
    }

    public function createEscolaridad(Request $req)
    {
        // dd($req->file("evidencia"));
        $directorio = '../evidencia_colaborador/';
        $reglas = [
            "id_colaborador" => "required",
            "nivel_escolaridad" => "required",
            "nombre_institucion_curso" => "required",
            "anio_curso" => "required",
            "estatus" => "required",
            "evidencia" => "required|file",
        ];
        $this->validate($req, $reglas);

        $file = $req->file('evidencia');
        $temp = explode(".", $file->getClientOriginalName());
        $filename = round(microtime(true)) . '.' . end($temp);
        $file->move($directorio, $filename);

        return ColaboradorDAO::createEscolaridad((object)$req->all(), $filename);
    }

    public function getParentesco()
    {
        return response()->json(ColaboradorDAO::getParentesco())->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function getFilesEvidencia(Request $req)
    {
        $file = $req->input('evidencia');
        $mime = mime_content_type("../evidencia_colaborador/$file");
        $file_ = file_get_contents("../evidencia_colaborador/$file");
        return response($file_)->header('Content-type', $mime);
    }

    public function getExpediente($id)
    {
        return response()->json(ColaboradorDAO::getExpediente($id))->setEncodingOptions(JSON_NUMERIC_CHECK);
    }


    public function createExpediente(Request $req)
    {
        $directorio = '../expediente_colaborador/';
        $reglas = [
            "cve_colaborador" => "required",
            "cve_rh_documento" => "required",
            "documento" => "required|file",
        ];
        $this->validate($req, $reglas);

        $file = $req->file('documento');
        $temp = explode(".", $file->getClientOriginalName());
        $filename = round(microtime(true)) . '.' . end($temp);
        $file->move($directorio, $filename);

        return ColaboradorDAO::createExpediente((object)$req->all(), $filename);
    }
    
    public function deleteExpediente(Request $req)
    {
        $id=$req->input("id");
        return ColaboradorDAO::deleteExpediente($id);
    }


    public function getHistoricoColaboradorPermanencia($id)
    {
        return ColaboradorDAO::getHistoricoColaboradorPermanencia($id);
    }

    public function ReporteAltasBajasColaborador(Request $req)
    {
        $fecha_inicio=$req->input("fecha_inicio");
        $fecha_fin=$req->input("fecha_fin");
        $estatus=$req->input("estatus");
        
        return ColaboradorDAO::ReporteAltasBajasColaborador($fecha_inicio,$fecha_fin,$estatus);
    }
}
