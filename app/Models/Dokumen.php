<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Admin;
use App\Models\Kuwu;
use App\Models\Kemahasiswaan;

class Dokumen extends Model
{
    use HasFactory;
    protected $table = 'dokumens';

    protected $fillable = [
        'file',
        'nomor_surat',
        'nama_pemohon',
        'qr_position_x',
        'qr_position_y',
        'qr_width',
        'qr_height',
        'status_dokumen',
        'is_signed',
        'qr_code_path',
        'kode_pengesahan',
        'tanggal_pengajuan',
        'tanggal_verifikasi',
        'keterangan',
        'keterangan_revisi',
        'keterangan_pengirim',
        'tanggal_revisi',
        'id_admin',
        'id_kuwu',
    ];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
    ];

    // Relationship with  Admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }

    // Relationship with Kuwu
    public function kuwu()
    {
        return $this->belongsTo(Kuwu::class, 'id_kuwu');
    }

}
