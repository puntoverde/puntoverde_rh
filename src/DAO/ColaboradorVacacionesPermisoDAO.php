<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class ColaboradorVacacionesPermisoDAO
{

   public function __construct()
   {
   }

   public static function getVacacionesByColaborador($id)
   {
      
      return DB::table("colaborador_vacaciones")->where("id_colaborador",$id)->orderBy("fecha_vacaciones","desc")->get();

   }

   public static function getPermisosByColaborador($id)
   {
      
      return DB::table("colaborador_permisos")->where("id_colaborador",$id)->orderBy("fecha_permiso","desc")->get();

   }


   public static function createVacacionesByColaborador($p)
   {
      // dd(collect($p->fecha_vacaciones)->count());

      // $is_vacacion_exist=DB::table("colaborador_vacaciones")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_vacaciones",$p->fecha_vacaciones)->exists();
      $is_vacacion_exist=DB::table("colaborador_vacaciones")->where("id_colaborador",$p->id_colaborador)->whereIn("fecha_vacaciones",$p->fecha_vacaciones)->exists();
      // dd($is_vacacion_exist);
      // $is_permiso_exist=DB::table("colaborador_permisos")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_permiso",$p->fecha_vacaciones)->exists();
      $is_permiso_exist=DB::table("colaborador_permisos")->where("id_colaborador",$p->id_colaborador)->whereIn("fecha_permiso",$p->fecha_vacaciones)->exists();
      // dd($is_permiso_exist);
      // $is_asueto_exist=DB::table("rh_dias_asueto")->whereDate("dia",$p->fecha_vacaciones)->exists();      
      $is_asueto_exist=DB::table("rh_dias_asueto")->whereIn("dia",$p->fecha_vacaciones)->exists();      
      // dd($is_asueto_exist);

      $disponibles= DB::table("rh_colaboradores_vacaciones")
      ->leftJoin("colaborador_vacaciones" , "rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion","colaborador_vacaciones.cve_rh_colaborador_vacacion" )
      ->where("rh_colaboradores_vacaciones.cve_colaborador",$p->id_colaborador)
      ->whereNull("colaborador_vacaciones.id_colaborador_vaciones")
      ->count("rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion");
      // dd($disponibles);
      // dd(collect($p->fecha_vacaciones)->count()>$disponibles);

      if($is_vacacion_exist || $is_permiso_exist || $is_asueto_exist || collect($p->fecha_vacaciones)->count()>$disponibles){
         // return '0';
         dd($disponibles);
      }
      

      //por mientras se manda cve_persona puesto que no se tiene a colaborador en el localstorage
      $colaborador_encargado=DB::table("colaborador")->where("cve_persona",$p->id_colaborador_encargado)->value("id_colaborador");
      // dd($colaborador_encargado);


      $disponibles_ids= DB::table("rh_colaboradores_vacaciones")
      ->leftJoin("colaborador_vacaciones" , "rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion","colaborador_vacaciones.cve_rh_colaborador_vacacion" )
      ->where("rh_colaboradores_vacaciones.cve_colaborador",$p->id_colaborador)
      ->whereNull("colaborador_vacaciones.id_colaborador_vaciones")
      ->select("rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion")
      ->get();

      // dd($disponibles_ids->map(function($item){return $item->cve_rh_colaborador_vacacion;})->zip($p->fecha_vacaciones));

      $data_insert=$disponibles_ids
         ->map(function($item){return $item->cve_rh_colaborador_vacacion;})
         ->zip($p->fecha_vacaciones)
         ->map(function($item,$key)use($colaborador_encargado,$p){
            return [
               "cve_rh_colaborador_vacacion"=>$item[0],
               "id_colaborador_encargado"=>$colaborador_encargado,
               "id_colaborador"=>$p->id_colaborador,
               "fecha_vacaciones"=>$item[1],
               "descripcion"=>$p->descripcion
              ];
         })
         ->whereNotNull("fecha_vacaciones")
         ->all();

         // dd($data_insert);

      // return DB::table("colaborador_vacaciones")->insertGetId(
      //    ["id_colaborador_encargado"=>$colaborador_encargado,
      //    "id_colaborador"=>$p->id_colaborador,
      //    "fecha_vacaciones"=>$p->fecha_vacaciones,
      //    "descripcion"=>$p->descripcion]);

      return DB::table("colaborador_vacaciones")->insert($data_insert);

   }


   public static function createPermisosByColaborador($p)
   {

      $is_vacacion_exist=DB::table("colaborador_vacaciones")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_vacaciones",$p->fecha_permiso)->exists();
      $is_permiso_exist=DB::table("colaborador_permisos")->where("id_colaborador",$p->id_colaborador)->whereDate("fecha_permiso",$p->fecha_permiso)->exists();
      $is_asueto_exist=DB::table("rh_dias_asueto")->whereDate("dia",$p->fecha_permiso)->exists();

      if($is_vacacion_exist || $is_permiso_exist || $is_asueto_exist){
         return '0';
      }

      //por mientras se manda cve_persona puesto que no se tiene a colaborador en el localstorage
      $colaborador_encargado=DB::table("colaborador")->where("cve_persona",$p->id_colaborador_encargado)->value("id_colaborador");

      return DB::table("colaborador_permisos")->insertGetId(
         ["id_colaborador_encargado"=>$colaborador_encargado,
         "id_colaborador"=>$p->id_colaborador,
         "fecha_permiso"=>$p->fecha_permiso,
         "descripcion"=>$p->descripcion,
         "tipo"=>$p->tipo
         ]);

   }


   public static function deleteVacacion($id)
   {
      return DB::table("colaborador_vacaciones")->where("id_colaborador_vaciones",$id)->delete();
   }

   public static function deletePermiso($id)
   {
      return DB::table("colaborador_permisos")->where("id_colaborador_permiso",$id)->delete();
   }

   public static function diaDisabled($id)
   {
      /*
      
      SELECT dia AS dia_disabled FROM rh_dias_asueto WHERE MONTH(rh_dias_asueto.dia)=MONTH(CURDATE()) AND YEAR(rh_dias_asueto.dia)=YEAR(CURDATE())
      UNION 
      SELECT fecha_vacaciones FROM colaborador_vacaciones WHERE MONTH(colaborador_vacaciones.fecha_vacaciones)=MONTH(CURDATE()) AND YEAR(colaborador_vacaciones.fecha_vacaciones)=YEAR(CURDATE()) AND colaborador_vacaciones.id_colaborador=42
      UNION 
      SELECT fecha_permiso FROM colaborador_permisos WHERE MONTH(colaborador_permisos.fecha_permiso)=MONTH(CURDATE()) AND YEAR(colaborador_permisos.fecha_permiso)=YEAR(CURDATE()) AND colaborador_permisos.id_colaborador=42

      */

       $dias_asueto=DB::table("rh_dias_asueto")->whereRaw("rh_dias_asueto.dia >= CURDATE()")->select("dia");
       $dias_vaciones=DB::table("colaborador_vacaciones")
                     ->whereRaw("colaborador_vacaciones.fecha_vacaciones >= CURDATE()")
                     ->where("colaborador_vacaciones.id_colaborador",$id)
                     ->select("fecha_vacaciones");
       $dias_permiso=DB::table("colaborador_permisos")
                     ->whereRaw("colaborador_permisos.fecha_permiso >= CURDATE()")
                     ->where("colaborador_permisos.id_colaborador",$id)
                     ->select("fecha_permiso");

      return $dias_asueto->union($dias_vaciones)->union($dias_permiso)->get()->map(function($i){return $i->dia;});


   }

   public static function getVacacionesDisponibles($id)
   {
      /*
         SELECT 
            COUNT(rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion) AS disponibles 
         FROM rh_colaboradores_vacaciones
         LEFT JOIN colaborador_vacaciones ON rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion=colaborador_vacaciones.cve_rh_colaborador_vacacion 
         WHERE rh_colaboradores_vacaciones.cve_colaborador=42 AND colaborador_vacaciones.id_colaborador_vaciones IS NULL 
      */

      return DB::table("rh_colaboradores_vacaciones")
      ->leftJoin("colaborador_vacaciones" , "rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion","colaborador_vacaciones.cve_rh_colaborador_vacacion" )
      ->where("rh_colaboradores_vacaciones.cve_colaborador",$id)
      ->whereNull("colaborador_vacaciones.id_colaborador_vaciones")
      ->count("rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion");
   }

 
}
