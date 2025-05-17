<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;


class CelebracionDAO
{

   public function __construct()
   {
   }

   public static function getCelebraciones()
   {
      
     return  DB::table("celebracion")     
     ->select("id_celebracion","celebracion","fecha","estatus","motivo")
     ->get();     

   }
   
   public static function getCelebracion($id)
   {
      
     return  DB::table("celebracion")     
     ->where("id_celebracion",$id)
     ->select("id_celebracion","celebracion","fecha","estatus","motivo")
     ->first();     

   }

   public static function createCelebracion($p)
   {
      
     return  DB::table("celebracion")     
     ->insertGetId([
        "celebracion"=>$p->celebracion,
        "fecha"=>$p->fecha,
        "estatus"=>1,
     ]);

   }
   
   public static function deleteCelebracion($id,$p)
   {
      
     return  DB::table("celebracion") 
     ->where("id_celebracion",$id)   
     ->update([
        "estatus"=>0,
        "motivo"=>$p->motivo
     ]);

   }



 
}
