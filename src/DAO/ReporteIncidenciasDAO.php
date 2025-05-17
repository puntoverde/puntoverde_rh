<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;

use Illuminate\Support\Facades\DB;


class ReporteIncidenciasDAO
{

   public function __construct()
   {
   }

   

   public static function getReporteIncidencias($p)
   {  
     
      /*SELECT colaborador.id_colaborador,rh_departamento.nombre AS departamento,colaborador.nomina,persona.nombre,persona.apellido_paterno,persona.apellido_materno FROM colaborador
         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
         INNER JOIN area_rh ON colaborador.id_area=area_rh.id_area_rh
         INNER JOIN rh_departamento ON area_rh.id_departamento=rh_departamento.id_departamento
         WHERE colaborador.estatus=1 and area_rh.id_departamento=9 */

     $query=DB::table("colaborador")
     ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
     ->join("area_rh" , "colaborador.id_area","area_rh.id_area_rh")
     ->join("rh_departamento" , "area_rh.id_departamento","rh_departamento.id_departamento")
     ->where("colaborador.estatus",1)
     ->select("colaborador.id_colaborador");
   //   ->select("colaborador.id_colaborador","area_rh.id_departamento","rh_departamento.nombre AS departamento","colaborador.nomina","persona.nombre","persona.apellido_paterno","persona.apellido_materno")
   //   ->orderBy("rh_departamento.nombre");
     
     if($p->departamento??false)
     {        
        $query->where("area_rh.id_departamento",$p->departamento);
     }

     $colaboradores=$query->get()->map(function($item){
      return ($item->id_colaborador);      
   })->all();
   //   dd($colaboradores);

     /* consulta old 30-10-2024

               SELECT 
               SUM(tbl_incidencias.tolerancia) as tolerancia,
               SUM(tbl_incidencias.retardo_menor) as retardo_menor,
               SUM(tbl_incidencias.retardo_mayor) as retardo_mayor,
               SUM(tbl_incidencias.is_falta) as faltas,
               SUM(tbl_incidencias.is_asueto) as dias_asueto,
               SUM(tbl_incidencias.is_vacaciones) as vacaciones,
               SUM(tbl_incidencias.is_permiso) as permisos,
               SUM(tbl_incidencias.is_incidencia) as incidencias ,
               SUM(tbl_incidencias.prima_dominical) as prima_dominical,
               SUM(tbl_incidencias.horas_prima_dominical) as horas_prima_dominical
               FROM (SELECT 
                  tbl_dias.Date,
                  convert(MIN(colaborador_acceso.hora_acceso),TIME) AS entro,
                  colaborador_horario.hora_entrada,
                  TIMEDIFF(convert(MIN(colaborador_acceso.hora_acceso),TIME),colaborador_horario.hora_entrada) AS diferencia,
                  ifnull(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,convert(MIN(colaborador_acceso.hora_acceso),TIME)) BETWEEN 1 AND 5,0) AS tolerancia,
                  ifnull(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,convert(MIN(colaborador_acceso.hora_acceso),TIME)) BETWEEN 6 AND 10,0) AS retardo_menor,
                  ifnull(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,convert(MIN(colaborador_acceso.hora_acceso),TIME)) BETWEEN 11 AND  180,0) AS retardo_mayor,
                  if(convert(MIN(colaborador_acceso.hora_acceso),TIME) IS NULL AND colaborador_horario.hora_entrada IS NOT NULL AND if(rh_dias_asueto.id_dia_asueto IS NULL,0,1)=0 AND if(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1)=0 AND if(colaborador_permisos.id_colaborador_permiso IS NULL,0,1)=0 AND IF(rh_incidencia.id_incidencia IS NULL,0,1)=0,1,0) AS is_falta,
                  if(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
                  if(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
                  if(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
                  IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia,
                  IF(colaborador_acceso.hora_acceso IS NOT NULL AND dia_index=7,1,0) AS prima_dominical,
                  IF(dia_index=7,TIMEDIFF(MAX(colaborador_acceso.hora_acceso),MIN(colaborador_acceso.hora_acceso)),0) AS horas_prima_dominical

                  FROM (SELECT a.Date,(WEEKDAY(a.Date)+1) AS dia_index,ELT(WEEKDAY(a.DATE)+1, 
                               'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo') AS dia_name
                                 FROM (
                                       SELECT :fecha_fin - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY as Date
                                       from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
                                       cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
                                       cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
                                       cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as d
                                      ) a
                           WHERE a.Date BETWEEN  :fecha_inicio AND :fecha_fin) AS tbl_dias
                           LEFT JOIN colaborador_acceso ON tbl_dias.Date=CONVERT(colaborador_acceso.hora_acceso,DATE) AND colaborador_acceso.id_colaborador=:id_colaborador
                           LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.Date)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador=:id_colaborador
                           LEFT JOIN rh_dias_asueto ON tbl_dias.Date=rh_dias_asueto.dia
                           LEFT JOIN colaborador_vacaciones ON tbl_dias.Date=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=:id_colaborador
                           LEFT JOIN colaborador_permisos ON tbl_dias.Date=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=:id_colaborador
                           LEFT JOIN rh_incidencia ON tbl_dias.Date=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=:id_colaborador

               GROUP BY tbl_dias.Date ORDER BY tbl_dias.Date ASC) AS tbl_incidencias

      */


      /* nueva consulta 30-10-2024 se le quitaran las columnas old en la que esta en query 
      
               SELECT 
		         colaborador.nomina,
		         persona.nombre,
		         persona.apellido_paterno,
		         persona.apellido_materno,
		         rh_departamento.nombre AS departamento,
               SUM(tbl_incidencias.tolerancia) as tolerancia,
               SUM(tbl_incidencias.retardo_menor) as retardo_menor,
               SUM(tbl_incidencias.retardo_mayor) as retardo_mayor,
               SUM(tbl_incidencias.is_falta) as faltas,
               SUM(tbl_incidencias.is_asueto) as dias_asueto,
               SUM(tbl_incidencias.is_vacaciones) as vacaciones,
               SUM(tbl_incidencias.is_permiso) as permisos,
               SUM(tbl_incidencias.is_incidencia) as incidencias ,
               SUM(tbl_incidencias.prima_dominical) as prima_dominical,
               SUM(tbl_incidencias.horas_prima_dominical) as horas_prima_dominical
               FROM (SELECT 
                  #tbl_dias.dia,
                  #CONVERT(MIN(colaborador_acceso.hora_acceso),TIME) AS entro,
                  #CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME) AS entro2,
                  #colaborador_horario.hora_entrada,

                  #TIMEDIFF(CONVERT(MIN(colaborador_acceso.hora_acceso),TIME),colaborador_horario.hora_entrada) AS diferencia_old,
                  TIMEDIFF(CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME),colaborador_horario.hora_entrada) AS diferencia,

                  #ifnull(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(colaborador_acceso.hora_acceso),TIME)) BETWEEN 1 AND 5,0) AS tolerancia_old,
                  IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME)) BETWEEN 1 AND 5,0) AS tolerancia,

                  #IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(colaborador_acceso.hora_acceso),TIME)) BETWEEN 6 AND 10,0) AS retardo_menor_old,
                  IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME)) BETWEEN 6 AND 10,0) AS retardo_menor,

                  #ifnull(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(colaborador_acceso.hora_acceso),TIME)) BETWEEN 11 AND  180,0) AS retardo_mayor_old,
                  IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME)) BETWEEN 11 AND  180,0) AS retardo_mayor,

                  #IF(CONVERT(MIN(colaborador_acceso.hora_acceso),TIME) IS NULL AND colaborador_horario.hora_entrada IS NOT NULL AND IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1)=0 AND IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1)=0 AND IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1)=0 AND IF(rh_incidencia.id_incidencia IS NULL,0,1)=0,1,0) AS is_falta_old,
		         	IF(CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME) IS NULL AND colaborador_horario.hora_entrada IS NOT NULL AND IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1)=0 AND IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1)=0 AND IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1)=0 AND IF(rh_incidencia.id_incidencia IS NULL,0,1)=0,1,0) AS is_falta,

		         	IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
                  IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
                  IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
                  IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia,
                  #IF(colaborador_acceso.hora_acceso IS NOT NULL AND dia_index=7,1,0) AS prima_dominical_old,
                  IF(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso) IS NOT NULL AND dia_index=7,1,0) AS prima_dominical,

                  #IF(dia_index=7,TIMEDIFF(MAX(colaborador_acceso.hora_acceso),MIN(colaborador_acceso.hora_acceso)),0) AS horas_prima_dominical_old,
                  IF(dia_index=7,TIMEDIFF(MAX(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso))),0) AS horas_prima_dominical,

                  colaborador_horario.id_colaborador

                  FROM (SELECT a.dia,(WEEKDAY(a.dia)+1) AS dia_index
                                 FROM (
                                       SELECT '2024-10-31' - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS dia
                                       FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                                       CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                                       CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) As c
                                       CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d
                                      ) a
                           WHERE a.dia BETWEEN  '2024-10-01' AND '2024-10-31') AS tbl_dias
                           LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.dia)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador IN(40,42)
                           LEFT JOIN colaborador_acceso ON tbl_dias.dia=CONVERT(colaborador_acceso.hora_acceso,DATE) AND colaborador_acceso.id_colaborador=colaborador_horario.id_colaborador                  
                           LEFT JOIN rh_dias_asueto ON tbl_dias.dia=rh_dias_asueto.dia
                           LEFT JOIN colaborador_vacaciones ON tbl_dias.dia=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=colaborador_horario.id_colaborador
                           LEFT JOIN colaborador_permisos ON tbl_dias.dia=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=colaborador_horario.id_colaborador
                           LEFT JOIN rh_incidencia ON tbl_dias.dia=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=colaborador_horario.id_colaborador
                           LEFT JOIN rh_colaboradores_accesos_revisiones ON colaborador_acceso.id_colaborador_acceso=rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso OR (tbl_dias.dia=CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,Date) AND rh_colaboradores_accesos_revisiones.cve_colaborador=colaborador_horario.id_colaborador)

               GROUP BY tbl_dias.dia ,colaborador_horario.id_colaborador 
		         ORDER BY tbl_dias.dia ASC) AS tbl_incidencias 
		         INNER JOIN colaborador ON tbl_incidencias.id_colaborador=colaborador.id_colaborador
		         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
		         INNER JOIN area_rh ON colaborador.id_area = area_rh.id_area_rh
               INNER JOIN rh_departamento ON area_rh.id_departamento=rh_departamento.id_departamento
		         WHERE tbl_incidencias.id_colaborador IS NOT NULL 
		         GROUP BY tbl_incidencias.id_colaborador

      */

         

   //   $colaboradores->each(function ($item) use($p){


      
       
     $incidencias=DB::select("SELECT 
		         colaborador.nomina,
		         persona.nombre,
		         persona.apellido_paterno,
		         persona.apellido_materno,
		         rh_departamento.nombre AS departamento,
               SUM(tbl_incidencias.tolerancia) as tolerancia,
               SUM(tbl_incidencias.retardo_menor) as retardo_menor,
               SUM(tbl_incidencias.retardo_mayor) as retardo_mayor,
               SUM(tbl_incidencias.is_falta) as faltas,
               SUM(tbl_incidencias.is_asueto) as dias_asueto,
               SUM(tbl_incidencias.is_vacaciones) as vacaciones,
               SUM(tbl_incidencias.is_permiso) as permisos,
               SUM(tbl_incidencias.is_incidencia) as incidencias ,
               SUM(tbl_incidencias.prima_dominical) as prima_dominical,
               SUM(tbl_incidencias.horas_prima_dominical) as horas_prima_dominical
               FROM (SELECT                   
                  TIMEDIFF(CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME),colaborador_horario.hora_entrada) AS diferencia,               
                  IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME)) BETWEEN 1 AND 5,0) AS tolerancia,
                  IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME)) BETWEEN 6 AND 10,0) AS retardo_menor,
                  IFNULL(TIMESTAMPDIFF(MINUTE,colaborador_horario.hora_entrada,CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME)) BETWEEN 11 AND  180,0) AS retardo_mayor,
		         	IF(CONVERT(MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),TIME) IS NULL AND colaborador_horario.hora_entrada IS NOT NULL AND IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1)=0 AND IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1)=0 AND IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1)=0 AND IF(rh_incidencia.id_incidencia IS NULL,0,1)=0,1,0) AS is_falta,
		         	IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
                  IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
                  IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
                  IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia,
                  IF(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso) IS NOT NULL AND dia_index=7,1,0) AS prima_dominical,
                  IF(dia_index=7,TIMEDIFF(MAX(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso)),MIN(IFNULL(colaborador_acceso.hora_acceso,rh_colaboradores_accesos_revisiones.hora_acceso))),0) AS horas_prima_dominical,
                  colaborador_horario.id_colaborador

                  FROM (SELECT a.dia,(WEEKDAY(a.dia)+1) AS dia_index
                                 FROM (
                                       SELECT :fecha_fin - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS dia
                                       FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                                       CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                                       CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) As c
                                       CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d
                                      ) a
                           WHERE a.dia BETWEEN  :fecha_inicio AND :fecha_fin) AS tbl_dias
                           LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.dia)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador IN(".implode(',', $colaboradores).")
                           LEFT JOIN colaborador_acceso ON tbl_dias.dia=CONVERT(colaborador_acceso.hora_acceso,DATE) AND colaborador_acceso.id_colaborador=colaborador_horario.id_colaborador                  
                           LEFT JOIN rh_dias_asueto ON tbl_dias.dia=rh_dias_asueto.dia
                           LEFT JOIN colaborador_vacaciones ON tbl_dias.dia=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=colaborador_horario.id_colaborador
                           LEFT JOIN colaborador_permisos ON tbl_dias.dia=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=colaborador_horario.id_colaborador
                           LEFT JOIN rh_incidencia ON tbl_dias.dia=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=colaborador_horario.id_colaborador
                           LEFT JOIN rh_colaboradores_accesos_revisiones ON colaborador_acceso.id_colaborador_acceso=rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso OR (tbl_dias.dia=CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,Date) AND rh_colaboradores_accesos_revisiones.cve_colaborador=colaborador_horario.id_colaborador)

               GROUP BY tbl_dias.dia ,colaborador_horario.id_colaborador 
		         ORDER BY tbl_dias.dia ASC) AS tbl_incidencias 
		         INNER JOIN colaborador ON tbl_incidencias.id_colaborador=colaborador.id_colaborador
		         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
		         INNER JOIN area_rh ON colaborador.id_area = area_rh.id_area_rh
               INNER JOIN rh_departamento ON area_rh.id_departamento=rh_departamento.id_departamento
		         WHERE tbl_incidencias.id_colaborador IS NOT NULL 
		         GROUP BY tbl_incidencias.id_colaborador",["fecha_inicio"=>$p->fecha_inicio,"fecha_fin"=>$p->fecha_fin]);

// 

     
      // $item->tolerancia=$incidencias[0]->tolerancia;
      // $item->retardo_menor=$incidencias[0]->retardo_menor;
      // $item->retardo_mayor=$incidencias[0]->retardo_mayor;
      // $item->faltas=$incidencias[0]->faltas;
      // $item->dias_asueto=$incidencias[0]->dias_asueto;
      // $item->vacaciones=$incidencias[0]->vacaciones;
      // $item->permisos=$incidencias[0]->permisos;
      // $item->incidencias=$incidencias[0]->incidencias;
      // $item->prima_dominical=$incidencias[0]->prima_dominical;
      // $item->horas_prima_dominical=$incidencias[0]->horas_prima_dominical;

   
   
      // });//fin for each;

  
      return $incidencias;
   
   }

   public static function crateDiasAsueto($dias)
   {      
      $data=collect($dias)->map(function($i){return ["dia"=>$i];})->all();
      DB::table('rh_dias_asueto')->insert($data);
   }


   public static function getDepartamentos()
   {
      return DB::table("rh_departamento")->orderBy("rh_departamento.nombre")->select("id_departamento","nombre")->get();
   }

   public static function deleteDiaAsueto($id)
   {
      try{
         return DB::table("rh_dias_asueto")->where("id_dia_asueto",$id)->whereRaw("dia >= CURDATE()")->delete();
      }
      catch(\Exception $e)
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
      ->where("rh_departamento.jefe_departamento",function($query)use($id){$query->select("colaborador.id_colaborador")->from("colaborador")->where("cve_persona",$id)->limit(1);})
      ->where("colaborador.id_colaborador","!=",function($query)use($id){$query->select("colaborador.id_colaborador")->from("colaborador")->where("cve_persona",$id)->limit(1);})
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


   public static function ReporteIncidenciasColaboradoresByDia($fecha)
   {
      /*
         SELECT 
	         colaborador.id_colaborador,
	         colaborador.nomina,	
	         persona.apellido_paterno,
	         persona.apellido_materno,
	         persona.nombre,
	         CONCAT_WS('-',rh_departamento.nombre,area_rh.nombre) AS departamento_area,
	         GROUP_CONCAT(DISTINCT CONVERT(colaborador_acceso.hora_acceso,TIME)) AS asistencia,
	         colaborador.foto,
	         colaborador_horario.dia_entrada,
	         colaborador_horario.dia_salida,
	         GROUP_CONCAT(DISTINCT colaborador_horario.hora_entrada) AS hora_entrada,
	         GROUP_CONCAT(DISTINCT colaborador_horario.hora_salida) AS hora_salida
         FROM colaborador
         INNER JOIN persona ON colaborador.cve_persona=persona.cve_persona
         LEFT JOIN area_rh ON colaborador.id_area=area_rh.id_area_rh
         LEFT JOIN rh_departamento ON area_rh.id_departamento=rh_departamento.id_departamento
         LEFT JOIN colaborador_horario ON colaborador.id_colaborador=colaborador_horario.id_colaborador AND colaborador_horario.dia_entrada=WEEKDAY(CURDATE())+1
         LEFT JOIN colaborador_acceso ON colaborador.id_colaborador=colaborador_acceso.id_colaborador AND CONVERT(colaborador_acceso.hora_acceso,DATE) = CURDATE() 
         WHERE colaborador.estatus=1 AND colaborador.nomina IS NOT NULL 
         GROUP BY colaborador.id_colaborador ORDER BY persona.apellido_paterno,persona.apellido_materno,persona.nombre
      */

      return DB::table("colaborador")
      ->join("persona" , "colaborador.cve_persona","persona.cve_persona")
      ->leftJoin("area_rh" , "colaborador.id_area","area_rh.id_area_rh")
      ->leftJoin("rh_departamento" , "area_rh.id_departamento","rh_departamento.id_departamento")
      ->leftJoin("colaborador_horario" , function($join) use($fecha){$join->on("colaborador.id_colaborador","colaborador_horario.id_colaborador")->whereRaw("colaborador_horario.dia_entrada=WEEKDAY(?)+1",[$fecha]);})
      ->leftJoin("colaborador_acceso" ,function($join) use($fecha){ $join->on("colaborador.id_colaborador","colaborador_acceso.id_colaborador")->whereRaw("CONVERT(colaborador_acceso.hora_acceso,DATE) = ?",[$fecha]);})
      ->where("colaborador.estatus",1)
      ->whereNotNull("colaborador.nomina")
      ->groupBy("colaborador.id_colaborador")
      ->orderBy("persona.apellido_paterno")
      ->orderBy("persona.apellido_materno")
      ->orderBy("persona.nombre")
      ->select(
         "colaborador.id_colaborador",
         "colaborador.nomina",	
         "persona.apellido_paterno",
         "persona.apellido_materno",
         "persona.nombre",
         "colaborador_horario.dia_entrada",
         "colaborador_horario.dia_salida")
      ->selectRaw("CONCAT_WS('-',rh_departamento.nombre,area_rh.nombre) AS departamento_area")
      ->selectRaw("GROUP_CONCAT(DISTINCT CONVERT(colaborador_acceso.hora_acceso,TIME)) AS asistencia")
      ->selectRaw("GROUP_CONCAT(DISTINCT colaborador_horario.hora_entrada) AS hora_entrada")
      ->selectRaw("GROUP_CONCAT(DISTINCT colaborador_horario.hora_salida) AS hora_salida")
      // ->limit(20)
      ->get();

   }

   public static function ReporteIncidenciasColaboradorAuto($fecha,$id_colaborador)
   {
      /*
      SELECT 
         acceso_auto.entrada,acceso_auto.salida 
      FROM acceso_auto 
      INNER JOIN autos_usuario ON acceso_auto.id_auto_usuario=autos_usuario.id_auto_usuario
      INNER JOIN socios ON autos_usuario.cve_socio=socios.cve_socio
      INNER JOIN colaborador ON socios.cve_persona=colaborador.cve_persona
      WHERE acceso_auto.fecha=CURDATE() AND colaborador.id_colaborador=40
      */

      return DB::table("acceso_auto")
      ->join("autos_usuario" , "acceso_auto.id_auto_usuario","autos_usuario.id_auto_usuario")
      ->join("socios" , "autos_usuario.cve_socio","socios.cve_socio")
      ->join("colaborador" , "socios.cve_persona","colaborador.cve_persona")
      ->where("acceso_auto.fecha",$fecha)
      ->where("colaborador.id_colaborador",$id_colaborador)
      ->select("acceso_auto.entrada","acceso_auto.salida")
      ->get();

   }

}
