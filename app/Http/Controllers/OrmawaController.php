<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Dokumen;
use App\Models\Ormawas;
use Illuminate\Http\Request;
use App\Models\Kemahasiswaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class OrmawaController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Dokumen::where('id_ormawa', auth()->guard('ormawa')->id());

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

        $countDiajukan = Dokumen::where('id_ormawa', auth()->guard('ormawa')->id())
                                ->where('status_dokumen', 'diajukan')->count();
        $countDisahkan = Dokumen::where('id_ormawa', auth()->guard('ormawa')->id())
                                ->where('status_dokumen', 'disahkan')->count();
        $countDisetujui = Dokumen::where('id_ormawa', auth()->guard('ormawa')->id())
                                ->where('status_dokumen', 'disetujui')->count();

        return view('user.ormawa.ormawa_dashboard', compact(
            'dokumens',
            'countDiajukan',
            'countDisahkan',
            'countDisetujui'
        ));
    }

    public function pengajuan()
    {
        $ormawa = Auth::guard('ormawa')->user();
        $dosenList = Dosen::all();
        $kemahasiswaanList = Kemahasiswaan::all();
        return view('user.ormawa.pengajuan_ormawa', compact('ormawa', 'dosenList', 'kemahasiswaanList'));
    }

    public function storePengajuan(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'nomor_surat' => 'required|string|max:255',
                'jenis_surat' => 'required|string|max:255',
                'nama_pengaju' => 'required|string|max:255',
                'tujuan_pengajuan' => 'required|in:dosen,kemahasiswaan',
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
            $dokumen->id_ormawa = Auth::guard('ormawa')->id();

            // Set id_dosen atau id_kemahasiswaan berdasarkan tujuan
            if ($request->tujuan_pengajuan === 'dosen') {
                $dokumen->id_dosen = $request->kepada_tujuan;
                $dokumen->id_kemahasiswaan = null;
            } else {
                $dokumen->id_kemahasiswaan = $request->kepada_kemahasiswaan;
                $dokumen->id_dosen = null;
            }

            // Save document
            if (!$dokumen->save()) {
                // Hapus file jika gagal menyimpan ke database
                Storage::disk('public')->delete($filePath);
                throw new \Exception('Gagal menyimpan dokumen ke database');
            }

            return redirect()
                ->route('ormawa.dashboard')
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
        $query = Dokumen::where('id_ormawa', auth()->guard('ormawa')->id());

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

        return view('user.ormawa.riwayat_ormawa', compact('dokumens'));
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
        $ormawa = Auth::guard('ormawa')->user();
        return view('user.ormawa.profile', compact('ormawa'));
    }

    public function editProfile()
    {
        $ormawa = Auth::guard('ormawa')->user();
        return view('user.ormawa.profile', compact('ormawa'));
    }
    public function updateProfile(Request $request)
    {
        $ormawa = Auth::guard('ormawa')->user();

        $request->validate([
            'namaMahasiswa' => 'required|string|max:255',
            'email' => 'required|email',
            'noHp' => 'required|string|max:15',
            'currentPassword' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'passwordConfirmation' => 'nullable|same:password',
        ]);

        // Check if email is being changed
        $emailChanged = ($request->email !== $ormawa->email);

        // Update basic info
        $data = [
            'namaMahasiswa' => $request->namaMahasiswa,
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
            if (!Hash::check($request->currentPassword, $ormawa->password)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['currentPassword' => 'Current password is incorrect']);
            }

            $data['password'] = Hash::make($request->password);
        }

        $ormawa->update($data);

        // If email changed, redirect to verification page
        if ($emailChanged) {
            return redirect()->route('ormawa.profile')
                ->with('verify_email', true)
                ->with('new_email', $request->email);
        }

        return redirect()->route('ormawa.profile')
            ->with('success', 'Profile updated successfully');
    }

    // Add method to get verification status for AJAX calls
    public function getVerificationStatus()
    {
        $ormawa = Auth::guard('ormawa')->user();

        return response()->json([
            'is_verified' => $ormawa->is_email_verified,
            'email' => $ormawa->email,
            'verification_in_progress' => !empty($ormawa->verification_email),
            'verification_email' => $ormawa->verification_email,
        ]);
    }

    // Generate OTP and send to email
    public function sendEmailOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $ormawa = Auth::guard('ormawa')->user();

            // Check if the email is already verified
            if ($ormawa->is_email_verified && $ormawa->email === $request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already verified.'
                ]);
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP and set expiration time (15 minutes)
            $ormawa->verification_email = $request->email;
            $ormawa->email_verification_code = $otp;
            $ormawa->email_verification_expires_at = Carbon::now()->addMinutes(15);
            $ormawa->save();

            // Send email with OTP
            $this->sendOTPEmail($ormawa->verification_email, $otp, $ormawa->namaMahasiswa);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email. Please check your inbox.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }
    }

    // Verify the OTP
    public function verifyEmailOTP(Request $request)
    {
        try {
            $request->validate([
                'otp' => 'required|numeric|digits:6'
            ]);

            $ormawa = Auth::guard('ormawa')->user();

            // Check if OTP is expired
            if (Carbon::now()->isAfter($ormawa->email_verification_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.'
                ]);
            }

            // Verify OTP
            if ($request->otp != $ormawa->email_verification_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP. Please try again.'
                ]);
            }

            // Update email and verification status
            $ormawa->email = $ormawa->verification_email;
            $ormawa->is_email_verified = true;
            $ormawa->email_verified_at = Carbon::now();
            $ormawa->email_verification_code = null;
            $ormawa->verification_email = null;
            $ormawa->save();

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to verify OTP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify OTP. Please try again.'
            ], 500);
        }
    }

    // Resend OTP
    public function resendOTP()
    {
        try {
            $ormawa = Auth::guard('ormawa')->user();

            if (!$ormawa->verification_email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email verification in progress.'
                ]);
            }

            // Generate new OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Update OTP and expiration time
            $ormawa->email_verification_code = $otp;
            $ormawa->email_verification_expires_at = Carbon::now()->addMinutes(15);
            $ormawa->save();

            // Send email with new OTP
            $this->sendOTPEmail($ormawa->verification_email, $otp, $ormawa->namaMahasiswa);

            return response()->json([
                'success' => true,
                'message' => 'New OTP sent to your email. Please check your inbox.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again.'
            ], 500);
        }
    }

    // Helper function to send OTP email
    private function sendOTPEmail($email, $otp, $name)
    {
        $data = [
            'otp' => $otp,
            'name' => $name
        ];

        Mail::send('emails.otp-verification', $data, function($message) use($email) {
            $message->to($email)
                    ->subject('Email Verification Code - Sistem Dokumen Digital');
        });
    }

    // public function updateProfile(Request $request)
    // {
    //     $request->validate([
    //         'namaMahasiswa' => 'required|string|max:255',
    //         'email' => 'required|email',
    //         'noHp' => 'required|string|max:15',
    //     ]);

    //     $ormawa = Auth::guard('ormawa')->user();
    //     $ormawa->update([
    //         'namaMahasiswa' => $request->namaMahasiswa,
    //         'email' => $request->email,
    //         'noHp' => $request->noHp,
    //     ]);
    //     $ormawa->save();

    //     return redirect()->route('ormawa.profile')->with('success', 'Profile updated successfully');
    // }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'max:1024'] // 1MB Max
        ]);

        $ormawa = Auth::guard('ormawa')->user();

        if ($ormawa->profile) {
            Storage::disk('public')->delete($ormawa->profile);
        }

        $path = $request->file('profile_photo')->store('profile-photos', 'public');

        $ormawa->update([
            'profile' => $path
        ]);

        return back()->with('success', 'Profile photo updated successfully');
    }

    public function destroyPhoto()
    {
        $ormawa = Auth::guard('ormawa')->user();

        if ($ormawa->profile) {
            Storage::disk('public')->delete($ormawa->profile);

            $ormawa->update([
                'profile' => null
            ]);
        }

        return back()->with('success', 'Profile photo removed successfully');
    }

    public function logout()
    {
        Auth::guard('ormawa')->logout();
        return redirect()->route('login');
    }

    public function showDokumen($id)
    {
        try {
            // Log request for debugging
            Log::info('Show dokumen request', [
                'id' => $id,
                'user_id' => auth()->guard('ormawa')->id()
            ]);

            $dokumen = Dokumen::with(['dosen', 'ormawa', 'kemahasiswaan'])
                ->where('id', $id)
                ->where('id_ormawa', auth()->guard('ormawa')->id())
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
                    'tujuan' => $dokumen->dosen ? [
                        'nama' => $dokumen->dosen->nama_dosen,
                        'jenis' => 'Dosen'
                    ] : ($dokumen->kemahasiswaan ? [
                        'nama' => $dokumen->kemahasiswaan->nama_kemahasiswaan,
                        'jenis' => 'Kemahasiswaan'
                    ] : null)
                ]
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
                'user_id' => auth()->guard('ormawa')->id()
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
                'user_id' => auth()->guard('ormawa')->id(),
                'has_file' => $request->hasFile('dokumen')
            ]);

            $dokumen = Dokumen::findOrFail($id);

            // Validate request
            $request->validate([
                'dokumen' => 'required|file|mimes:pdf|max:2048'
            ]);

            // Make sure the document belongs to the current user and needs revision
            if ($dokumen->id_ormawa != auth()->guard('ormawa')->id()) {
                Log::warning('Unauthorized access attempt', [
                    'dokumen_id' => $id,
                    'requesting_user' => auth()->guard('ormawa')->id(),
                    'document_owner' => $dokumen->id_ormawa
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
        $dokumen = Dokumen::with(['dosen', 'ormawa'])
            ->where('id', $id)
            ->where('id_ormawa', auth()->guard('ormawa')->user()->id)
            ->firstOrFail();

        return view('user.ormawa.detail_dokumen', compact('dokumen'));
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
                ->where('id_ormawa', auth()->guard('ormawa')->id())
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
                ->where('id_ormawa', auth()->guard('ormawa')->id())
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

            if ($dokumen->id_ormawa != auth()->guard('ormawa')->id()) {
                abort(403, 'Unauthorized action.');
            }

            if ($dokumen->status_dokumen !== 'disetujui') {
                return back()->with('error', 'Dokumen harus berstatus disetujui untuk mengedit QR Code');
            }

            // Generate QR code jika belum ada
            if (!$dokumen->qr_code_path || !Storage::disk('public')->exists($dokumen->qr_code_path)) {
                // Generate kode pengesahan baru
                $dokumen->kode_pengesahan = Str::random(10);

                // Set path QR code
                $qrCodePath = 'qrcodes/qr_' . $dokumen->id . '_' . time() . '.png';
                $fullPath = storage_path('app/public/' . $qrCodePath);

                // Buat direktori jika belum ada
                if (!file_exists(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0755, true);
                }

                // Generate QR code
                QrCode::format('png')
                      ->size(400)
                      ->margin(1)
                      ->generate(
                          route('verify.document', ['id' => $dokumen->id, 'kode' => $dokumen->kode_pengesahan]),
                          $fullPath
                      );

                // Update dokumen
                $dokumen->update([
                    'qr_code_path' => $qrCodePath,
                    'kode_pengesahan' => $dokumen->kode_pengesahan
                ]);
            }

            return view('user.ormawa.edit_qr', compact('dokumen'));

        } catch (\Exception $e) {
            Log::error('Error in editQrCode: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat QR Code: ' . $e->getMessage());
        }
    }

    public function saveQrPosition(Request $request, Dokumen $dokumen)
    {
        try {
            $validated = $request->validate([
                'x' => 'required|numeric',
                'y' => 'required|numeric',
                'width' => 'required|numeric',
                'height' => 'required|numeric',
                'page' => 'required|numeric'
            ]);

            if ($dokumen->status_dokumen !== 'disetujui') {
                throw new \Exception('Dokumen harus berstatus disetujui untuk menyimpan posisi QR Code');
            }

            if (!$dokumen->qr_code_path || !Storage::disk('public')->exists($dokumen->qr_code_path)) {
                throw new \Exception('QR Code belum di-generate');
            }

            $sourcePdfPath = storage_path('app/public/' . $dokumen->file);
            if (!file_exists($sourcePdfPath)) {
                throw new \Exception('File PDF sumber tidak ditemukan');
            }

            // Proses penambahan QR Code ke PDF
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($sourcePdfPath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplIdx = $pdf->importPage($pageNo);
                $pdf->useTemplate($tplIdx);

                if ($pageNo === (int)$validated['page']) {
                    $qrCodePath = storage_path('app/public/' . $dokumen->qr_code_path);

                    $pageWidth = $pdf->GetPageWidth();
                    $pageHeight = $pdf->GetPageHeight();

                    $x = ($validated['x'] * $pageWidth) / 100;
                    $y = ($validated['y'] * $pageHeight) / 100;
                    $width = ($validated['width'] * $pageWidth) / 100;
                    $height = ($validated['height'] * $pageHeight) / 100;

                    $pdf->Image($qrCodePath, $x, $y, $width, $height);
                }
            }

            $newFileName = 'signed_' . time() . '_' . basename($dokumen->file);
            $newFilePath = 'dokumen/' . $newFileName;
            $fullPath = storage_path('app/public/' . $newFilePath);

            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $pdf->Output($fullPath, 'F');

            $dokumen->update([
                'file' => $newFilePath,
                'qr_position_x' => $validated['x'],
                'qr_position_y' => $validated['y'],
                'qr_width' => $validated['width'],
                'qr_height' => $validated['height'],
                'qr_page' => $validated['page'],
                'is_signed' => true,
                'tanggal_verifikasi' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil ditambahkan'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in saveQrPosition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
