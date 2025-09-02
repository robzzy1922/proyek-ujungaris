<?php

namespace App\Http\Controllers;

use App\Models\Kuwu;
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

class KuwuController extends Controller
{
    public function dashboardKuwu(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $kuwu_id = auth()->guard('kuwu')->user()->id;

        $query = Dokumen::with('kuwu')
            ->where('id_kuwu', $kuwu_id);

        if ($status) {
            $query->where('status_dokumen', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('tanggal_pengajuan', 'like', "%{$search}%")
                  ->orWhere('nama_pemohon', 'like', "%{$search}%")
                  ->orWhereHas('kuwu', function ($q) use ($search) {
                      $q->where('nama_kuwu', 'like', "%{$search}%");
                  })
                  ->orWhere('status_dokumen', 'like', "%{$search}%");
            });
        }

        $dokumens = $query->latest()->get();

        $countDiajukan = Dokumen::where('id_kuwu', $kuwu_id)
            ->where('status_dokumen', 'diajukan')->count();
        $countDisahkan = Dokumen::where('id_kuwu', $kuwu_id)
            ->where('status_dokumen', 'disahkan')->count();
        $countDisetujui = Dokumen::where('id_kuwu', $kuwu_id)
            ->where('status_dokumen', 'disetujui')->count();

        return view('user.kuwu.dashboard_kuwu', compact('dokumens', 'status', 'countDiajukan', 'countDisahkan', 'countDisetujui'));
    }

    public function create()
    {
        return view('user.kuwu.create_tandatangan');
    }

    public function riwayat(Request $request)
    {
        $query = Dokumen::query()->where('id_kuwu', Auth::guard('kuwu')->id());

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

        return view('user.kuwu.riwayat_kuwu', compact('documents'));
    }

    public function showDokumen($id)
    {
        try {
            $kuwu_id = auth()->guard('kuwu')->user()->id;

            $dokumen = Dokumen::with(['admin', 'kuwu'])
                ->where('id_kuwu', $kuwu_id)
                ->where('id', $id)
                ->firstOrFail();

            // Periksa keberadaan file
            if (!Storage::disk('public')->exists($dokumen->file)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            // Generate URL yang valid
            $fileUrl = asset('storage/' . $dokumen->file);

            return response()->json([
                'success' => true,
                'id' => $dokumen->id,
                'nomor_surat' => $dokumen->nomor_surat,
                'tanggal_pengajuan' => Carbon::parse($dokumen->tanggal_pengajuan)->format('d F Y'),
                'nama_pemohon' => $dokumen->nama_pemohon,
                'status_dokumen' => ucfirst($dokumen->status_dokumen),
                'keterangan' => $dokumen->keterangan,
                'file_url' => $fileUrl,
                'pengaju' => $dokumen->admin ? [
                    'nama' => $dokumen->admin->namaAdmin,
                ] : null
            ]);

        } catch (\Exception $e) {
            Log::error('Error in showDokumen: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDokumenDetail($id)
    {
        $dokumen = Dokumen::with(['admin', 'kuwu'])->findOrFail($id);
        return response()->json($dokumen);
    }

    public function profile()
    {
        $kuwu = Auth::guard('kuwu')->user();
        return view('user.kuwu.profile', compact('kuwu'));
    }

    public function editProfile()
    {
        $kuwu = Auth::guard('kuwu')->user();
        return view('user.kuwu.profile', compact('kuwu'));
    }

    public function updateProfile(Request $request)
    {
        $kuwu = Auth::guard('kuwu')->user();

        $request->validate([
            'namaKuwu' => 'required|string|max:255',
            'email' => 'required|email',
            'noHp' => 'required|string|max:15',
            'currentPassword' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'passwordConfirmation' => 'nullable|same:password',
        ]);

        // Check if email is being changed
        $emailChanged = ($request->email !== $kuwu->email);

        // Update basic info
        $data = [
            'nama_kuwu' => $request->namaKuwu,
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
            if (!\Illuminate\Support\Facades\Hash::check($request->currentPassword, $kuwu->password)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['currentPassword' => 'Current password is incorrect']);
            }

            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $kuwu->update($data);

        // If email changed, redirect to verification page
        if ($emailChanged) {
            return redirect()->route('kuwu.profile')
                ->with('verify_email', true)
                ->with('new_email', $request->email);
        }

        return redirect()->route('kuwu.profile')
            ->with('success', 'Profile updated successfully');
    }

    public function updatePhoto(Request $request)
    {
        // Validasi file
        $request->validate([
            'profile_photo' => ['required', 'image', 'max:2048'], // Maksimal 2MB
        ]);

        // Ambil pengguna yang sedang login
        $kuwu = Auth::guard('kuwu')->user();

        // Periksa apakah pengguna ditemukan
        if (!$kuwu) {
            return back()->with('error', 'Failed to update profile photo. User not found.');
        }

        // Hapus foto profil lama jika ada
        if ($kuwu->profile && file_exists(public_path('profiles/' . $kuwu->profile))) {
            unlink(public_path('profiles/' . $kuwu->profile));
        }

        // Simpan file baru ke folder public/profiles
        $file = $request->file('profile_photo');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('profiles'), $filename);

        // Update database
        $kuwu->update([
            'profile' => $filename,
        ]);

        return back()->with('success', 'Profile photo updated successfully.');
    }


    public function destroyPhoto()
    {
        $kuwu = Auth::guard('kuwu')->user();

        if ($kuwu->profile) {
            Storage::disk('public')->delete($kuwu->profile);

            $kuwu->update([
                'profile' => null
            ]);
        }

        return back()->with('success', 'Profile photo removed successfully');
    }

    public function logout(Request $request)
    {
        Auth::guard('kuwu')->logout();
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
                'id_admin' => $dokumen->id_admin,
                'id_kuwu' => auth()->guard('kuwu')->id(),
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
            $dokumen = Dokumen::with(['kuwu', 'admin', 'kemahasiswaan'])->findOrFail($id);

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

            if ($dokumen->id_kuwu != auth()->guard('kuwu')->id()) {
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

            return view('user.kuwu.edit_qr', compact('dokumen'));

        } catch (\Exception $e) {
            Log::error('Error in editQrCode: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat QR Code: ' . $e->getMessage());
        }
    }


    public function approveDokumen($id)
    {
        try {
            $dokumen = Dokumen::findOrFail($id);

            // Pastikan dokumen milik kuwu yang sedang login
            if ($dokumen->id_kuwu != auth()->guard('kuwu')->id()) {
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
            //     'user_id' => auth()->guard('kuwu')->id(),
            //     'action' => 'approve',
            //     'dokumen_id' => $dokumen->id,
            //     'description' => 'Dokumen telah disetujui'
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving document: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui dokumen: ' . $e->getMessage()
            ], 500);
        }
    }
}
