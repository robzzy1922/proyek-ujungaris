<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\TandaQr;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|mimes:pdf|max:10240',
        ]);

        $filePath = $request->file('document')->store('documents');

        $dokumen = Dokumen::create([
            'user_id' => auth()->id(),
            'file_path' => $filePath,
            'status' => 'pending',
        ]);

        return redirect()->route('dokumen.show', $dokumen);
    }

    public function show(Dokumen $dokumen)
    {
        return view('user.kuwu.show', compact('dokumen'));
    }

    public function saveBarcodePosition(Request $request, Dokumen $dokumen)
    {
        // Validasi posisi (dikirim dalam persen %)
        $request->validate([
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
        ]);

        // Simpan posisi QR dalam database (tetap simpan persen biar konsisten)
        $dokumen->update([
            'qr_position_x' => $request->x,
            'qr_position_y' => $request->y,
            'qr_width'      => $request->width,
            'qr_height'     => $request->height,
        ]);

        // Path file asli PDF & QR
        $pdfPath = storage_path('app/' . $dokumen->file_path);
        $qrCodePath = storage_path('app/public/' . $dokumen->qr_code_path);

        // Load PDF dengan FPDI
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            // Konversi persen → mm
            $x = ($request->x / 100) * $size['width'];
            $y = ($request->y / 100) * $size['height'];
            $w = ($request->width / 100) * $size['width'];
            $h = ($request->height / 100) * $size['height'];

            // Tempel QR di halaman pertama saja
            if ($pageNo === 1) {
                $pdf->Image($qrCodePath, $x, $y, $w, $h);
            }
        }

        // Simpan PDF baru
        $newPdfPath = 'documents/signed_' . $dokumen->id . '.pdf';
        Storage::put($newPdfPath, $pdf->Output('S'));

        // Update dokumen dengan file baru dan status
        $dokumen->update([
            'file_path' => $newPdfPath,
            'status_dokumen' => 'disahkan'
        ]);

        return response()->json(['success' => true, 'file' => $newPdfPath]);
    }


    public function insertBarcodeToPdf(Dokumen $dokumen)
    {
        $pdf = Pdf::loadFile(storage_path('app/' . $dokumen->file_path));

        // Posisikan barcode di PDF sesuai dengan data yang disimpan
    // Gunakan PDF library seperti DomPDF untuk sisipkan barcode ke dalam file PDF.

        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isPhpEnabled', true);

    // Generate PDF yang telah disertakan barcode
    $output = $pdf->output();

        // Simpan hasilnya atau kembalikan kepada pengguna
        file_put_contents(storage_path('app/documents/signed_' . $dokumen->id . '.pdf'), $output);
    }



public function generateQrCode(Dokumen $dokumen)
{
    try {
        // Buat URL verifikasi
        $verifyUrl = route('dokumen.verify', $dokumen->id);

        // Generate QR Code langsung dengan URL
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($verifyUrl);

        // Simpan file QR di storage
        $qrPath = 'qrcodes/doc_' . $dokumen->id . '_' . time() . '.png';
        Storage::disk('public')->put($qrPath, $qrCode);

        // Simpan metadata QR ke tabel tanda_qrs
        TandaQr::create([
            'data_qr' => $verifyUrl, // simpan URL (bukan JSON lagi)
            'tanggal_pembuatan' => now(),
            'id_admin' => $dokumen->id_admin,
            'id_kuwu' => auth()->id(),
            'id_dokumen' => $dokumen->id
        ]);

        // Update dokumen dengan path QR code
        $dokumen->update([
            'qr_code_path' => $qrPath
        ]);

        return response()->json([
            'success' => true,
            'qrCodeUrl' => asset('storage/' . $qrPath),
            'verifyUrl' => $verifyUrl
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate QR code: ' . $e->getMessage()
        ], 500);
    }
}



    public function viewDocument($id)
    {
        $dokumen = Dokumen::findOrFail($id);

        if (!$dokumen->file || !Storage::disk('public')->exists($dokumen->file)) {
            abort(404, 'Document not found');
        }

        $path = Storage::disk('public')->path($dokumen->file);
        $content = file_get_contents($path);
            $mimeType = Storage::disk('public')->mimeType($dokumen->file);

        return response($content)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($dokumen->file) . '"');
    }

   public function verify($id, $kode)
{
    $dokumen = Dokumen::with(['admin', 'kuwu'])
        ->where('kode_pengesahan', $kode)
        ->first();

    if (!$dokumen) {
        return view('verify.document', [
            'verified' => false,
            'message' => 'Dokumen tidak valid atau kode salah.'
        ]);
    }

    if ($dokumen->is_signed && $dokumen->qr_code_path) {
        return view('verify.document', [
            'verified' => true,
            'dokumen' => $dokumen,
            'message' => 'Dokumen berhasil diverifikasi.',
            'timestamp' => now()->format('d M Y H:i:s'),
        ]);
    }

    return view('verify.document', [
        'verified' => false,
        'message' => 'Dokumen belum disahkan atau QR code tidak tersedia.'
    ]);
}


}
