<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;

use Illuminate\Support\Facades\DB;


class IncidenciasDAO
{

   public function __construct()
   {
   }

   

   public static function getDiasAsueto($annio)
   {     
      return DB::table("rh_dias_asueto")
      ->whereRaw("YEAR(dia)=?",[$annio])
      ->orderBy("dia","desc")
      ->select('id_dia_asueto','dia','estatus')
      ->get();
   }

   public static function crateDiasAsueto($dias)
   {      
      $data=collect($dias)->map(function($i){return ["dia"=>$i];})->all();
      DB::table('rh_dias_asueto')->insert($data);
   }

   public static function deleteDiaAsueto($id)
   {
      try{
         return DB::table("rh_dias_asueto")->where("id_dia_asueto",$id)->whereRaw("dia >= CURDATE()")->delete();
      }
      catch(Exception $e)
      {
         return $e;
      }
   }

   public static function deleteFullHorario($id)
   {
      DB::table("colaborador_horario")->where("id_colaborador",$id)->delete();
   }


   public static function getColaboradoresArea($id)
   {
      /*SELECT 
         colaborador.id_colaborador,
         persona.nombre,
         persona.apellido_paterno,
         persona.apellido_materno 
      FROM rh_departamento
      INNER JOIN area_rh ON rh_departamento.id_departamento=area_rh.id_departamento
      INNER JOIN colaborador ON area_rh.id_area_rh=colaborador.id_area
      INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
      WHERE rh_departamento.jefe_departamento=(SELECT colaborador.id_colaborador FROM colaborador WHERE cve_persona=9601 LIMIT 1) 
      AND colaborador.id_colaborador !=(SELECT colaborador.id_colaborador FROM colaborador WHERE cve_persona=9601 LIMIT 1)*/

      return DB::table("rh_departamento")
      ->join("area_rh" , "rh_departamento.id_departamento","area_rh.id_departamento")
      ->join("colaborador" , "area_rh.id_area_rh","colaborador.id_area")
      ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
      // ->where("rh_departamento.jefe_departamento",function($query)use($id){$query->select("colaborador.id_colaborador")->from("colaborador")->where("cve_persona",$id)->limit(1);})
      // ->where("colaborador.id_colaborador","!=",function($query)use($id){$query->select("colaborador.id_colaborador")->from("colaborador")->where("cve_persona",$id)->limit(1);})
      ->select("colaborador.id_colaborador","colaborador.nomina","persona.nombre","persona.apellido_paterno","persona.apellido_materno")
      ->orderBy("persona.apellido_paterno")
      ->orderBy("persona.apellido_materno")
      ->orderBy("persona.nombre")
      ->get();
   }


   public static function getAllColaboradoresArea($id)
   {
      /*SELECT 
         colaborador.id_colaborador,
         persona.nombre,
         persona.apellido_paterno,
         persona.apellido_materno 
      FROM rh_departamento
      INNER JOIN area_rh ON rh_departamento.id_departamento=area_rh.id_departamento
      INNER JOIN colaborador ON area_rh.id_area_rh=colaborador.id_area
      INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
      WHERE rh_departamento.jefe_departamento=(SELECT colaborador.id_colaborador FROM colaborador WHERE cve_persona=9601 LIMIT 1) 
      AND colaborador.id_colaborador !=(SELECT colaborador.id_colaborador FROM colaborador WHERE cve_persona=9601 LIMIT 1)*/

      return DB::table("rh_departamento")
      ->join("area_rh" , "rh_departamento.id_departamento","area_rh.id_departamento")
      ->join("colaborador" , "area_rh.id_area_rh","colaborador.id_area")
      ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
      ->where("rh_departamento.jefe_departamento",function($query)use($id){$query->select("colaborador.id_colaborador")->from("colaborador")->where("cve_persona",$id)->limit(1);})
      // ->where("colaborador.id_colaborador","!=",function($query)use($id){$query->select("colaborador.id_colaborador")->from("colaborador")->where("cve_persona",$id)->limit(1);})
      ->select("colaborador.id_colaborador","colaborador.nomina","persona.nombre","persona.apellido_paterno","persona.apellido_materno")
      ->get();
   }

   public static function getTipoIncidencia()
   {
      return DB::table("rh_tipo_incidencia")->get();
   }

   public static function createIncidencia($p)
   {
      try{

         //por mientras se manda cve_persona puesto que no se tiene a colaborador en el localstorage
         $colaborador_encargado=DB::table("colaborador")->where("cve_persona",$p->id_colaborador_encargado)->value("id_colaborador");
           
         return DB::table("rh_incidencia")->insertGetId([
            "id_tipo_incidencia"=>$p->id_tipo_incidencia,
            "id_colaborador_encargado"=>$colaborador_encargado,
            "id_colaborador"=>$p->id_colaborador,
            // "id_colaborador_acceso"=>$p->id_colaborador_acceso,
            "fecha_incidencia"=>$p->fecha_incidencia,
            "descripcion"=>$p->descripcion,
            "estatus"=>1,
         ]);

      }
      catch(\Exception $e)
      {

      }
   }
   
   public static function updateIncidencia($p)
   {
      try{

         // dd($p->id_colaborador);

         DB::table("colaborador_vacaciones")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_vacaciones",$p->fecha_incidencia)->update(["estatus"=>0]);
         DB::table("colaborador_permisos")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_permiso",$p->fecha_incidencia)->update(["estatus"=>0]);
         DB::table("rh_incidencia")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_incidencia",$p->fecha_incidencia)->update(["estatus"=>0]);

         //por mientras se manda cve_persona puesto que no se tiene a colaborador en el localstorage
         $colaborador_encargado=DB::table("colaborador")->where("cve_persona",$p->id_colaborador_encargado)->value("id_colaborador");
           
         return DB::table("rh_incidencia")->insertGetId([
            "id_tipo_incidencia"=>$p->id_tipo_incidencia,
            "id_colaborador_encargado"=>$colaborador_encargado,
            "id_colaborador"=>$p->id_colaborador,
            "fecha_incidencia"=>$p->fecha_incidencia,
            "descripcion"=>$p->descripcion,
            "estatus"=>1,
         ]);

      }
      catch(\Exception $e)
      {

      }
   }


}
