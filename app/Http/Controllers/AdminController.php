<?php

namespace App\Http\Controllers;

use App\Models\Kuwu;
use App\Models\Dokumen;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Dokumen::where('id_admin', auth()->guard('admin')->id());

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'like', "%$search%")
                  ->orWhere('nama_pemohon', 'like', "%$search%");
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status_dokumen', $request->status);
        }

        $dokumens = $query->latest()->get();

        $countDiajukan = Dokumen::where('id_admin', auth()->guard('admin')->id())
                                ->where('status_dokumen', 'diajukan')->count();
        $countDisahkan = Dokumen::where('id_admin', auth()->guard('admin')->id())
                                ->where('status_dokumen', 'disahkan')->count();
        $countDisetujui = Dokumen::where('id_admin', auth()->guard('admin')->id())
                                ->where('status_dokumen', 'disetujui')->count();

        return view('user.admin.admin_dashboard', compact(
            'dokumens',
            'countDiajukan',
            'countDisahkan',
            'countDisetujui'
        ));
    }

    public function pengajuan()
    {
        $admin = Auth::guard('admin')->user();
        $kuwuList = Kuwu::all();
        return view('user.admin.pengajuan_admin', compact('admin', 'kuwuList'));
    }

    public function storePengajuan(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'nomor_surat' => 'required|string|max:255',
                'jenis_surat' => 'required|string|max:255',
                'nama_pengaju' => 'required|string|max:255',
                'tujuan_pengajuan' => 'required|in:kuwu,kemahasiswaan',
                'nama_pemohon' => 'required|string|max:255',
                'unggah_dokumen' => 'required|file|mimes:pdf|max:2048',
                'catatan' => 'nullable|string',
            ]);

            // Handle file upload
            if ($request->hasFile('unggah_dokumen')) {
                $file = $request->file('unggah_dokumen');
                $fileName = time() . '_' . $request->nomor_surat . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('dokumen', $fileName, 'public');
            } else {
                throw new \Exception('File tidak ditemukan');
            }

            // Create new document
            $dokumen = new Dokumen();
            $dokumen->nomor_surat = $request->nomor_surat;
            $dokumen->jenis_surat = $request->jenis_surat;
            $dokumen->nama_pemohon = $request->nama_pemohon;
            $dokumen->file = $filePath;
            $dokumen->keterangan = $request->catatan;
            $dokumen->tanggal_pengajuan = now();
            $dokumen->status_dokumen = 'diajukan';
            $dokumen->id_admin = Auth::guard('admin')->id();

            // Set id_kuwu atau id_kemahasiswaan berdasarkan tujuan
            if ($request->tujuan_pengajuan === 'kuwu') {
                $dokumen->id_kuwu = $request->kepada_tujuan;
            } else {
                $dokumen->id_kuwu = null;
            }

            // Save document
            if (!$dokumen->save()) {
                // Hapus file jika gagal menyimpan ke database
                Storage::disk('public')->delete($filePath);
                throw new \Exception('Gagal menyimpan dokumen ke database');
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Dokumen berhasil diajukan!');

        } catch (\Exception $e) {
            Log::error('Error in storePengajuan:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function riwayat(Request $request)
    {
        $query = Dokumen::where('id_admin', auth()->guard('admin')->id());

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'like', "%$search%")
                  ->orWhere('jenis_surat', 'like', "%$search%")
                  ->orWhere('nama_pemohon', 'like', "%$search%")
                  ->orWhere('status_dokumen', 'like', "%$search%");
            });
        }
        // Apply status filter if exists
        if ($request->has('status') && $request->status != '') {
            $query->where('status_dokumen', $request->status);
        }

        $dokumens = $query->latest()->get();

        return view('user.admin.riwayat_admin', compact('dokumens'));
    }

    public function getDokumenContent($id)
    {
        $dokumen = Dokumen::find($id);
        if ($dokumen) {
            $filePath = storage_path('app/public/' . $dokumen->file);
            if (file_exists($filePath)) {
                return response()->download($filePath, $dokumen->nomor_surat . '.pdf');
            }
            return response()->json(['error' => 'File not found'], 404);
        }
        return response()->json(['error' => 'Document not found'], 404);
    }

    public function profile()
    {
        $admin = Auth::guard('admin')->user();
        return view('user.admin.profile', compact('admin'));
    }

    public function editProfile()
    {
        $admin = Auth::guard('admin')->user();
        return view('user.admin.profile', compact('admin'));
    }
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'namaAdmin' => 'required|string|max:255',
            'email' => 'required|email',
            'noHp' => 'required|string|max:15',
            'currentPassword' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'passwordConfirmation' => 'nullable|same:password',
        ]);

        // Update basic info
        $data = [
            'namaAdmin' => $request->namaAdmin,
            'noHp' => $request->noHp,
        ];

        // If email is changed, set it as unverified and store new email in verification_email
        if ($emailChanged) {
            $data['verification_email'] = $request->email;
            // Keep the old email until verified
        }

        // Update password if provided
        if ($request->filled('currentPassword') && $request->filled('password')) {
            // Verify current password
            if (!Hash::check($request->currentPassword, $admin->password)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['currentPassword' => 'Current password is incorrect']);
            }

            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        // If email changed, redirect to verification page
        if ($emailChanged) {
            return redirect()->route('admin.profile')
                ->with('verify_email', true)
                ->with('new_email', $request->email);
        }

        return redirect()->route('admin.profile')
            ->with('success', 'Profile updated successfully');
    }


    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'max:2048'] // 2MB Max
        ]);

        $admin = Auth::guard('admin')->user();

        if ($admin->profile) {
            Storage::disk('public')->delete($admin->profile);
        }

        $path = $request->file('profile_photo')->store('profile-photos', 'public');

        $admin->update([
            'profile' => $path
        ]);

        return back()->with('success', 'Profile photo updated successfully');
    }

    public function destroyPhoto()
    {
        $admin = Auth::guard('admin')->user();

        if ($admin->profile) {
            Storage::disk('public')->delete($admin->profile);

            $admin->update([
                'profile' => null
            ]);
        }

        return back()->with('success', 'Profile photo removed successfully');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('login');
    }

    public function showDokumen($id)
    {
        try {
            // Log request for debugging
            Log::info('Show dokumen request', [
                'id' => $id,
                'user_id' => auth()->guard('admin')->id()
            ]);

            $dokumen = Dokumen::with(['kuwu', 'admin'])
                ->where('id', $id)
                ->where('id_admin', auth()->guard('admin')->id())
                ->firstOrFail();

            // Periksa apakah file ada
            if (!$dokumen->file) {
                Log::warning('Document file path is missing', ['dokumen_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'File dokumen tidak ditemukan'
                ], 404);
            }

            $filePath = 'dokumen/' . basename($dokumen->file);

            // Check if file exists in storage
            if (!Storage::disk('public')->exists($filePath)) {
                Log::warning('Document file not found in storage', [
                    'dokumen_id' => $id,
                    'file_path' => $filePath
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'File dokumen tidak ditemukan di server'
                ], 404);
            }

            // Generate URL yang valid untuk file
            $fileUrl = asset('storage/' . $filePath);

            // Format tanggal ke format yang lebih readable
            $tanggalPengajuan = \Carbon\Carbon::parse($dokumen->tanggal_pengajuan)->format('d F Y');

            $response = [
                'success' => true,
                'data' => [
                    'id' => $dokumen->id,
                    'nomor_surat' => $dokumen->nomor_surat,
                    'jenis_surat' => $dokumen->jenis_surat,
                    'tanggal_pengajuan' => $tanggalPengajuan,
                    'nama_pemohon' => $dokumen->nama_pemohon,
                    'status_dokumen' => ucfirst($dokumen->status_dokumen),
                    'keterangan_revisi' => $dokumen->keterangan_revisi,
                    'keterangan_pengirim' => $dokumen->keterangan_pengirim,
                    'file_url' => $fileUrl,
                    'tujuan' => $dokumen->kuwu ? [
                        'nama' => $dokumen->kuwu->nama_kuwu,
                        'jenis' => 'Kuwu'
                    ] : null
                ],
            ];

            // Log success
            Log::info('Document successfully retrieved', [
                'dokumen_id' => $id,
                'status' => $dokumen->status_dokumen
            ]);

            return response()->json($response, 200, ['Content-Type' => 'application/json']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Document not found', [
                'id' => $id,
                'user_id' => auth()->guard('admin')->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in showDokumen: ' . $e->getMessage(), [
                'id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDokumen(Request $request, $id)
    {
        try {
            // Log the request for debugging
            Log::info('Update dokumen request received', [
                'id' => $id,
                'user_id' => auth()->guard('admin')->id(),
                'has_file' => $request->hasFile('dokumen')
            ]);

            $dokumen = Dokumen::findOrFail($id);

            // Validate request
            $request->validate([
                'dokumen' => 'required|file|mimes:pdf|max:2048'
            ]);

            // Make sure the document belongs to the current user and needs revision
            if ($dokumen->id_admin != auth()->guard('admin')->id()) {
                Log::warning('Unauthorized access attempt', [
                    'dokumen_id' => $id,
                    'requesting_user' => auth()->guard('admin')->id(),
                    'document_owner' => $dokumen->id_admin
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk merevisi dokumen ini'
                ], 403);
            }

            // Check if the document needs revision
            if ($dokumen->status_dokumen != 'butuh revisi') {
                Log::warning('Attempted revision of document not needing revision', [
                    'dokumen_id' => $id,
                    'current_status' => $dokumen->status_dokumen
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen ini tidak memerlukan revisi'
                ], 400);
            }

            // Use a transaction to ensure database consistency
            DB::beginTransaction();

            try {
                // Delete old file if exists
                if ($dokumen->file && Storage::disk('public')->exists($dokumen->file)) {
                    Storage::disk('public')->delete($dokumen->file);
                }

                // Store new file dengan nama yang lebih terstruktur
                $file = $request->file('dokumen');
                $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('dokumen', $fileName, 'public');

                // Update dokumen
                $dokumen->update([
                    'file' => $filePath,
                    'status_dokumen' => 'sudah direvisi',
                    'tanggal_revisi' => now(),
                    'keterangan_pengirim' => $request->input('keterangan') // Optional revision notes from the sender
                ]);

                DB::commit();

                // Log success
                Log::info('Dokumen berhasil direvisi', [
                    'dokumen_id' => $dokumen->id,
                    'new_file' => $filePath
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil direvisi'
                ]);

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Dokumen tidak ditemukan', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak ditemukan'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validasi gagal', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'File yang diunggah tidak valid. Pastikan file dalam format PDF dan ukuran maksimal 2MB'
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error saat revisi dokumen', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat merevisi dokumen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detailDokumen($id)
    {
        $dokumen = Dokumen::with(['kuwu', 'admin'])
            ->where('id', $id)
            ->where('id_admin', auth()->guard('admin')->user()->id)
            ->firstOrFail();

        return view('user.admin.detail_dokumen', compact('dokumen'));
    }

    public function showEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        // Simpan email baru di session untuk ditampilkan di modal
        session(['verify_email' => true, 'new_email' => $email]);

        return response()->json([
            'success' => true,
            'message' => 'Verification modal is ready'
        ]);
    }

    public function downloadDokumen($id)
    {
        try {
            $dokumen = Dokumen::where('id', $id)
                ->where('id_admin', auth()->guard('admin')->id())
                ->firstOrFail();

            $filePath = storage_path('app/public/' . $dokumen->file);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            // Set headers untuk memaksa download
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . basename($dokumen->file) . '"',
            ];

            return response()->download($filePath, basename($dokumen->file), $headers);

        } catch (\Exception $e) {
            Log::error('Error in downloadDokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunduh dokumen'
            ], 500);
        }
    }

    public function viewDokumen($id)
    {
        try {
            $dokumen = Dokumen::where('id', $id)
                ->where('id_admin', auth()->guard('admin')->id())
                ->firstOrFail();

            $filePath = storage_path('app/public/' . $dokumen->file);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            // Set headers untuk menampilkan PDF di browser
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($dokumen->file) . '"',
            ];

            return response()->file($filePath, $headers);

        } catch (\Exception $e) {
            Log::error('Error in viewDokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menampilkan dokumen'
            ], 500);
        }
    }

    public function generateQrCode($id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            if ($dokumen->status_dokumen !== 'disetujui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen harus berstatus disetujui untuk membubuhkan QR Code'
                ], 400);
            }

            // Generate kode pengesahan jika belum ada
            if (!$dokumen->kode_pengesahan) {
                $dokumen->kode_pengesahan = Str::random(10);
                $dokumen->save();
            }

            // Buat URL verifikasi
            $verificationUrl = route('verify.document', ['id' => $id, 'kode' => $dokumen->kode_pengesahan]);

            // Generate QR Code dengan path yang benar
            $qrCodePath = 'qrcodes/qr_' . $id . '_' . time() . '.png';
            $fullPath = storage_path('app/public/' . $qrCodePath);

            // Pastikan direktori exists
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            // Generate QR code menggunakan SimpleSoftwareIO
            QrCode::format('png')
                  ->size(400)
                  ->margin(1)
                  ->generate($verificationUrl, $fullPath);

            // Update dokumen dengan path QR code
            $dokumen->update([
                'qr_code_path' => $qrCodePath
            ]);

            return response()->json([
                'success' => true,
                'qrCodeUrl' => Storage::url($qrCodePath),
                'message' => 'QR Code berhasil dibuat'
            ]);

        } catch (\Exception $e) {
            Log::error('QR Code Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editQrCode($id)
{
    try {
        $dokumen = Dokumen::findOrFail($id);

        // Check authorization
        if ($dokumen->id_admin != auth()->guard('admin')->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Check document status
        if ($dokumen->status_dokumen !== 'disetujui') {
            return back()->with('error', 'Dokumen harus berstatus disetujui untuk mengedit QR Code');
        }

        // Generate QR code if it doesn't exist
        if (!$dokumen->qr_code_path || !Storage::disk('public')->exists($dokumen->qr_code_path)) {
            // Generate kode pengesahan if not exists
            if (!$dokumen->kode_pengesahan) {
                $dokumen->kode_pengesahan = Str::random(10);
            }

            // Set QR code path
            $qrCodePath = 'qrcodes/qr_' . $dokumen->id . '_' . time() . '.png';
            $fullPath = storage_path('app/public/' . $qrCodePath);

            // Create directory if not exists
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            // Generate verification URL
            $verificationUrl = route('verify.document', [
                'id' => $dokumen->id,
                'kode' => $dokumen->kode_pengesahan
            ]);

            // Generate QR code
            QrCode::format('png')
                  ->size(200)
                  ->margin(1)
                  ->generate($verificationUrl, $fullPath);

            // Update document
            $dokumen->update([
                'qr_code_path' => $qrCodePath,
                'kode_pengesahan' => $dokumen->kode_pengesahan
            ]);

            Log::info('QR Code generated for document', [
                'dokumen_id' => $dokumen->id,
                'qr_path' => $qrCodePath
            ]);
        }

        // Verify QR code file exists and is accessible
        $qrFullPath = storage_path('app/public/' . $dokumen->qr_code_path);
        if (!file_exists($qrFullPath)) {
            Log::error('QR Code file not found', [
                'dokumen_id' => $dokumen->id,
                'expected_path' => $qrFullPath
            ]);
            return back()->with('error', 'File QR Code tidak ditemukan');
        }

        return view('user.admin.edit_qr', compact('dokumen'));

    } catch (\Exception $e) {
        Log::error('Error in editQrCode: ' . $e->getMessage(), [
            'dokumen_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Gagal memuat QR Code: ' . $e->getMessage());
    }
}


    public function saveQrPosition(Request $request, $id)
{
    try {
        // Validate request
        $validated = $request->validate([
            'x' => 'required|numeric|min:0|max:100',
            'y' => 'required|numeric|min:0|max:100',
            'width' => 'required|numeric|min:1|max:50',
            'height' => 'required|numeric|min:1|max:50',
            'page' => 'required|numeric|min:1'
        ]);

        $dokumen = Dokumen::findOrFail($id);

        // Check authorization
        if ($dokumen->id_admin != auth()->guard('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Check document status
        if ($dokumen->status_dokumen !== 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen harus berstatus disetujui untuk menyimpan posisi QR Code'
            ], 400);
        }

        // Check if QR code exists
        if (!$dokumen->qr_code_path || !Storage::disk('public')->exists($dokumen->qr_code_path)) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code belum di-generate'
            ], 400);
        }

        $sourcePdfPath = storage_path('app/public/' . $dokumen->file);
        if (!file_exists($sourcePdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File PDF sumber tidak ditemukan'
            ], 404);
        }

        // Check if FPDI is available
        if (!class_exists('\setasign\Fpdi\Fpdi')) {
            // Alternative: Just save position data without embedding QR into PDF
            $dokumen->update([
                'qr_position_x' => $validated['x'],
                'qr_position_y' => $validated['y'],
                'qr_width' => $validated['width'],
                'qr_height' => $validated['height'],
                'qr_page' => $validated['page'],
                'is_signed' => true,
                'tanggal_verifikasi' => now(),
                'status_dokumen' => 'disahkan'
            ]);

            Log::info('QR position saved without PDF embedding', [
                'dokumen_id' => $dokumen->id,
                'position' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Posisi QR Code berhasil disimpan'
            ]);
        }

        // Create new PDF with embedded QR code
        $pdf = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($sourcePdfPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $tplIdx = $pdf->importPage($pageNo);
            $pdf->useTemplate($tplIdx);

            // Add QR code only to specified page
            if ($pageNo === (int)$validated['page']) {
                $qrCodePath = storage_path('app/public/' . $dokumen->qr_code_path);

                if (file_exists($qrCodePath)) {
                    $pageWidth = $pdf->GetPageWidth();
                    $pageHeight = $pdf->GetPageHeight();

                    // Convert percentage to actual coordinates
                    $x = ($validated['x'] * $pageWidth) / 100;
                    $y = ($validated['y'] * $pageHeight) / 100;
                    $width = ($validated['width'] * $pageWidth) / 100;
                    $height = ($validated['height'] * $pageHeight) / 100;

                    $pdf->Image($qrCodePath, $x, $y, $width, $height);
                }
            }
        }

        // Save new PDF with embedded QR code
        $newFileName = 'signed_' . time() . '_' . basename($dokumen->file);
        $newFilePath = 'dokumen/' . $newFileName;
        $fullPath = storage_path('app/public/' . $newFilePath);

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf->Output($fullPath, 'F');

        // Update document record
        $dokumen->update([
            'file' => $newFilePath,
            'qr_position_x' => $validated['x'],
            'qr_position_y' => $validated['y'],
            'qr_width' => $validated['width'],
            'qr_height' => $validated['height'],
            'qr_page' => $validated['page'],
            'is_signed' => true,
            'tanggal_verifikasi' => now(),
            'status_dokumen' => 'disahkan'
        ]);

        Log::info('QR Code successfully embedded in PDF', [
            'dokumen_id' => $dokumen->id,
            'new_file' => $newFilePath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR Code berhasil ditambahkan ke dokumen',
            'redirect' => route('admin.dashboard')
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Data posisi tidak valid',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error in saveQrPosition: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan posisi QR code: ' . $e->getMessage()
        ], 500);
    }
}
}
