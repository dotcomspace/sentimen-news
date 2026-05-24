<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryAnalisis extends Model
{
    protected $table = 'history_analisis';

    protected $fillable = [
        'judul_berita',
        'konten',
        'hasil_sentimen',
        'confidence_score',
    ];
}