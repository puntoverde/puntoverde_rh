<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;

use Illuminate\Support\Facades\DB;


class ColaboradorHorarioDAO
{

   public function __construct()
   {
   }

   

   public static function getHorarioByEmpleado($id)
   {
      return DB::table("colaborador_horario")
      ->select('id_colaborador_horario','dia_entrada', 'hora_entrada', 'dia_salida', 'hora_salida')
      ->where('estatus', 1)
      ->where('id_colaborador', $id)
      ->get();
   }

   public static function setHorario($id,$p)
   {
      $data=array_map(function($i)use($id){return array_merge($i,["id_colaborador"=>$id,"estatus"=>1]);},$p);
      DB::table('colaborador_horario')->insert($data);
   }

   public static function deleteDiaHorario($id)
   {
      DB::table("colaborador_horario")->where("id_colaborador_horario",$id)->delete();
   }

   public static function deleteFullHorario($id)
   {
      DB::table("colaborador_horario")->where("id_colaborador",$id)->delete();
   }
}
