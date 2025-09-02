<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function show(Dokumen $dokumen)
    {
        return view('verify.document', compact('dokumen'));
    }
}
