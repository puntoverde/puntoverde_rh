<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;

use Illuminate\Support\Facades\DB;


class DiasAsuetoDAO
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
}
