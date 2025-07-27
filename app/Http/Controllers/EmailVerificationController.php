<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Ormawas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Services\BrevoEmailService;

class EmailVerificationController extends Controller
{
    protected $brevoEmailService;

    public function __construct(BrevoEmailService $brevoEmailService)
    {
        $this->brevoEmailService = $brevoEmailService;
    }

    // Generate OTP and send to email
    public function sendEmailOTP(Request $request)
    {
        try {
            Log::info('Received send OTP request', $request->all());

            $request->validate([
                'email' => 'required|email'
            ]);

            $ormawa = Auth::guard('ormawa')->user();
            Log::info('User authenticated', ['user_id' => $ormawa->id]);

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
            $saved = $ormawa->save();

            if (!$saved) {
                Log::error('Failed to save OTP data to database');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save verification data. Please try again.'
                ], 500);
            }

            Log::info('OTP generated and saved', [
                'user_id' => $ormawa->id,
                'verification_email' => $ormawa->verification_email,
                'otp' => $otp, // Don't log OTP in production!
                'expires_at' => $ormawa->email_verification_expires_at
            ]);

            // Send email with OTP
            try {
                $this->sendOTPEmail($ormawa->verification_email, $otp, $ormawa->namaMahasiswa);
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
                        'message' => 'OTP sent to your email. Please check your inbox.'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process sendEmailOTP: ' . $e->getMessage(), [
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
            Log::info('Received verify OTP request', $request->all());

            $request->validate([
                'otp' => 'required|string|digits:6'
            ]);

            $ormawa = Auth::guard('ormawa')->user();
            Log::info('User authenticated', ['user_id' => $ormawa->id]);

            Log::info('Verifying OTP', [
                'user_id' => $ormawa->id,
                'submitted_otp' => $request->otp,
                'stored_otp' => $ormawa->email_verification_code,
                'expires_at' => $ormawa->email_verification_expires_at
            ]);

            // Check if verification is in progress
            if (!$ormawa->verification_email || !$ormawa->email_verification_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email verification in progress.'
                ]);
            }

            // Check if OTP is expired
            if (!$ormawa->email_verification_expires_at || Carbon::now()->isAfter($ormawa->email_verification_expires_at)) {
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
            $saved = $ormawa->save();

            if (!$saved) {
                Log::error('Failed to save verification status');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update verification status. Please try again.'
                ], 500);
            }

            Log::info('Email verified successfully', [
                'user_id' => $ormawa->id,
                'email' => $ormawa->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to verify OTP: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify OTP: ' . $e->getMessage()
                ], 500);
            }

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

            $ormawa = Auth::guard('ormawa')->user();
            Log::info('User authenticated', ['user_id' => $ormawa->id]);

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
            $saved = $ormawa->save();

            if (!$saved) {
                Log::error('Failed to save new OTP data');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate new OTP. Please try again.'
                ], 500);
            }

            Log::info('New OTP generated for resend', [
                'user_id' => $ormawa->id,
                'verification_email' => $ormawa->verification_email,
                'otp' => $otp, // Don't log OTP in production!
                'expires_at' => $ormawa->email_verification_expires_at
            ]);

            // Send email with new OTP
            try {
                $this->sendOTPEmail($ormawa->verification_email, $otp, $ormawa->namaMahasiswa);
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

    // Helper function to send OTP email
    private function sendOTPEmail($email, $otp, $name)
    {
        try {
            Log::info('Preparing to send OTP email', ['email' => $email]);

            $data = [
                'otp' => $otp,
                'name' => $name
            ];

            // Render Blade template to HTML string
            $htmlContent = View::make('emails.otp-verification', $data)->render();
            $subject = 'Email Verification Code - Sistem Dokumen Digital';

            $success = $this->brevoEmailService->sendEmail($email, $subject, $htmlContent, $name);

            if (!$success) {
                Log::error('Failed to send email to: ' . $email);
                throw new \Exception('Failed to send email');
            }

            Log::info('Email sent successfully to: ' . $email);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }
}