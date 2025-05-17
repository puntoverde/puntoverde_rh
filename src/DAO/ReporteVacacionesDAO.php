<?php
namespace App\DAO;
use Illuminate\Support\Facades\DB;

class ReporteVacacionesDAO {

    public function __construct(){}
    
    
    public static function getColaboradorVacacionesRestantes($p){
       /*
        SELECT 
	        colaborador.id_colaborador,
	        colaborador.nomina,
	        persona.nombre,
	        persona.apellido_paterno,
	        persona.apellido_materno,
	        area_rh.nombre AS area_, 
	        SUM(IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL AND rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion IS NOT NULL,1,0)) AS dias_vacaciones 
        FROM colaborador
        INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
        LEFT JOIN area_rh ON area_rh.id_area_rh=colaborador.id_area
        LEFT JOIN rh_colaboradores_vacaciones ON colaborador.id_colaborador=rh_colaboradores_vacaciones.cve_colaborador
        LEFT JOIN colaborador_vacaciones ON rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion=colaborador_vacaciones.cve_rh_colaborador_vacacion
        WHERE colaborador.estatus=1 AND nomina IS NOT NULL 
        GROUP BY colaborador.id_colaborador


        //AND nomina='1007' AND CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE '%Jose leon%' AND area_rh.id_area_rh=9
     */

     
    
     $query=DB::table("colaborador")
     ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
     ->leftJoin("area_rh" , "area_rh.id_area_rh","colaborador.id_area")
     ->leftJoin("rh_colaboradores_vacaciones" , "colaborador.id_colaborador","rh_colaboradores_vacaciones.cve_colaborador")
     ->leftJoin("colaborador_vacaciones" , "rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion","colaborador_vacaciones.cve_rh_colaborador_vacacion")
     ->where("colaborador.estatus",1)
     ->whereNotNull("nomina")
     ->groupBy("colaborador.id_colaborador")
     ->select("colaborador.id_colaborador",
     "colaborador.nomina",
     "persona.nombre",
     "persona.apellido_paterno",
     "persona.apellido_materno",
     "area_rh.nombre AS area_")
     ->selectRaw("SUM(IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL AND rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion IS NOT NULL,1,0)) AS dias_vacaciones");

     if($p->nombre??false)
     {      
      $nombre_=$p->nombre;
      $query->whereRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) LIKE ?",["%".$nombre_."%"]);
     }
     if($p->nomina??false)
     {

      $query->where("nomina",$p->nomina);
     }
     if($p->area??false)
     {
      $query->where("area_rh.id_area_rh",$p->area);
     }

     return $query->get();

    }

    public static function getPrevieColaboradorVacacionesAnio($id_colaborador){
      /*
        SELECT 
            rh_colaboradores_vacaciones.cve_colaborador,
            YEAR(rh_colaboradores_vacaciones.dia_vacacion) AS anio,
            COUNT(rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion) AS total, 
            SUM(IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,1,0)) AS libres,
            SUM(IF(colaborador_vacaciones.id_colaborador_vaciones IS NOT NULL,1,0)) AS no_libres 
        FROM rh_colaboradores_vacaciones
        LEFT JOIN colaborador_vacaciones ON rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion=colaborador_vacaciones.cve_rh_colaborador_vacacion
        WHERE rh_colaboradores_vacaciones.cve_colaborador=42
        GROUP BY rh_colaboradores_vacaciones.cve_colaborador, YEAR(rh_colaboradores_vacaciones.dia_vacacion)
      */

     return  DB::table("rh_colaboradores_vacaciones")
      ->leftJoin("colaborador_vacaciones","rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion","colaborador_vacaciones.cve_rh_colaborador_vacacion")
      ->where("rh_colaboradores_vacaciones.cve_colaborador",$id_colaborador)
      ->groupBy("rh_colaboradores_vacaciones.cve_colaborador")
      ->groupByRaw("YEAR(rh_colaboradores_vacaciones.dia_vacacion)")
      ->select("rh_colaboradores_vacaciones.cve_colaborador")
      ->selectRaw("YEAR(rh_colaboradores_vacaciones.dia_vacacion) AS anio")
      ->selectRaw("COUNT(rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion) AS total")
      ->selectRaw("SUM(IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,1,0)) AS libres")
      ->selectRaw("SUM(IF(colaborador_vacaciones.id_colaborador_vaciones IS NOT NULL,1,0)) AS no_libres")
      ->get();
    }

    public static function getdetalleVacacionesByAnio($id_colaborador,$anio)
    {
        /*
            SELECT 
	            colaborador_vacaciones.fecha_vacaciones,
                colaborador_vacaciones.descripcion
            FROM rh_colaboradores_vacaciones
            LEFT JOIN colaborador_vacaciones ON rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion=colaborador_vacaciones.cve_rh_colaborador_vacacion
            WHERE rh_colaboradores_vacaciones.cve_colaborador=42 AND YEAR(rh_colaboradores_vacaciones.dia_vacacion)=2024
        */                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        

        return DB::table("rh_colaboradores_vacaciones")
        ->leftJoin("colaborador_vacaciones" , "rh_colaboradores_vacaciones.cve_rh_colaborador_vacacion","colaborador_vacaciones.cve_rh_colaborador_vacacion")
        ->where("rh_colaboradores_vacaciones.cve_colaborador",$id_colaborador)
        ->whereRaw("YEAR(rh_colaboradores_vacaciones.dia_vacacion)=?",[$anio])
        ->select("colaborador_vacaciones.fecha_vacaciones","colaborador_vacaciones.descripcion")
        ->get();
    }

    public static function getAreasByVacaciones()
    {
        /*
            SELECT id_area_rh,nombre AS area_ FROM area_rh WHERE estatus=1
        */                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        

        return DB::table("area_rh")
        ->where("estatus",1)
        ->select("id_area_rh","nombre AS area_")
        ->get();
    }

    public static function getFullVacaciones(){
        return DB::table("")
        ->where("estatus",1)
        ->select("id","nombre")
        ->get()
    }

}