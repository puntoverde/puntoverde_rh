<?php

namespace App\DAO;
use Illuminate\Support\Facades\DB;


class PermisoFaltaDAO
{

   public function __construct()
   {
   }

   public static function getPermisoByEmpleadoId($id)
   {
      /**SELECT 
          colaborador_permisos.id_colaborador_permiso,
          colaborador_permisos.id_colaborador_encargado,
          colaborador_permisos.id_colaborador,
          colaborador_permisos.descripcion,
          colaborador_permisos.tipo,
          colaborador_permisos.estatus,
          colaborador.nomina,
          persona.nombre,
          persona.apellido_paterno,
          persona.apellido_materno
         FROM colaborador_permisos
         INNER JOIN colaborador ON colaborador_permisos.id_colaborador_encargado=colaborador.id_colaborador
         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
         WHERE colaborador_permisos.id_colaborador=1 */

      $permisos = DB::table("colaborador_permisos")
         ->join("colaborador","colaborador_permisos.id_colaborador_encargado","colaborador.id_colaborador")
         ->join("persona","colaborador.cve_persona","persona.cve_persona")
         ->select("colaborador_permisos.id_colaborador_permiso","colaborador_permisos.id_colaborador_encargado")
         ->addSelect("colaborador_permisos.id_colaborador","colaborador_permisos.descripcion")
         ->addSelect("colaborador_permisos.fecha_permiso","colaborador_permisos.tipo","colaborador_permisos.estatus")
         ->addSelect("colaborador.nomina","persona.nombre","persona.apellido_paterno","persona.apellido_materno")
         ->where("colaborador_permisos.id_colaborador", $id);      

      return $permisos->get();
   }

   public static function setPermiso($p)
   {
      return DB::transaction(function () use ($p){

         // $p=[{id_colaborador_encargado,1,id_colaborador:1,descripcion:'asuntos personalez',tipo:1,estatus:1}]

         DB::table('colaborador_permisos')->insert($p);
         return 1;

         });

   }

   public static function CancelarPermiso($id)
   {
    $flag_delete = DB::table('colaborador_permisos')->where("id_colaborador_permiso",$id)->whereDate('fecha_permiso','<=',date("Y-m-d"))->count();
    if($flag_delete==0)
    {
       DB::table('colaborador_permisos')->where("id_colaborador_permiso",$id)->delete();
       return 1;
    }
    else {
      DB::table('colaborador_permisos')->where("id_colaborador_permiso",$id)->update(["estatus"=>0]);
      return 0;
    }

   }

}
