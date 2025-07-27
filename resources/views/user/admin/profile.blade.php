@extends('layouts.app_admin')
@section('title', 'Profil Admin')
@section('content')
    <div class="container px-4 py-8 mx-auto">
        <!-- Alert Component -->
        <div id="alert" class="fixed top-4 right-4 px-4 py-3 bg-green-100 rounded-lg border-l-4 border-green-500 shadow-lg transition-transform duration-300 transform {{ session('success') ? '' : 'hidden' }}">
            <div class="flex items-center space-x-2">
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <p class="font-medium text-green-700">{{ session('success') ?? 'Changes saved successfully!' }}</p>
            </div>
        </div>

        <!-- Verification Modal -->
        <div id="verificationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transform transition-opacity duration-300 {{ session('verify_email') ? '' : 'hidden' }}">
            <div class="p-6 mx-4 w-full max-w-md bg-white rounded-lg shadow-xl">
                <div class="text-center">
                    <h3 class="mb-2 text-xl font-bold text-gray-900">Verify Your Email</h3>
                    <div class="mb-6 text-gray-600">
                        We need to verify your new email address <span id="verificationEmail" class="font-semibold">{{ session('new_email') }}</span>
                    </div>
                </div>

                <div id="verificationStep1" class="">
                    <p class="mb-4 text-sm text-gray-600">Please verify your email by clicking the button below to receive a verification code.</p>
                    <button id="sendOtpBtn" class="py-3 w-full font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Send Verification Code
                    </button>
                </div>

                <div id="verificationStep2" class="hidden">
                    <div class="mb-4">
                        <label for="otpInput" class="block mb-1 text-sm font-medium text-gray-700">Enter 6-Digit Code</label>
                        <input type="text" id="otpInput" maxlength="6" class="px-3 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter verification code">
                        <p class="mt-2 text-sm text-gray-600">Code expires in <span id="countdownTimer">15:00</span></p>
                    </div>
                    <button id="verifyOtpBtn" class="py-3 mb-3 w-full font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Verify Code
                    </button>
                    <button id="resendOtpBtn" class="py-2 w-full font-medium text-blue-500 bg-white rounded-lg border border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Resend Code
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <button id="closeModalBtn" class="text-sm text-gray-500 hover:text-gray-700">
                        Skip for now (you'll need to verify later)
                    </button>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-3xl bg-white rounded-xl border border-gray-100 shadow-lg">
            <!-- Profile Header -->
            <div class="p-6 bg-gradient-to-bl from-blue-400 to-blue-500 rounded-t-xl border-b">
                <h2 class="text-2xl font-bold text-white">Profile Settings</h2>
            </div>

            <!-- Profile Photo Section -->
            <div class="p-8 bg-gray-50 border-b">
                <div class="flex justify-center items-center space-x-6">
                    <div class="flex relative flex-col items-center">
                        @if($admin->profile)
                        <img src="{{ asset('storage/' . $admin->profile) }}"
                        alt="Profile Picture"
                        class="object-cover w-20 h-20 rounded-full">
                        @else
                            <div class="flex justify-center items-center w-40 h-40 bg-gradient-to-r from-blue-400 to-blue-500 rounded-full border-4 border-white shadow-lg">
                                <span class="text-5xl font-semibold text-white">{{ substr($admin->namaMahasiswa, 0, 1) }}</span>
                            </div>
                        @endif

                        <div class="flex mt-6 space-x-3">
                            <form action="{{ route('admin.profile.photo.update') }}" method="POST" enctype="multipart/form-data" id="photoForm" class="inline">
                                @csrf
                                <input type="file" name="profile_photo" id="profile_photo" class="hidden" accept="image/*" onchange="this.form.submit()">
                                <label for="profile_photo" class="inline-block px-4 py-2 text-sm font-medium text-white bg-blue-400 rounded-lg shadow-md transition duration-200 cursor-pointer hover:bg-blue-500">
                                    Change Photo
                                </label>
                            </form>

                            @if($admin->profile)
                                <form action="{{ route('admin.profile.photo.destroy') }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-block px-4 py-2 text-sm font-medium text-red-600 bg-white rounded-lg border border-red-200 shadow-md transition duration-200 hover:bg-red-50">
                                        Remove Photo
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Form -->
            <form action="{{ route('admin.profile.update') }}" method="POST" class="p-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="namaMahasiswa" class="block text-sm font-semibold text-gray-700">Name</label>
                        <input type="text" name="namaMahasiswa" id="namaMahasiswa"
                               value="{{ old('namaMahasiswa', $admin->namaMahasiswa) }}"
                               class="block px-4 py-3 w-full rounded-lg border border-gray-300 shadow-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    </div>

                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-semibold text-gray-700">Email</label>
                        <div class="relative">
                            <input type="email" name="email" id="email"
                                   value="{{ old('email', $admin->email) }}"
                                   class="block px-4 py-3 pr-12 w-full rounded-lg border border-gray-300 shadow-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <div class="flex absolute inset-y-0 right-0 items-center pr-3">
                                <span id="emailStatus" class="text-xs font-medium"></span>
                            </div>
                        </div>
                        <p id="emailHelp" class="mt-1 text-xs text-gray-500">Changing your email requires verification.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="noHp" class="block text-sm font-semibold text-gray-700">Phone Number</label>
                        <input type="text" name="noHp" id="noHp"
                               value="{{ old('noHp', $admin->noHp) }}"
                               class="block px-4 py-3 w-full rounded-lg border border-gray-300 shadow-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    </div>
                </div>

                <!-- Password Section -->
                <div class="pt-8 mt-8 border-t border-gray-200">
                    <h3 class="mb-6 text-xl font-semibold text-gray-900">Update Password</h3>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label for="currentPassword" class="block text-sm font-semibold text-gray-700">Current Password</label>
                            <input type="password" name="currentPassword" id="currentPassword"
                                   class="block px-4 py-3 w-full rounded-lg border border-gray-300 shadow-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-semibold text-gray-700">New Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password"
                                       class="block px-4 py-3 w-full rounded-lg border border-gray-300 shadow-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                                       onkeyup="checkPasswordMatch()">
                                <button type="button" onclick="togglePassword('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-500 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="hidden w-5 h-5 text-gray-500 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="passwordConfirmation" class="block text-sm font-semibold text-gray-700">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="passwordConfirmation" id="passwordConfirmation"
                                       class="block px-4 py-3 w-full rounded-lg border border-gray-300 shadow-sm transition duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                                       onkeyup="checkPasswordMatch()">
                                <button type="button" onclick="togglePassword('passwordConfirmation')" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="w-5 h-5 text-gray-500 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="hidden w-5 h-5 text-gray-500 eye-closed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <p id="password_message" class="hidden mt-2 text-sm font-medium"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8">
                    <button type="submit" class="px-6 py-3 text-sm font-medium text-white bg-blue-400 rounded-lg shadow-md transition duration-200 hover:bg-blue-500 focus:ring-4 focus:ring-blue-400 focus:ring-opacity-50">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function checkPasswordMatch() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('passwordConfirmation');
            const message = document.getElementById('password_message');

            if (password.value || confirmPassword.value) {
                message.classList.remove('hidden');
                if (password.value !== confirmPassword.value) {
                    message.classList.remove('text-green-600');
                    message.classList.add('text-red-500');
                    message.textContent = '❌ Passwords do not match!';
                } else {
                    message.classList.remove('text-red-500');
                    message.classList.add('text-green-600');
                    message.textContent = '✓ Passwords match!';
                }
            } else {
                message.classList.add('hidden');
            }
        }

        // Update alert handling
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('alert');

            if (!alert.classList.contains('hidden')) {
                // Hide the alert after 5 seconds
                setTimeout(() => {
                    alert.classList.add('translate-y-[-100%]');
                    setTimeout(() => {
                        alert.classList.add('hidden');
                        alert.classList.remove('translate-y-[-100%]');
                    }, 300);
                }, 5000);
            }

            // Check email verification status
            updateEmailVerificationStatus();

            // Setup verification modal handlers
            setupVerificationHandlers();
        });

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const eyeOpen = button.querySelector('.eye-open');
            const eyeClosed = button.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // Update email verification status indicator
        function updateEmailVerificationStatus() {
            fetch('{{ route('admin.email.verification.status') }}')
                .then(response => response.json())
                .then(data => {
                    const statusEl = document.getElementById('emailStatus');

                    if (data.is_verified) {
                        statusEl.textContent = 'Verified';
                        statusEl.classList.add('text-green-600');
                        statusEl.innerHTML = `<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>`;
                    } else {
                        statusEl.textContent = 'Unverified';
                        statusEl.classList.add('text-orange-500');
                        statusEl.innerHTML = `<svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>`;
                    }
                })
                .catch(error => console.error('Error fetching verification status:', error));
        }

        function setupVerificationHandlers() {
            const modal = document.getElementById('verificationModal');
            const closeBtn = document.getElementById('closeModalBtn');
            const sendOtpBtn = document.getElementById('sendOtpBtn');
            const verifyOtpBtn = document.getElementById('verifyOtpBtn');
            const resendOtpBtn = document.getElementById('resendOtpBtn');
            const step1 = document.getElementById('verificationStep1');
            const step2 = document.getElementById('verificationStep2');
            const otpInput = document.getElementById('otpInput');
            const countdownTimer = document.getElementById('countdownTimer');
            const verificationEmail = document.getElementById('verificationEmail');

            let countdownInterval;

            // Close modal
            closeBtn.addEventListener('click', function() {
                modal.classList.add('hidden');
                clearInterval(countdownInterval);
            });

            // Show modal when email input changes
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('change', function() {
                const newEmail = this.value;
                if (newEmail && newEmail !== '{{ $admin->email }}') {
                    const formData = new FormData();
                    formData.append('email', newEmail);

                    fetch('{{ route('admin.email.show.verification') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });

            // Function to show alert
            function showAlert(message, type = 'success') {
                const alert = document.getElementById('alert');
                const alertMessage = alert.querySelector('p');

                alert.classList.remove('hidden', 'bg-green-100', 'bg-red-100', 'border-green-500', 'border-red-500');
                alertMessage.classList.remove('text-green-700', 'text-red-700');

                if (type === 'success') {
                    alert.classList.add('bg-green-100', 'border-green-500');
                    alertMessage.classList.add('text-green-700');
                } else {
                    alert.classList.add('bg-red-100', 'border-red-500');
                    alertMessage.classList.add('text-red-700');
                }

                alertMessage.textContent = message;
                alert.classList.remove('hidden');

                setTimeout(() => {
                    alert.classList.add('hidden');
                }, 5000);
            }

            // Send OTP
            sendOtpBtn.addEventListener('click', function() {
                const email = verificationEmail.textContent.trim();
                const formData = new FormData();
                formData.append('email', email);

                fetch('{{ route('admin.email.send.otp') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        step1.classList.add('hidden');
                        step2.classList.remove('hidden');
                        startCountdown();
                        showAlert(data.message);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to send OTP. Please try again.', 'error');
                });
            });

            // Verify OTP
            verifyOtpBtn.addEventListener('click', function() {
                const otp = otpInput.value.trim();

                if (!otp || otp.length !== 6) {
                    showAlert('Please enter a valid 6-digit code', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('otp', otp);

                fetch('{{ route('admin.email.verify.otp') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message);
                        verificationModal.classList.add('hidden');
                        clearInterval(countdownInterval);
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to verify OTP. Please try again.', 'error');
                });
            });

            // Resend OTP
            resendOtpBtn.addEventListener('click', function() {
                console.log('Attempting to resend OTP');

                fetch('{{ route('admin.email.resend.otp') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        // Reset countdown
                        clearInterval(countdownInterval);
                        startCountdown();

                        // Show success notification
                        showAlert(data.message, 'success');
                    } else {
                        showAlert(data.message || 'Failed to resend OTP', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error resending OTP:', error);
                    showAlert('Failed to resend OTP. Please try again.', 'error');
                });
            });

            // Start countdown timer (15 minutes)
            function startCountdown() {
                let minutes = 15;
                let seconds = 0;

                // Clear any existing countdown
                clearInterval(countdownInterval);

                countdownInterval = setInterval(() => {
                    if (seconds === 0) {
                        if (minutes === 0) {
                            clearInterval(countdownInterval);
                            countdownTimer.textContent = "Expired";
                            countdownTimer.classList.add('text-red-500');
                            return;
                        }
                        minutes--;
                        seconds = 59;
                    } else {
                        seconds--;
                    }

                    countdownTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }, 1000);
            }
        }
    </script>
@endsection
