<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;


class ColaboradorDAO
{

   public function __construct() {}

   public static function getEmpleados($p)
   {

      /**SELECT colaborador.nomina,colaborador.nomina_reloj,CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS colaborador, persona.curp,persona.rfc, colaborador.fecha_ingreso,colaborador.fecha_baja,colaborador.estatus 
          FROM colaborador
          INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
          WHERE CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE '%jose leon %' */

      $empleados = DB::table("colaborador")
         ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
         ->leftJoin("area_rh", "colaborador.id_area", "area_rh.id_area_rh")
         ->leftJoin("rh_departamento", "area_rh.id_departamento", "rh_departamento.id_departamento")
         ->select(
            "colaborador.id_colaborador", 
            "colaborador.nomina", 
            "colaborador.nomina_reloj", 
            "persona.curp", 
            "persona.rfc")
         ->addSelect(
            "colaborador.fecha_ingreso", 
            "colaborador.fecha_baja", 
            "colaborador.estatus", 
            "persona.nombre", 
            "persona.apellido_paterno AS paterno", 
            "persona.apellido_materno AS materno")
         ->SelectRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) as empleado")
         ->addSelect(DB::raw("IFNULL(rh_departamento.id_departamento,0) AS id_departamento"), DB::raw("IFNULL(rh_departamento.nombre,'') AS departamento_colaborador"))
         ->addSelect(DB::raw("IFNULL(area_rh.id_area_rh,0) AS id_area_rh"), DB::raw("IFNULL(area_rh.nombre,'') AS area_colaborador"));
      // DB::raw("IFNULL(rh_departamento.id_area_rh,0) AS id_area_rh")      
      if ($p->nomina ?? false) {
         $empleados->where("colaborador.nomina", $p->nomina);
      }

      if ($p->colaborador ?? false) {
         $empleados->whereRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE ?", ["%" . $p->colaborador . "%"]);
      }

      if ($p->curp ?? false) {
         $empleados->where("persona.curp", $p->curp);
      }

      if ($p->rfc ?? false) {
         $empleados->where("persona.rfc", $p->rfc);
      }

      if ($p->fecha_ingreso ?? false) {
         $empleados->where("colaborador.fecha_ingreso", $p->fecha_ingreso);
      }

      if (is_numeric($p->estatus ?? false)) {
         $empleados->where("colaborador.estatus", $p->estatus);
      }

      $empleados
         // ->orderBy("colaborador.estatus","desc")
         // ->orderByRaw("case colaborador.estatus when 0 then 4 when 1 then 1 when 2 then 2 when 3 then 3 else 4 end")
         ->orderByRaw("case colaborador.estatus when 0 then 4 else colaborador.estatus end")
         ->orderBy("persona.apellido_paterno")
         ->orderBy("persona.apellido_materno")
         ->orderBy("persona.nombre")
      ;

      return $empleados->get();
   }

   public static function getEmpleadoById($id)
   {
      $empleados = DB::table("colaborador")
         ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
         ->leftJoin("direccion", "colaborador.cve_direccion", "direccion.cve_direccion")
         ->leftJoin("colonia", "direccion.cve_colonia", "colonia.cve_colonia")
         ->leftJoin("municipio", "colonia.cve_municipio", "municipio.cve_municipio")
         ->leftJoin("estado", "municipio.cve_estado", "estado.cve_estado")
         ->leftJoin("area_rh", "colaborador.id_area", "area_rh.id_area_rh")
         ->select("id_colaborador", "colaborador.nomina", "colaborador.nomina_reloj", "persona.curp", "persona.rfc")
         ->addSelect("persona.sexo", "persona.fecha_nacimiento", "persona.estado_civil", "persona.cve_persona")
         ->addSelect("colaborador.fecha_ingreso", "colaborador.fecha_baja", "colaborador.estatus")
         ->addSelect("area_rh.id_departamento", "colaborador.id_area")
         ->addSelect('persona.nombre', 'persona.apellido_paterno', 'persona.apellido_materno')
         ->addSelect('calle', 'colonia.cp', 'colonia.nombre AS colonia', "colonia.cve_colonia", "direccion.cve_direccion")
         ->addSelect('numero_exterior', 'numero_interior', 'municipio.nombre as municipio', "estado.nombre as estado")
         ->where("colaborador.id_colaborador", $id);

      // $horario = DB::table("colaborador_horario")->select('dia_entrada', 'hora_entrada', 'dia_salida', 'hora_salida')->where('estatus', 1)->where('id_colaborador', $id);

      return $empleados->first();
   }

   public static function getAccionByNameOrNomina($name_or_nomina)
   {
      /**SELECT colaborador.id_colaborador,colaborador.nomina,CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS colaborador 
    FROM colaborador
    INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
    WHERE CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE '%jose leon %' */

      $empleados = DB::table("colaborador")
         ->join("persona", "colaborador.cve_persona", "persona.cve_persona")
         ->leftJoin("area_rh", "colaborador.id_area", "area_rh.id_area_rh")
         ->leftJoin("rh_departamento", "area_rh.id_departamento", "rh_departamento.id_departamento")
         ->select("id_colaborador", "colaborador.nomina", "rh_departamento.nombre AS departamento")
         ->SelectRaw("CONCAT_WS(' ',persona.apellido_paterno,persona.apellido_materno,persona.nombre) AS empleado")
         ->where("colaborador.estatus", 1)
         ->whereRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE ?", ["%" . $name_or_nomina . "%"])
         ->orWhere("colaborador.nomina", "like", "%" . $name_or_nomina . "%")
         ->orderBy("persona.apellido_paterno")
         ->orderBy("persona.apellido_materno")
         ->orderBy("persona.nombre");

      return $empleados->get();
   }

   public static function setEmpleado($p)
   {
      return DB::transaction(function () use ($p) {

         $colonia = Colonia::find($p->cve_colonia);

         $persona = new Persona();
         $persona->nombre = $p->nombre;
         $persona->apellido_paterno = $p->paterno;
         $persona->apellido_materno = $p->materno;
         $persona->sexo = $p->genero;
         $persona->fecha_nacimiento = $p->fecha_nac;
         $persona->cve_pais = 121;
         $persona->curp = $p->curp;
         $persona->rfc = $p->rfc;
         $persona->estado_civil = $p->estado_civil;
         $persona->estatus = 1;
         $persona->save();

         $direccion = new Direccion();
         $direccion->calle = $p->calle;
         $direccion->numero_exterior = $p->num_ext;
         $direccion->numero_interior = $p->num_int;
         $direccion->colonia()->associate($colonia);
         $direccion->save();

         $colaborador = new Colaborador();
         $colaborador->cve_persona = $persona->cve_persona;
         $colaborador->cve_direccion = $direccion->cve_direccion;
         // $colaborador->id_departamento=$p->id_departamento;
         $colaborador->id_area = $p->id_area;
         $colaborador->nomina = $p->nomina;
         $colaborador->nomina_reloj = $p->nomina_reloj;
         $colaborador->fecha_ingreso = $p->fecha_ingreso;
         // $colaborador->fecha_baja=$p->fecha_baja;
         $colaborador->estatus = 1;

         $colaborador->save();

         //se agrega esto para cada vez que se agrege un colaborador crear un socio para la accion 4000
         DB::table("socios")->insertGetId([
            "cve_persona" => $persona->cve_persona,
            "cve_direccion" => $direccion->cve_direccion,
            "cve_profesion" => 8, //8 es empleado
            "cve_parentesco" => 16, //16 es empleado
            "cve_accion" => 1914, //es la accion 4000
            "posicion" => $p->nomina, //es el numero de nomina que ingresen sewra su numero de posicion
            "observaciones" => "es desde el alta de colaborador por rh",
            "estatus" => 1,
            "fecha_ingreso_accion" => $p->fecha_ingreso,
            "fecha_ingreso_club" => $p->fecha_ingreso,
            "fecha_alta" => $p->fecha_ingreso,
            "fecha_aceptacion" => $p->fecha_ingreso, //es para que diga que el contador 6ya lo reviso
         ]);


         return 1;
      });
   }

   public static function updateEmpleado($id, $p)
   {
      return DB::transaction(function () use ($id, $p) {

         $colonia = Colonia::find($p->cve_colonia);

         $persona = Persona::find($p->cve_persona);
         $persona->nombre = $p->nombre;
         $persona->apellido_paterno = $p->paterno;
         $persona->apellido_materno = $p->materno;
         $persona->sexo = $p->genero;
         $persona->fecha_nacimiento = $p->fecha_nac;
         $persona->cve_pais = 121;
         $persona->curp = $p->curp;
         $persona->rfc = $p->rfc;
         $persona->estado_civil = $p->estado_civil;
         $persona->estatus = 1;
         $persona->save();

         $direccion = Direccion::find($p->cve_direccion);
         $direccion->calle = $p->calle;
         $direccion->numero_exterior = $p->num_ext;
         $direccion->numero_interior = $p->num_int;
         $direccion->colonia()->associate($colonia);
         $direccion->save();

         $colaborador = Colaborador::find($id);
         $colaborador->nomina = $p->nomina;
         $colaborador->nomina_reloj = $p->nomina_reloj;
         // $colaborador->id_departamento=$p->id_departamento;
         $colaborador->id_area = $p->id_area;
         $colaborador->fecha_ingreso = $p->fecha_ingreso;
         // $colaborador->fecha_baja=$p->fecha_baja;
         // $colaborador->estatus=1;      

         $colaborador->save();

         return 1;
      });
   }

   public static function bajaColaborador($id)
   {
      $colaborador = Colaborador::find($id);
      $colaborador->fecha_baja = Carbon::now();
      $colaborador->estatus = 0;
      $colaborador->save();
   }

   public static function reingresoColaborador($id)
   {
      $colaborador = Colaborador::find($id);
      $colaborador->fecha_reingreso = Carbon::now();
      $colaborador->estatus = 2;
      $colaborador->save();
   }

   public static function getDepartamentos()
   {
      return DB::table('rh_departamento')->select("id_departamento", "nombre", "jefe_departamento AS encargado")->where("estatus", 1)->get();
   }

   public static function getAreaByDepartamento($id)
   {
      return DB::table('area_rh')->select("id_area_rh", "nombre", "responsable AS encargado")->where("estatus", 1)->where("id_departamento", $id)->get();
   }

   public static function setJefeDepartamento($id_departamento, $id_colaborador)
   {
      //  return DB::table('area_rh')->where("id_area_rh",$id_area)->update(["jefe_area"=>$id_colaborador]);
      return DB::table('rh_departamento')->where("id_departamento", $id_departamento)->update(["jefe_departamento" => $id_colaborador]);
   }

   public static function getBeneficiario($id_colaborador)
   {

      /*
         SELECT 
	         colaborador_beneficiario.id_colaborador_beneficiario,
	         colaborador_beneficiario.id_parentesco,
	         colaborador_beneficiario.nombre,
	         colaborador_beneficiario.paterno,
	         colaborador_beneficiario.materno,
	         colaborador_beneficiario.contacto,
	         colaborador_beneficiario.domicilio,
	         rh_parentescos.nombre
         FROM colaborador_beneficiario
         INNER JOIN rh_parentescos ON colaborador_beneficiario.id_parentesco=rh_parentescos.cve_rh_parentesco
         WHERE colaborador_beneficiario.id_colaborador=42
      */
      return DB::table("colaborador_beneficiario")
         ->join("rh_parentescos", "colaborador_beneficiario.id_parentesco", "rh_parentescos.cve_rh_parentesco")
         ->where("colaborador_beneficiario.id_colaborador", $id_colaborador)
         ->select(
            "colaborador_beneficiario.id_colaborador_beneficiario",
            "colaborador_beneficiario.id_parentesco",
            "colaborador_beneficiario.nombre",
            "colaborador_beneficiario.paterno",
            "colaborador_beneficiario.materno",
            "colaborador_beneficiario.contacto",
            "colaborador_beneficiario.domicilio",
            "rh_parentescos.nombre AS parentesco_name"
         )
         ->get();
   }

   public static function createBeneficiario($p)
   {
      return DB::table("colaborador_beneficiario")->insertGetId([
         "id_colaborador" => $p->id_colaborador,
         "id_parentesco" => $p->id_parentesco,
         "nombre" => $p->nombre,
         "paterno" => $p->paterno,
         "materno" => $p->materno,
         "contacto" => $p->contacto,
         "domicilio" => $p->domicilio
      ]);
   }

   public static function getEscolaridad($id_colaborador)
   {
      /*
         SELECT 
	         colaborador_escolaridad.id_colaborador_escolaridad,
	         colaborador_escolaridad.nivel_escolaridad,
	         colaborador_escolaridad.nombre_institucion_curso,
	         colaborador_escolaridad.anio_curso,
	         colaborador_escolaridad.estatus,
	         colaborador_escolaridad.evidencia
         FROM colaborador_escolaridad
         WHERE colaborador_escolaridad.id_colaborador=42
      */

      return DB::table("colaborador_escolaridad")
         ->where("colaborador_escolaridad.id_colaborador", $id_colaborador)
         ->select(
            "colaborador_escolaridad.id_colaborador_escolaridad",
            "colaborador_escolaridad.nivel_escolaridad",
            "colaborador_escolaridad.nombre_institucion_curso",
            "colaborador_escolaridad.anio_curso",
            "colaborador_escolaridad.estatus",
            "colaborador_escolaridad.evidencia"
         )
         ->get();
   }

   public static function createEscolaridad($p, $evidencia)
   {
      return DB::table("colaborador_escolaridad")->insertGetId([
         "id_colaborador" => $p->id_colaborador,
         "nivel_escolaridad" => $p->nivel_escolaridad,
         "nombre_institucion_curso" => $p->nombre_institucion_curso,
         "anio_curso" => $p->anio_curso,
         "estatus" => $p->estatus,
         "evidencia" => $evidencia,
      ]);
   }


   public static function getParentesco()
   {
      return DB::table("rh_parentescos")->where("estatus", 1)->select("cve_rh_parentesco", "nombre")->get();
   }

   public static function getExpediente($id)
   {
      /*
         SELECT 
            rh_colaborador_expedientes.cve_rh_colaborador_expediente,
            rh_documentos.cve_rh_documento,
            rh_documentos.nombre,
            rh_colaborador_expedientes.documento 
         FROM rh_documentos 
         LEFT JOIN rh_colaborador_expedientes ON rh_documentos.cve_rh_documento=rh_colaborador_expedientes.cve_rh_documento AND rh_colaborador_expedientes.cve_colaborador=42
      */
      return DB::table("rh_documentos")
         ->leftJoin("rh_colaborador_expedientes", function ($join) use ($id) {
            $join->on("rh_documentos.cve_rh_documento", "rh_colaborador_expedientes.cve_rh_documento")->where("rh_colaborador_expedientes.cve_colaborador", $id);
         })
         ->select(
            "rh_colaborador_expedientes.cve_rh_colaborador_expediente",
            "rh_documentos.cve_rh_documento",
            "rh_documentos.nombre",
            "rh_colaborador_expedientes.documento"
         )
         ->get();
   }



   public static function createExpediente($p, $doc)
   {
      return DB::table("rh_colaborador_expedientes")->insertGetId([
         "cve_rh_documento" => $p->cve_rh_documento,
         "cve_colaborador" => $p->cve_colaborador,
         "documento" => $doc,
         "estatus" => 1,
      ]);
   }
   
   public static function deleteExpediente($id)
   {
      return DB::table("rh_colaborador_expedientes")->where("cve_rh_colaborador_expediente",$id)->delete();
   }


   public static function getHistoricoColaboradorPermanencia($id)
   {

      /*
         SELECT fecha_inicio, CONVERT(GROUP_CONCAT(fecha_fin), DATE) AS fecha_fin, MAX(estatus) AS estatus_inicio,IF(GROUP_CONCAT(fecha_fin) IS NULL, NULL, MIN(estatus)) AS estatus_fin
         FROM colaborador_historico
         WHERE id_colaborador_historico=1
         GROUP BY fecha_inicio
      */

      return DB::table("colaborador_historico")->where("id_colaborador_historico", $id)
         ->select("fecha_inicio")
         ->selectRaw("CONVERT(GROUP_CONCAT(fecha_fin), DATE) AS fecha_fin")
         ->selectRaw("MAX(estatus) AS estatus_inicio")
         ->selectRaw("IF(GROUP_CONCAT(fecha_fin) IS NULL, NULL, MIN(estatus)) AS estatus_fin")
         ->groupBy("colaborador_historico.fecha_inicio")
         ->orderBy("fecha_inicio", "desc")
         ->get();
   }


   public static function ReporteAltasBajasColaborador($fecha_inicio,$fecha_fin,$estatus) {

      /*
         SELECT 
            colaborador.nomina,
            persona.nombre,
            persona.apellido_paterno,
            persona.apellido_materno,
            area_rh.nombre,
            rh_departamento.nombre,
            colaborador.fecha_ingreso,
            colaborador.fecha_baja 
         FROM colaborador 
         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
         LEFT JOIN area_rh ON colaborador.id_area=area_rh.id_area_rh
         LEFT JOIN rh_departamento ON area_rh.id_departamento=rh_departamento.id_departamento
         WHERE colaborador.estatus=1
      */

      return DB::table("colaborador")
      ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
      ->leftJoin("area_rh" , "colaborador.id_area","area_rh.id_area_rh")
      ->leftJoin("rh_departamento" , "area_rh.id_departamento","rh_departamento.id_departamento")
      ->where("colaborador.estatus",$estatus)
      ->where(function($where)use($fecha_inicio,$fecha_fin){
         $where->whereRaw("colaborador.fecha_ingreso BETWEEN ? AND ?",[$fecha_inicio,$fecha_fin])
         ->orWhereRaw("colaborador.fecha_baja BETWEEN ? AND ?",[$fecha_inicio,$fecha_fin]);
      })
      
      ->select(
            "colaborador.nomina",
            "persona.nombre",
            "persona.apellido_paterno",
            "persona.apellido_materno",
            "area_rh.nombre as area_",
            "rh_departamento.nombre as depa_",
            "colaborador.fecha_ingreso",
            "colaborador.fecha_baja"
      )
      ->get();

   }
}
