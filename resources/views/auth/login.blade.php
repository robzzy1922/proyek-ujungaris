<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Desa Ujungaris - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-xl mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-semibold text-gray-800">Web Desa Ujungaris</h1>
                <p class="text-gray-500 text-sm mt-1">Silakan login untuk melanjutkan</p>
            </div>

            <form action="{{ route('login.submit') }}" method="POST" onsubmit="return validateForm()">
                @csrf

                <!-- Role Selection -->
                <div class="mb-4">
                    <label for="role" class="block text-gray-700 text-sm font-medium mb-2">Masuk Sebagai</label>
                    <select id="role" name="role"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onchange="toggleInputField()">
                        <option value="">Pilih Role</option>
                        <option value="ormawa">Admin</option>
                        <option value="dosen">Kuwu</option>
                    </select>
                </div>

                @if ($errors->has('login'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-600 text-sm">{{ $errors->first('login') }}</p>
                </div>
                @endif

                <!-- Email Field (Hidden by default) -->
                <div id="emailField" class="hidden mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" id="email"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Masukkan email">
                </div>

                <!-- NIM Field -->
                <div id="nimField" class="mb-4 hidden">
                    <label for="nim" class="block text-gray-700 text-sm font-medium mb-2">NIM</label>
                    <input type="text" name="nim" id="nim" value="{{ old('nim') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Masukkan NIM">
                </div>

                <!-- NIP Field -->
                <div id="nipField" class="mb-4 hidden">
                    <label for="nip" class="block text-gray-700 text-sm font-medium mb-2">NIP</label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Masukkan NIP">
                </div>

                <!-- Password Field -->
                <div class="mb-4 hidden" id="passwordField">
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password"
                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Masukkan password">
                        <button type="button" onclick="togglePasswordVisibility()"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700">
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Role Alert -->
                <div id="roleAlert" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-600 text-sm">Silakan pilih role terlebih dahulu</p>
                </div>

                <!-- Forgot Password Link -->
                <div class="flex justify-end mb-6 hidden" id="forgotPasswordLink">
                    <button type="button" onclick="toggleForgotPassword()"
                            class="text-blue-600 hover:text-blue-700 text-sm">
                        Lupa password?
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitButton"
                        class="hidden w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    Masuk
                </button>
            </form>

            <!-- Footer -->
            <p class="mt-6 text-xs text-center text-gray-500">
                © 2025 Web Desa Ujungaris. Hak cipta dilindungi.
            </p>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordSection" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 rounded-xl mb-4">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Lupa Password?</h2>
                <p class="text-gray-600 text-sm">
                    Masukkan email Anda untuk mendapatkan link reset password
                </p>
            </div>

            <div class="space-y-4">
                <input type="email"
                       placeholder="Email"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <button class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Kirim Link Reset
                </button>
                <button onclick="toggleForgotPassword()"
                        class="w-full py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Kembali
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleInputField() {
            var role = document.getElementById('role').value;

            // Hide all fields first
            document.getElementById('emailField').classList.add('hidden');
            document.getElementById('nimField').classList.add('hidden');
            document.getElementById('nipField').classList.add('hidden');
            document.getElementById('passwordField').classList.add('hidden');
            document.getElementById('submitButton').classList.add('hidden');
            document.getElementById('forgotPasswordLink').classList.add('hidden');

            if (role) {
                // Show appropriate fields based on role
                if (role === 'ormawa') {
                    document.getElementById('nimField').classList.remove('hidden');
                } else if (role === 'dosen' || role === 'kemahasiswaan') {
                    document.getElementById('nipField').classList.remove('hidden');
                }

                // Always show password, submit button, and forgot password link when role is selected
                document.getElementById('passwordField').classList.remove('hidden');
                document.getElementById('submitButton').classList.remove('hidden');
                document.getElementById('forgotPasswordLink').classList.remove('hidden');
            }
        }

        function validateForm() {
            var role = document.getElementById('role').value;
            var roleAlert = document.getElementById('roleAlert');

            if (!role) {
                roleAlert.classList.remove('hidden');
                return false;
            }

            roleAlert.classList.add('hidden');
            return true;
        }

        function togglePasswordVisibility() {
            var passwordInput = document.getElementById('password');
            var eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        function toggleForgotPassword() {
            var forgotPasswordSection = document.getElementById('forgotPasswordSection');
            forgotPasswordSection.classList.toggle('hidden');
        }

        // Initialize the form state based on old input (for Laravel validation errors)
        document.addEventListener('DOMContentLoaded', function() {
            var oldRole = '{{ old("role") }}';
            if (oldRole) {
                document.getElementById('role').value = oldRole;
                toggleInputField();
            }
        });
    </script>
</body>

</html>
