<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Admin;
use App\Models\Kuwu;
use App\Models\Dokumen;

class TandaQr extends Model
{
    use HasFactory;

    protected $table = 'tanda_qrs';
    protected $fillable = [
        'data_qr',
        'tanggal_pembuatan',
        'id_admin',
        'id_kuwu',
        'id_dokumen',
    ];

    // Relationship with Dosen model
    public function kuwu()
    {
        return $this->belongsTo(Kuwu::class, 'id_kuwu');
    }

    // Relationship with Dokumen model
    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class, 'id_dokumen');
    }

    // Relationship with Ormawa model
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin');
    }
}
