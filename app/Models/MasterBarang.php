<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    use HasFactory;

    protected $table = 'master_barang'; // Nama tabel di HeidiSQL
    
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'ukuran_spesifikasi',
        'satuan',
        'stok_saat_ini',
        'stok_minimum'
    ];
}