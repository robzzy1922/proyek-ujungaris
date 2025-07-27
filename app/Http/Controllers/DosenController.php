<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Dokumen;
use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\TandaQr;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class DosenController extends Controller
{
    public function dashboardDosen(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $dosen_id = auth()->guard('dosen')->user()->id;

        $query = Dokumen::with('dosen')
            ->where('id_dosen', $dosen_id);

        if ($status) {
            $query->where('status_dokumen', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('tanggal_pengajuan', 'like', "%{$search}%")
                  ->orWhere('nama_pemohon', 'like', "%{$search}%")
                  ->orWhereHas('dosen', function ($q) use ($search) {
                      $q->where('nama_dosen', 'like', "%{$search}%");
                  })
                  ->orWhere('status_dokumen', 'like', "%{$search}%");
            });
        }

        $dokumens = $query->latest()->get();

        $countDiajukan = Dokumen::where('id_dosen', $dosen_id)
            ->where('status_dokumen', 'diajukan')->count();
        $countDisahkan = Dokumen::where('id_dosen', $dosen_id)
            ->where('status_dokumen', 'disahkan')->count();
        $countDisetujui = Dokumen::where('id_dosen', $dosen_id)
            ->where('status_dokumen', 'disetujui')->count();

        return view('user.dosen.dashboard_dosen', compact('dokumens', 'status', 'countDiajukan', 'countDisahkan', 'countDisetujui'));
    }

    public function create()
    {
        return view('user.dosen.create_tandatangan');
    }

    public function riwayat(Request $request)
    {
        $query = Dokumen::query()->where('id_dosen', Auth::guard('dosen')->id());

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status_dokumen', $request->status);
        }

        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_surat', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pemohon', 'LIKE', "%{$search}%");
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('user.dosen.riwayat_dosen', compact('documents'));
    }

    public function showDokumen($id)
    {
        $dosen_id = auth()->guard('dosen')->user()->id;

        $dokumen = Dokumen::with(['ormawa', 'dosen'])
            ->where('id_dosen', $dosen_id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'id' => $dokumen->id,
            'nomor_surat' => $dokumen->nomor_surat,
            'tanggal_pengajuan' => $dokumen->tanggal_pengajuan,
            'nama_pemohon' => $dokumen->nama_pemohon,
            'status_dokumen' => ucfirst($dokumen->status_dokumen),
            'keterangan' => $dokumen->keterangan,
            'keterangan_revisi' => $dokumen->keterangan_revisi,
            'keterangan_pengirim' => $dokumen->keterangan_pengirim,
            'file' => $dokumen->file,
            'pengaju' => $dokumen->ormawa ? [
                'nama' => $dokumen->ormawa->namaMahasiswa,
                'ormawa' => $dokumen->ormawa->namaOrmawa,
            ] : null,
            'dosen' => $dokumen->dosen ? [
                'nama' => $dokumen->dosen->nama_dosen,
            ] : null,
        ]);
    }

    public function getDokumenDetail($id)
    {
        $dokumen = Dokumen::with(['ormawa', 'dosen'])->findOrFail($id);
        return response()->json($dokumen);
    }

    public function profile()
    {
        $dosen = Auth::guard('dosen')->user();
        return view('user.dosen.profile', compact('dosen'));
    }

    public function editProfile()
    {
        $dosen = Auth::guard('dosen')->user();
        return view('user.dosen.profile', compact('dosen'));
    }

    public function updateProfile(Request $request)
    {
        $dosen = Auth::guard('dosen')->user();

        $request->validate([
            'namaDosen' => 'required|string|max:255',
            'email' => 'required|email',
            'noHp' => 'required|string|max:15',
            'currentPassword' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'passwordConfirmation' => 'nullable|same:password',
        ]);

        // Check if email is being changed
        $emailChanged = ($request->email !== $dosen->email);

        // Update basic info
        $data = [
            'nama_dosen' => $request->namaDosen,
            'no_hp' => $request->noHp,
        ];

        // If email is changed, set it as unverified and store new email in verification_email
        if ($emailChanged) {
            $data['verification_email'] = $request->email;
            // Keep the old email until verified
        }

        // Update password if provided
        if ($request->filled('currentPassword') && $request->filled('password')) {
            // Verify current password
            if (!\Illuminate\Support\Facades\Hash::check($request->currentPassword, $dosen->password)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['currentPassword' => 'Current password is incorrect']);
            }

            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $dosen->update($data);

        // If email changed, redirect to verification page
        if ($emailChanged) {
            return redirect()->route('dosen.profile')
                ->with('verify_email', true)
                ->with('new_email', $request->email);
        }

        return redirect()->route('dosen.profile')
            ->with('success', 'Profile updated successfully');
    }

    public function updatePhoto(Request $request)
    {
        // Validasi file
        $request->validate([
            'profile_photo' => ['required', 'image', 'max:1024'], // Maksimal 1MB
        ]);

        // Ambil pengguna yang sedang login
        $dosen = Auth::guard('dosen')->user();

        // Periksa apakah pengguna ditemukan
        if (!$dosen) {
            return back()->with('error', 'Failed to update profile photo. User not found.');
        }

        // Hapus foto profil lama jika ada
        if ($dosen->profile && file_exists(public_path('profiles/' . $dosen->profile))) {
            unlink(public_path('profiles/' . $dosen->profile));
        }

        // Simpan file baru ke folder public/profiles
        $file = $request->file('profile_photo');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('profiles'), $filename);

        // Update database
        $dosen->update([
            'profile' => $filename,
        ]);

        return back()->with('success', 'Profile photo updated successfully.');
    }


    public function destroyPhoto()
    {
        $dosen = Auth::guard('dosen')->user();

        if ($dosen->profile) {
            Storage::disk('public')->delete($dosen->profile);

            $dosen->update([
                'profile' => null
            ]);
        }

        return back()->with('success', 'Profile photo removed successfully');
    }

    public function logout(Request $request)
    {
        Auth::guard('dosen')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Berhasil logout');
    }

    public function generateQrCode($id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

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

            // Simpan data ke tabel tanda_qrs
            TandaQr::create([
                'data_qr' => $verificationUrl,
                'tanggal_pembuatan' => now(),
                'id_ormawa' => $dokumen->id_ormawa,
                'id_dosen' => auth()->guard('dosen')->id(),
                'id_dokumen' => $dokumen->id
            ]);

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

            if (!$dokumen->qr_code_path || !Storage::disk('public')->exists($dokumen->qr_code_path)) {
                throw new \Exception('QR Code belum di-generate');
            }

            $sourcePdfPath = storage_path('app/public/' . $dokumen->file);
            if (!file_exists($sourcePdfPath)) {
                throw new \Exception('File PDF sumber tidak ditemukan');
            }

            // Inisialisasi FPDI
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($sourcePdfPath);

            // Proses setiap halaman
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $pdf->AddPage();
                $tplIdx = $pdf->importPage($pageNo);
                $pdf->useTemplate($tplIdx);

                // Tambahkan QR code hanya di halaman yang dipilih
                if ($pageNo === (int)$validated['page']) {
                    $qrCodePath = storage_path('app/public/' . $dokumen->qr_code_path);

                    // Dapatkan ukuran halaman
                    $pageWidth = $pdf->GetPageWidth();
                    $pageHeight = $pdf->GetPageHeight();

                    // Konversi persentase ke koordinat absolut
                    $x = ($validated['x'] * $pageWidth) / 100;
                    $y = ($validated['y'] * $pageHeight) / 100;
                    $width = ($validated['width'] * $pageWidth) / 100;
                    $height = ($validated['height'] * $pageHeight) / 100;

                    // Pastikan QR code tidak keluar dari halaman
                    $x = max(0, min($x, $pageWidth - $width));
                    $y = max(0, min($y, $pageHeight - $height));

                    // Tambahkan QR code ke PDF
                    $pdf->Image($qrCodePath, $x, $y, $width, $height);
                }
            }

            // Simpan PDF yang sudah ditandatangani
            $newFileName = 'signed_' . time() . '_' . basename($dokumen->file);
            $newFilePath = 'dokumen/' . $newFileName;

            // Pastikan direktori exists
            $fullPath = storage_path('app/public/' . $newFilePath);
            if (!file_exists(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            // Simpan PDF ke storage
            $pdf->Output($fullPath, 'F');

            // Update database dengan timestamp yang benar
            $dokumen->update([
                'file' => $newFilePath,
                'qr_position_x' => $validated['x'],
                'qr_position_y' => $validated['y'],
                'qr_width' => $validated['width'],
                'qr_height' => $validated['height'],
                'qr_page' => $validated['page'],
                'status_dokumen' => 'disahkan',
                'is_signed' => true,
                'tanggal_verifikasi' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil ditambahkan dan dokumen telah disahkan'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in saveQrPosition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyDocument($id)
    {
        try {
            $dokumen = Dokumen::with(['dosen', 'ormawa', 'kemahasiswaan'])->findOrFail($id);

            if (!$dokumen->is_signed || !$dokumen->kode_pengesahan) {
                return view('verify.document', [
                    'verified' => false,
                    'message' => 'Dokumen belum disahkan'
                ]);
            }

            return view('verify.document', [
                'dokumen' => $dokumen,
                'title' => 'Verifikasi Dokumen',
                'verified' => true,
                'timestamp' => now()->format('d M Y H:i:s')
            ]);
        } catch (\Exception $e) {
            return view('verify.document', [
                'verified' => false,
                'message' => 'Dokumen tidak ditemukan'
            ]);
        }
    }

    public function editQrCode($id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            if ($dokumen->id_dosen != auth()->guard('dosen')->id()) {
                abort(403, 'Unauthorized action.');
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

            return view('user.dosen.edit_qr', compact('dokumen'));

        } catch (\Exception $e) {
            Log::error('Error in editQrCode: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat QR Code: ' . $e->getMessage());
        }
    }


    public function getVerificationStatus()
    {
        $dosen = Auth::guard('dosen')->user();

        return response()->json([
            'is_verified' => $dosen->is_email_verified,
            'email' => $dosen->email,
            'verification_in_progress' => !empty($dosen->verification_email),
            'verification_email' => $dosen->verification_email,
        ]);
    }

    // Generate OTP and send to email
    public function sendEmailOTP(Request $request)
    {
        try {
            Log::info('Received send OTP request', $request->all());

            $request->validate([
                'email' => 'required|email'
            ]);

            $dosen = Auth::guard('dosen')->user();
            Log::info('User authenticated', ['user_id' => $dosen->id]);

            // Check if the email is already verified
            if ($dosen->is_email_verified && $dosen->email === $request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already verified.'
                ]);
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store OTP and set expiration time (15 minutes)
            $dosen->verification_email = $request->email;
            $dosen->email_verification_code = $otp;
            $dosen->email_verification_expires_at = Carbon::now()->addMinutes(15);
            $saved = $dosen->save();

            if (!$saved) {
                Log::error('Failed to save OTP data to database');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save verification data. Please try again.'
                ], 500);
            }

            Log::info('OTP generated and saved', [
                'user_id' => $dosen->id,
                'verification_email' => $dosen->verification_email,
                'otp' => $otp, // Don't log OTP in production!
                'expires_at' => $dosen->email_verification_expires_at
            ]);

            // Send email with OTP
            try {
                $this->sendOTPEmail($dosen->verification_email, $otp, $dosen->nama_dosen);
                Log::info('OTP email sent successfully');

                // For development, include OTP in response
                if (config('app.debug')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'OTP sent to your email. Please check your inbox.',
                        'debug_otp' => $otp
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent to your email. Please check your inbox.'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);

                // For development, return success with OTP even if email fails
                if (config('app.debug')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Email sending failed but OTP generated. For debugging: ' . $otp,
                        'debug_otp' => $otp
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send OTP: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP: ' . $e->getMessage()
                ], 500);
            }

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

            $dosen = Auth::guard('dosen')->user();

            // Check if OTP is expired
            if (Carbon::now()->isAfter($dosen->email_verification_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.'
                ]);
            }

            // Verify OTP
            if ($request->otp != $dosen->email_verification_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP. Please try again.'
                ]);
            }

            // Update email and verification status
            $dosen->email = $dosen->verification_email;
            $dosen->is_email_verified = true;
            $dosen->email_verified_at = Carbon::now();
            $dosen->email_verification_code = null;
            $dosen->verification_email = null;
            $dosen->save();

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
            Log::info('Received resend OTP request');

            $dosen = Auth::guard('dosen')->user();
            Log::info('User authenticated', ['user_id' => $dosen->id]);

            if (!$dosen->verification_email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email verification in progress.'
                ]);
            }

            // Generate new OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Update OTP and expiration time
            $dosen->email_verification_code = $otp;
            $dosen->email_verification_expires_at = Carbon::now()->addMinutes(15);
            $saved = $dosen->save();

            if (!$saved) {
                Log::error('Failed to save new OTP data');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate new OTP. Please try again.'
                ], 500);
            }

            Log::info('New OTP generated for resend', [
                'user_id' => $dosen->id,
                'verification_email' => $dosen->verification_email,
                'otp' => $otp, // Don't log OTP in production!
                'expires_at' => $dosen->email_verification_expires_at
            ]);

            // Send email with new OTP
            try {
                $this->sendOTPEmail($dosen->verification_email, $otp, $dosen->nama_dosen);
                Log::info('Resend OTP email sent successfully');

                // For development, include OTP in response
                if (config('app.debug')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'New OTP sent to your email. Please check your inbox.',
                        'debug_otp' => $otp
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'New OTP sent to your email. Please check your inbox.'
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send resend OTP email: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);

                // For development, return success with OTP even if email fails
                if (config('app.debug')) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Email sending failed but OTP generated. For debugging: ' . $otp,
                        'debug_otp' => $otp
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to resend OTP: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resend OTP: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again.'
            ], 500);
        }
    }

    // Show email verification modal
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

    // Helper function to send OTP email
    private function sendOTPEmail($email, $otp, $name)
    {
        try {
            Log::info('Preparing to send OTP email', ['email' => $email]);

            $data = [
                'otp' => $otp,
                'name' => $name
            ];

            Mail::send('emails.otp-verification', $data, function($message) use($email) {
                $message->to($email)
                        ->subject('Email Verification Code - Sistem Dokumen Digital');
            });

            // Check if there were any failures
            if (Mail::failures()) {
                Log::error('Failed to send email to: ' . $email, ['failures' => Mail::failures()]);
                throw new \Exception('Failed to send email: ' . implode(', ', Mail::failures()));
            }

            Log::info('Email sent successfully to: ' . $email);
            return true;
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    public function approveDokumen($id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            // Pastikan dokumen milik dosen yang sedang login
            if ($dokumen->id_dosen != auth()->guard('dosen')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            // Update status dokumen menjadi disetujui
            $dokumen->update([
                'status_dokumen' => 'disetujui',
                'tanggal_verifikasi' => now()
            ]);

            // Create activity log if you have it
            // ActivityLog::create([
            //     'user_id' => auth()->guard('dosen')->id(),
            //     'action' => 'approve',
            //     'dokumen_id' => $dokumen->id,
            //     'description' => 'Dokumen telah disetujui'
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error approving document: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui dokumen: ' . $e->getMessage()
            ], 500);
        }
    }
}
