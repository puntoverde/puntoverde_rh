<?php

namespace App\DAO;

use App\Entity\Colonia;
use App\Entity\Persona;
use App\Entity\Direccion;
use App\Entity\Colaborador;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;


class ColaboradorAccesoDAO
{

   public function __construct()
   {
   }

   public static function getColaboradorByNomina($id)
   {
      
     $colaborador= DB::table("colaborador")
     ->join("persona","colaborador.cve_persona","persona.cve_persona")
     ->leftJoin("rh_departamento","colaborador.id_departamento","rh_departamento.id_departamento")
     ->select("colaborador.id_colaborador","rh_departamento.nombre AS departamento")
     ->selectRaw("CONCAT_WS(' ',persona.nombre,persona.apellido_paterno,persona.apellido_materno) AS empleado");
     $colaborador->where("colaborador.nomina",$id);
     
     return $colaborador->first();

   }

   public static function setAccesoEmpleados($p)
   {


      ini_set('max_execution_time', 300);
      foreach ($p as $key => $value) {

         $id_colaborador = DB::table('colaborador')->where('nomina_reloj', $key)->value('id_colaborador');

         if ($id_colaborador ?? false) {


            $insert_time = array_map(function ($i) use ($id_colaborador) {

               // if(DB::table('colaborador_acceso')->where("hora_acceso",$i["time"])->where("id_colaborador",$id_colaborador)->doesntExist())
               // {
               return ["id_colaborador" => $id_colaborador, "hora_acceso" => $i["time"]];
               // }
               // else{
               //    return ["id_colaborador"=>0,"hora_acceso"=>"00:00:00 00:00"];
               // }

            }, $value);



            // $insert_final=array_filter($insert_time,function($i){ return $i["id_colaborador"]>0;});

            // DB::table('colaborador_acceso')->insert($insert_final);


            Schema::create('temp_accesos_colaboradores', function (Blueprint $table) {
               $table->integer('id_colaborador');
               $table->string('hora_acceso');
               $table->temporary();
            });

            DB::table('temp_accesos_colaboradores')->insert($insert_time);

            $datos =
               DB::table('temp_accesos_colaboradores')
               ->leftJoin('colaborador_acceso', function ($join) {
                  $join->on('temp_accesos_colaboradores.id_colaborador', 'colaborador_acceso.id_colaborador');
                  $join->whereColumn('temp_accesos_colaboradores.hora_acceso', 'colaborador_acceso.hora_acceso');
               })
               ->whereNull('colaborador_acceso.id_colaborador_acceso')
               ->select('temp_accesos_colaboradores.id_colaborador', 'temp_accesos_colaboradores.hora_acceso')
               ->get();
            Schema::drop('temp_accesos_colaboradores');
            //  
            $insert_final = $datos->map(function ($item, $key) {
               return ["id_colaborador" => $item->id_colaborador, "hora_acceso" => $item->hora_acceso];
            })->toArray();
            DB::table('colaborador_acceso')->insert($insert_final);
         } else {
            // echo "no se encontro...";
         }
      }

      // });

      return ["ok" => 1000];
   }

   public static function getAsistenciaColaborador($id,$fecha,$cve_persona)
   {
      // dd($cve_persona);
      // --   ROW_NUMBER() OVER (ORDER BY tbl_dias.Date) AS index_row,
      // (@row_number:=@row_number + 1) AS index_row, 
      // DB::statement("SET @row_number = 0;");

      /*   ----------anterior
               SELECT 
                    1 as index_row,
                    colaborador_acceso.id_colaborador,
                    tbl_dias.Date,
                    tbl_dias.dia_index,
                    tbl_dias.dia_name,
                    GROUP_CONCAT(DISTINCT colaborador_horario.dia_entrada) AS  dia_entrada,
                    GROUP_CONCAT(DISTINCT colaborador_horario.hora_entrada) AS hora_entrada,
                    GROUP_CONCAT(DISTINCT colaborador_horario.dia_salida) AS dia_salida,
                    GROUP_CONCAT(DISTINCT colaborador_horario.hora_salida) AS hora_salida,
                    GROUP_CONCAT(DISTINCT CONVERT(colaborador_acceso.hora_acceso,TIME) ORDER BY CONVERT(colaborador_acceso.hora_acceso,TIME)) AS asistencia,                
                    CASE 
                        WHEN rh_dias_asueto.id_dia_asueto IS NOT NULL THEN CONCAT('DIA ASUETO','|',rh_dias_asueto.descripcion) 
                        WHEN colaborador_permisos.id_colaborador_permiso IS NOT NULL THEN CONCAT('PERMISO ',colaborador_permisos.tipo,'|',colaborador_permisos.descripcion)
                        WHEN colaborador_vacaciones.id_colaborador_vaciones IS NOT NULL THEN CONCAT('VACACION','|',colaborador_vacaciones.descripcion)
                        WHEN rh_incidencia.id_incidencia IS NOT NULL THEN CONCAT('INCIDENCIA ',rh_tipo_incidencia.nombre,'|',rh_incidencia.descripcion) ELSE NULL END AS descripcion,
                    IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
                    IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
                    IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
                    IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia,
                    :is_rh AS is_rh
               FROM (SELECT a.Date,(WEEKDAY(a.Date)+1) AS dia_index,ELT(WEEKDAY(a.DATE)+1, 
                   'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo') AS dia_name
                     FROM (
                           SELECT :fecha_fin - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY as Date
                           FROM (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
                           CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
                           CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
                           CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as d
                          ) a
               WHERE a.Date BETWEEN :fecha_ini AND :fecha_fin) AS tbl_dias 
               LEFT JOIN colaborador_acceso ON tbl_dias.Date=CONVERT(colaborador_acceso.hora_acceso,DATE) AND colaborador_acceso.id_colaborador=:idEmp
               LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.Date)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador=:idEmp
               LEFT JOIN colaborador_permisos ON tbl_dias.Date=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=:idEmp AND colaborador_permisos.estatus=1
               LEFT JOIN colaborador_vacaciones ON tbl_dias.Date=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=:idEmp AND colaborador_vacaciones.estatus=1
               LEFT JOIN rh_dias_asueto ON tbl_dias.Date=rh_dias_asueto.dia
               LEFT JOIN rh_incidencia ON tbl_dias.Date=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=:idEmp AND rh_incidencia.estatus=1
               LEFT JOIN rh_tipo_incidencia ON rh_tipo_incidencia.id_tipo_incidencia=rh_incidencia.id_tipo_incidencia
               GROUP BY tbl_dias.Date
               ORDER BY tbl_dias.Date DESC
      
      */




         $exist=DB::table('colaborador')->join("area_rh" , "colaborador.id_area","area_rh.id_area_rh")
         ->where('colaborador.cve_persona', $cve_persona)->where("area_rh.id_departamento",7)->exists();                                                                                                                                                                 

         $asistencias=DB::select(
               "SELECT 						               
                    tbl_dias.dia,                    
						  GROUP_CONCAT(DISTINCT colaborador_horario.dia_entrada) AS  dia_entrada,
                    GROUP_CONCAT(DISTINCT colaborador_horario.hora_entrada) AS hora_entrada,
                    GROUP_CONCAT(DISTINCT colaborador_horario.dia_salida) AS dia_salida,
                    GROUP_CONCAT(DISTINCT colaborador_horario.hora_salida) AS hora_salida,                    
                    GROUP_CONCAT(DISTINCT if(rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso is not NULL,NULL,convert(colaborador_acceso.hora_acceso,TIME)))AS asistencia,
                    GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=0 THEN convert(rh_colaboradores_accesos_revisiones.hora_acceso,TIME) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS asistencia_revision,
                    GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=1 THEN convert(rh_colaboradores_accesos_revisiones.hora_acceso,TIME) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS extras,
                    CASE 
                        WHEN rh_dias_asueto.id_dia_asueto IS NOT NULL AND rh_incidencia.id_incidencia IS NULL THEN CONCAT('DIA ASUETO','|',rh_dias_asueto.descripcion) 
                        WHEN colaborador_permisos.id_colaborador_permiso IS NOT NULL THEN CONCAT('PERMISO ',colaborador_permisos.tipo,'|',colaborador_permisos.descripcion)
                        WHEN colaborador_vacaciones.id_colaborador_vaciones IS NOT NULL THEN CONCAT('VACACION','|',colaborador_vacaciones.descripcion)
                        WHEN rh_incidencia.id_incidencia IS NOT NULL THEN CONCAT('INCIDENCIA ',rh_tipo_incidencia.nombre,'|',rh_incidencia.descripcion) ELSE NULL END AS descripcion,
                    IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
                    IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
                    IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
                    IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia
               FROM (SELECT a.dia,(WEEKDAY(a.dia)+1) AS dia_index
                     FROM (
                           SELECT :fecha_fin - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS dia
                           FROM (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
                           CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
                           CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
                           CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as d
                          ) a
               WHERE a.dia BETWEEN :fecha_ini AND :fecha_fin) AS tbl_dias
               LEFT JOIN colaborador on colaborador.id_colaborador= :idEmp
               LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.dia)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador=colaborador.id_colaborador
            	LEFT JOIN colaborador_acceso ON tbl_dias.dia=CONVERT(colaborador_acceso.hora_acceso,Date) AND colaborador_acceso.id_colaborador=colaborador.id_colaborador
            	LEFT JOIN colaborador_permisos ON tbl_dias.dia=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=colaborador_horario.id_colaborador AND colaborador_permisos.estatus=1
            	LEFT JOIN colaborador_vacaciones ON tbl_dias.dia=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=colaborador_horario.id_colaborador AND colaborador_vacaciones.estatus=1
            	LEFT JOIN rh_dias_asueto ON tbl_dias.dia=rh_dias_asueto.dia
            	LEFT JOIN rh_incidencia ON tbl_dias.dia=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=colaborador.id_colaborador AND rh_incidencia.estatus=1
            	LEFT JOIN rh_tipo_incidencia ON rh_tipo_incidencia.id_tipo_incidencia=rh_incidencia.id_tipo_incidencia
            	LEFT JOIN rh_colaboradores_accesos_revisiones ON colaborador_acceso.id_colaborador_acceso=rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso OR (tbl_dias.dia=CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,Date) AND rh_colaboradores_accesos_revisiones.cve_colaborador=colaborador_horario.id_colaborador)
               GROUP BY tbl_dias.dia
               ORDER BY tbl_dias.dia DESC",["idEmp"=>$id,"fecha_ini"=>$fecha[0],"fecha_fin"=>$fecha[1],"is_rh"=>$exist]);
         return $asistencias;
   }


   public static function getAsistenciaColaboradorRevisar($id,$fecha)
{
     /*
            SELECT                     
               tbl_dias.dia,
               colaborador_horario.id_colaborador,
               GROUP_CONCAT(DISTINCT CONCAT(colaborador_acceso.id_colaborador_acceso,'|',CONVERT(colaborador_acceso.hora_acceso,TIME))) AS asistencia,
               GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=0 THEN CONCAT(IFNULL(rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso,0),'|',rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso_revision,'|',CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,TIME)) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS asistencia_revision,
               GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=1 THEN CONCAT(IFNULL(rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso,0),'|',rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso_revision,'|',CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,TIME)) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS extras,
               IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
               IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
               IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
               IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia
            FROM (SELECT a.dia
                  FROM (
                     SELECT '2024-10-30' - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS dia
                     FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                     CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                     CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                     CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d
                       ) a
            WHERE a.dia BETWEEN '2024-10-01' AND '2024-10-30') AS tbl_dias
            LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.dia)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador=40
            LEFT JOIN colaborador_acceso ON tbl_dias.dia=CONVERT(colaborador_acceso.hora_acceso,Date) AND colaborador_acceso.id_colaborador=colaborador_horario.id_colaborador
            LEFT JOIN colaborador_permisos ON tbl_dias.dia=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=colaborador_horario.id_colaborador AND colaborador_permisos.estatus=1
            LEFT JOIN colaborador_vacaciones ON tbl_dias.dia=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=colaborador_horario.id_colaborador AND colaborador_vacaciones.estatus=1
            LEFT JOIN rh_dias_asueto ON tbl_dias.dia=rh_dias_asueto.dia
            LEFT JOIN rh_incidencia ON tbl_dias.dia=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=colaborador_horario.id_colaborador AND rh_incidencia.estatus=1
            LEFT JOIN rh_tipo_incidencia ON rh_tipo_incidencia.id_tipo_incidencia=rh_incidencia.id_tipo_incidencia
            LEFT JOIN rh_colaboradores_accesos_revisiones ON colaborador_acceso.id_colaborador_acceso=rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso OR (tbl_dias.dia=CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,Date) AND rh_colaboradores_accesos_revisiones.cve_colaborador=colaborador_horario.id_colaborador)
            GROUP BY tbl_dias.dia ORDER BY tbl_dias.dia DESC
     */
// dd($id);
     return DB::select("SELECT                     
               tbl_dias.dia,
               colaborador_horario.id_colaborador,
               GROUP_CONCAT(DISTINCT colaborador_horario.dia_entrada) AS  dia_entrada,
               GROUP_CONCAT(DISTINCT colaborador_horario.hora_entrada) AS hora_entrada,
               GROUP_CONCAT(DISTINCT colaborador_horario.dia_salida) AS dia_salida,
               GROUP_CONCAT(DISTINCT colaborador_horario.hora_salida) AS hora_salida,
               GROUP_CONCAT(DISTINCT CONCAT(colaborador_acceso.id_colaborador_acceso,'|',CONVERT(colaborador_acceso.hora_acceso,TIME))) AS asistencia,
               GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=0 THEN CONCAT(IFNULL(rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso,0),'|',rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso_revision,'|',CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,TIME)) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS asistencia_revision,
               GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=1 THEN CONCAT(IFNULL(rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso,0),'|',rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso_revision,'|',CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,TIME)) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS extras,
               CASE 
                        WHEN rh_dias_asueto.id_dia_asueto IS NOT NULL AND rh_incidencia.id_incidencia IS NULL THEN CONCAT('DIA ASUETO','|',rh_dias_asueto.descripcion) 
                        WHEN colaborador_permisos.id_colaborador_permiso IS NOT NULL THEN CONCAT('PERMISO ',colaborador_permisos.tipo,'|',colaborador_permisos.descripcion)
                        WHEN colaborador_vacaciones.id_colaborador_vaciones IS NOT NULL THEN CONCAT('VACACION','|',colaborador_vacaciones.descripcion)
                        WHEN rh_incidencia.id_incidencia IS NOT NULL THEN CONCAT('INCIDENCIA ',rh_tipo_incidencia.nombre,'|',rh_incidencia.descripcion) ELSE NULL END AS descripcion,
               IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
               IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
               IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
               IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia
            FROM (SELECT a.dia
                  FROM (
                     SELECT :fecha_fin - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS dia
                     FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                     CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                     CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                     CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d
                       ) a
            WHERE a.dia BETWEEN :fecha_ini AND :fecha_fin) AS tbl_dias
            LEFT JOIN colaborador ON colaborador.id_colaborador=:idEmp
            LEFT JOIN colaborador_horario ON (WEEKDAY(tbl_dias.dia)+1)=colaborador_horario.dia_entrada AND colaborador_horario.id_colaborador=colaborador.id_colaborador
            LEFT JOIN colaborador_acceso ON tbl_dias.dia=CONVERT(colaborador_acceso.hora_acceso,Date) AND colaborador_acceso.id_colaborador=colaborador.id_colaborador
            LEFT JOIN colaborador_permisos ON tbl_dias.dia=colaborador_permisos.fecha_permiso AND colaborador_permisos.id_colaborador=colaborador_horario.id_colaborador AND colaborador_permisos.estatus=1
            LEFT JOIN colaborador_vacaciones ON tbl_dias.dia=colaborador_vacaciones.fecha_vacaciones AND colaborador_vacaciones.id_colaborador=colaborador_horario.id_colaborador AND colaborador_vacaciones.estatus=1
            LEFT JOIN rh_dias_asueto ON tbl_dias.dia=rh_dias_asueto.dia
            LEFT JOIN rh_incidencia ON tbl_dias.dia=rh_incidencia.fecha_incidencia AND rh_incidencia.id_colaborador=colaborador.id_colaborador AND rh_incidencia.estatus=1
            LEFT JOIN rh_tipo_incidencia ON rh_tipo_incidencia.id_tipo_incidencia=rh_incidencia.id_tipo_incidencia
            LEFT JOIN rh_colaboradores_accesos_revisiones ON colaborador_acceso.id_colaborador_acceso=rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso OR (tbl_dias.dia=CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,Date) AND rh_colaboradores_accesos_revisiones.cve_colaborador=colaborador_horario.id_colaborador)
            GROUP BY tbl_dias.dia ORDER BY tbl_dias.dia DESC",["idEmp"=>$id,"fecha_ini"=>$fecha[0],"fecha_fin"=>$fecha[1]]);

}

public static function registroAccesoNuevoRevision($p)
{

   return DB::table("rh_colaboradores_accesos_revisiones")->insertGetId([
      "cve_rh_colaborador_acceso"=>$p->cve_rh_colaborador_acceso,
      "cve_colaborador"=>$p->cve_colaborador,
      "hora_acceso"=>$p->hora_acceso,
      "fecha_registro"=>Carbon::now(),
      "colaborador_registra"=>$p->colaborador_registra,
      "tipo"=>$p->tipo,
   ]);

}

public static function actualizarAccesoNuevoRevision($id,$p)
{

   return DB::table("rh_colaboradores_accesos_revisiones")->where("cve_rh_colaborador_acceso_revision",$id)->update(["hora_acceso"=>$p]);

}



public static function getAsistenciasFullColaboradores($fecha)
{
   return DB::select("SELECT 
						            c_colaborador.id_colaborador,
						            persona.nombre,
						            persona.apellido_paterno,
						            persona.apellido_materno,
						            rh_departamento.nombre AS departamento,
						            c_colaborador.nomina,
						            tbl_dias.dia,						  
						            GROUP_CONCAT(DISTINCT colaborador_horario.dia_entrada) AS  dia_entrada,
                              GROUP_CONCAT(DISTINCT colaborador_horario.hora_entrada) AS hora_entrada,
                              GROUP_CONCAT(DISTINCT colaborador_horario.dia_salida) AS dia_salida,
                              GROUP_CONCAT(DISTINCT colaborador_horario.hora_salida) AS hora_salida,                    
                              c_a.hora_ AS asistencia,
						            GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=0 THEN CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,TIME) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS asistencia_revision,
						            GROUP_CONCAT(DISTINCT CASE WHEN rh_colaboradores_accesos_revisiones.tipo=1 THEN CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,TIME) ELSE NULL END ORDER BY rh_colaboradores_accesos_revisiones.hora_acceso) AS extras,
                              CASE 
                                  WHEN rh_dias_asueto.id_dia_asueto IS NOT NULL THEN CONCAT('DIA ASUETO','|',rh_dias_asueto.descripcion) 
                                  WHEN colaborador_permisos.id_colaborador_permiso IS NOT NULL THEN CONCAT('PERMISO ',colaborador_permisos.tipo,'|',colaborador_permisos.descripcion)
                                  WHEN colaborador_vacaciones.id_colaborador_vaciones IS NOT NULL THEN CONCAT('VACACION','|',colaborador_vacaciones.descripcion)
                                  WHEN rh_incidencia.id_incidencia IS NOT NULL THEN CONCAT('INCIDENCIA ',rh_tipo_incidencia.nombre,'|',rh_incidencia.descripcion) ELSE NULL END AS descripcion,
                              IF(rh_dias_asueto.id_dia_asueto IS NULL,0,1) AS is_asueto,
                              IF(colaborador_permisos.id_colaborador_permiso IS NULL,0,1) AS is_permiso,
                              IF(colaborador_vacaciones.id_colaborador_vaciones IS NULL,0,1) AS is_vacaciones,
                              IF(rh_incidencia.id_incidencia IS NULL,0,1) AS is_incidencia
                     FROM (SELECT a.dia,(WEEKDAY(a.dia)+1) AS dia_index
                           FROM (
                                 SELECT :fecha_fin - INTERVAL (a.a + (10 * b.a) + (100 * c.a) + (1000 * d.a) ) DAY AS dia
                                 FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                                 CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                                 CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                                 CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS d
                                ) a
                     WHERE a.dia BETWEEN :fecha_ini AND :fecha_fin) AS tbl_dias 
                     CROSS JOIN colaborador AS c_colaborador
                     INNER JOIN persona ON c_colaborador.cve_persona=persona.cve_persona
                     LEFT JOIN area_rh ON c_colaborador.id_area=area_rh.id_area_rh
                     LEFT JOIN rh_departamento ON area_rh.id_departamento=rh_departamento.id_departamento
                     LEFT JOIN colaborador_horario ON c_colaborador.id_colaborador=colaborador_horario.id_colaborador AND (WEEKDAY(tbl_dias.dia)+1)=colaborador_horario.dia_entrada AND colaborador_horario.estatus=1
                     LEFT JOIN (
                              SELECT 
					                		colaborador_acceso.id_colaborador_acceso,
					               		colaborador_acceso.id_colaborador,
					               		CONVERT(colaborador_acceso.hora_acceso,DATE) AS dia_,
					               		GROUP_CONCAT(CONVERT(IFNULL(rh_colaboradores_accesos_revisiones.hora_acceso,colaborador_acceso.hora_acceso),TIME)) AS hora_					
                              FROM colaborador_acceso 
                              LEFT JOIN rh_colaboradores_accesos_revisiones ON colaborador_acceso.id_colaborador_acceso=rh_colaboradores_accesos_revisiones.cve_rh_colaborador_acceso
					               WHERE CONVERT(colaborador_acceso.hora_acceso,date) BETWEEN :fecha_ini AND :fecha_fin																	
					               GROUP BY CONVERT(colaborador_acceso.hora_acceso,DATE) , colaborador_acceso.id_colaborador
                              ) AS c_a ON c_colaborador.id_colaborador=c_a.id_colaborador AND tbl_dias.dia=c_a.dia_
                     LEFT JOIN rh_colaboradores_accesos_revisiones ON rh_colaboradores_accesos_revisiones.cve_colaborador=c_colaborador.id_colaborador AND tbl_dias.dia=CONVERT(rh_colaboradores_accesos_revisiones.hora_acceso,Date)               
                     LEFT JOIN colaborador_permisos ON c_colaborador.id_colaborador=colaborador_permisos.id_colaborador AND colaborador_permisos.estatus=1 AND tbl_dias.dia=colaborador_permisos.fecha_permiso
            	      LEFT JOIN colaborador_vacaciones ON c_colaborador.id_colaborador=colaborador_vacaciones.id_colaborador AND colaborador_vacaciones.estatus=1 AND tbl_dias.dia=colaborador_vacaciones.fecha_vacaciones
            	      LEFT JOIN rh_dias_asueto ON tbl_dias.dia=rh_dias_asueto.dia
            	      LEFT JOIN rh_incidencia ON c_colaborador.id_colaborador=rh_incidencia.id_colaborador AND rh_incidencia.estatus=1 AND tbl_dias.dia=rh_incidencia.fecha_incidencia
            	      LEFT JOIN rh_tipo_incidencia ON rh_tipo_incidencia.id_tipo_incidencia=rh_incidencia.id_tipo_incidencia      
                     WHERE c_colaborador.estatus=1               
                     GROUP BY tbl_dias.dia, c_colaborador.id_colaborador ORDER BY  c_colaborador.id_colaborador,tbl_dias.dia;
   ",["fecha_ini"=>$fecha[0],"fecha_fin"=>$fecha[1]]);
}



}


