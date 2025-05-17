<?php

namespace App\Entity;
use Illuminate\Database\Eloquent\Model;
// use App\Entity\Accionista;


class Colaborador extends Model 
{
    protected $table = 'colaborador';
    protected $primaryKey = 'id_colaborador';
    public $timestamps = false;

    // public function accionista()
    // {
    //     return $this->hasOne(Accionista::class,'cve_persona','cve_persona');
    // }

}