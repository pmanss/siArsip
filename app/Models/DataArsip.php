<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataArsip extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = 'tb_arsip';
    protected $primaryKey = 'id_dok';
    protected $fillable = [
        'no_pen', 'status', 'box', 'batch', 'rak'
    ];

    function dokumen()
    {
        return $this->hasMany(Dokumen::class);
    }
}