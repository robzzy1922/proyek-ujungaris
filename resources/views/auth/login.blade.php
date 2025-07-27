<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signix Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="flex flex-col w-full max-w-4xl overflow-hidden bg-white rounded-lg shadow-lg md:flex-row">
            <div class="hidden w-full bg-blue-50 md:w-1/2 md:flex md:items-center md:justify-center">
                <img src="{{ asset('images/gambar_login.png') }}" alt="Building"
                    class="object-cover object-center w-full h-full">
            </div>

            <div class="relative w-full p-6 md:w-1/2 md:p-10">
                <div class="absolute inset-0 md:hidden">
                    <img src="{{ asset('images/gambar_login.png ') }}" alt="Building"
                        class="object-cover object-center w-full h-full opacity-5">
                </div>

                <div class="relative z-10">
                    <div class="w-32 mx-auto mb-8 md:w-40 md:mx-0">
                        <img src="{{ asset('images/logo_signix.png') }}" alt="Logo"
                            class="object-contain w-full h-auto">
                    </div>

                    <form action="{{ route('login.submit') }}" method="POST" onsubmit="return validateForm()"
                        class="mt-4">
                        @csrf

                        <div class="mb-4">
                            <label for="role" class="block mb-2 text-sm text-gray-600 md:text-base">Masuk
                                Sebagai</label>
                            <div class="flex items-center bg-white border border-gray-300 rounded-md">
                                <span class="pl-3 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <select id="role" name="role"
                                    class="w-full px-3 py-2 text-sm text-gray-700 bg-transparent rounded-md md:text-base focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    onchange="toggleInputField()">
                                    <option value="">-</option>
                                    <option value="ormawa">Ormawa</option>
                                    <option value="dosen">Dosen</option>
                                    <option value="kemahasiswaan">Kemahasiswaan</option>
                                </select>
                            </div>
                        </div>

                        @if ($errors->has('login'))
                        <p class="mb-4 text-sm text-red-600">{{ $errors->first('login') }}</p>
                        @endif

                        <div id="emailField" class="hidden mb-4">
                            <label for="email" class="block mb-2 text-sm text-gray-600 md:text-base">Email</label>
                            <div class="flex items-center bg-white border border-gray-300 rounded-md">
                                <input type="email" name="email" id="email"
                                    class="w-full px-3 py-2 text-sm text-gray-700 bg-transparent rounded-md md:text-base focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                        </div>

                        <div id="nimField" class="mb-4"
                            style="{{ old('role') === 'ormawa' ? 'display: block;' : 'display: none;' }}">
                            <label for="nim" class="block mb-2 text-sm text-gray-600 md:text-base">NIM</label>
                            <div class="flex items-center bg-white border border-gray-300 rounded-md">
                                <input type="text" name="nim" id="nim" value="{{ old('nim') }}"
                                    class="w-full px-3 py-2 text-sm text-gray-700 bg-transparent rounded-md md:text-base focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                        </div>

                        <div id="nipField" class="mb-4"
                            style="{{ old('role') === 'dosen' || old('role') === 'kemahasiswaan' ? 'display: block;' : 'display: none;' }}">
                            <label for="nip" class="block mb-2 text-sm text-gray-600 md:text-base">NIP</label>
                            <div class="flex items-center bg-white border border-gray-300 rounded-md">
                                <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
                                    class="w-full px-3 py-2 text-sm text-gray-700 bg-transparent rounded-md md:text-base focus:outline-none focus:ring-2 focus:ring-blue-400">
                            </div>
                        </div>

                        <div class="mb-6" id="passwordField"
                            style="{{ old('role') ? 'display: block;' : 'display: none;' }}">
                            <label for="password" class="block mb-2 text-sm text-gray-600 md:text-base">Password</label>
                            <div class="flex items-center bg-white border border-gray-300 rounded-md">
                                <span class="pl-3 text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input id="password" name="password" type="password" placeholder="Masukkan Kata sandi"
                                    class="w-full px-3 py-2 text-sm text-gray-700 bg-transparent rounded-md md:text-base focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <button type="button" onclick="togglePasswordVisibility()"
                                    class="pr-3 text-gray-500 focus:outline-none">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div id="roleAlert" class="hidden mb-4 text-sm text-red-600">
                            Please select a role before submitting the form.
                        </div>

                        <div class="flex justify-end mb-6" id="forgotPasswordLink" style="display: none;">
                            <a href="javascript:void(0);" onclick="toggleForgotPassword()"
                                class="text-sm text-blue-600">Lupa kata sandi?</a>
                        </div>

                        <button type="submit" id="submitButton"
                            class="hidden w-full py-2 text-sm font-semibold text-white transition bg-blue-600 rounded-md hover:bg-blue-700 md:text-base">Masuk</button>
                    </form>

                    <p class="mt-4 text-xs text-center text-gray-500">
                        Dengan menggunakan layanan kami, Anda berarti setuju atas <a href="#"
                            class="text-blue-600">Syarat & Ketentuan</a> dan <a href="#" class="text-blue-600">Kebijakan
                            Privasi</a> Signix
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="forgotPasswordSection"
        class="flex hidden inset-0 justify-center items-center p-4 bg-gray-800 bg-opacity-75">
        <div class="p-6 mx-4 w-full max-w-sm bg-white rounded-lg shadow-lg md:p-8">
            <div class="flex justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c0-1.1.9-2 2-2s2 .9 2 2-2 2-2 2-2-.9-2-2zm0 0c0-1.1-.9-2-2-2s-2 .9-2 2 2 2 2 2 2-.9 2-2zm0 0c0-1.1.9-2 2-2s2 .9 2 2-2 2-2 2-2-.9-2-2zm0 0c0-1.1-.9-2-2-2s-2 .9-2 2 2 2 2 2 2-.9 2-2z" />
                </svg>
            </div>
            <h2 class="mb-4 text-lg font-semibold text-center">Kesulitan masuk?</h2>
            <p class="mb-4 text-sm text-center text-gray-600 md:text-base">Masukkan email, telepon, atau nama pengguna
                Anda dan kami akan mengirimkan tautan untuk masuk kembali ke akun Anda.</p>
            <input type="text" placeholder="Email, Telepon, atau Nama Pengguna"
                class="w-full px-3 py-2 mb-4 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 md:text-base">
            <button
                class="w-full py-2 text-sm font-semibold text-white transition bg-blue-600 rounded-md hover:bg-blue-700 md:text-base">Kirim
                tautan masuk</button>
            <p class="mt-4 text-sm text-center text-gray-600">Tidak bisa mengatur ulang kata sandi Anda?</p>
            <button onclick="toggleForgotPassword()"
                class="w-full py-2 mt-4 text-sm font-semibold text-gray-700 transition bg-gray-200 rounded-md hover:bg-gray-300 md:text-base">Kembali
                ke login</button>
        </div>
    </div>

    <script>
        function toggleInputField() {
            var role = document.getElementById('role').value;
            document.getElementById('emailField').style.display = 'none';
            document.getElementById('nimField').style.display = (role === 'ormawa') ? 'block' : 'none';
            document.getElementById('nipField').style.display = (role === 'dosen' || role === 'kemahasiswaan') ? 'block' : 'none';
            document.getElementById('passwordField').style.display = role ? 'block' : 'none';
            document.getElementById('submitButton').style.display = role ? 'block' : 'none';
            document.getElementById('forgotPasswordLink').style.display = role ? 'block' : 'none';
        }

        function validateForm() {
            var role = document.getElementById('role').value;
            var roleAlert = document.getElementById('roleAlert');
            if (!role) {
                roleAlert.style.display = 'block';
            return false;
            }
            roleAlert.style.display = 'none';
        return true;
        }

        function togglePasswordVisibility() {
            var passwordInput = document.getElementById('password');
            var eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.97 10.97 0 0112 20c-5.52 0-10-4.48-10-10S6.48 0 12 0c2.61 0 5.01 1.01 6.94 2.94M1 1l22 22"></path>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        function toggleForgotPassword() {
            var forgotPasswordSection = document.getElementById('forgotPasswordSection');
            forgotPasswordSection.classList.toggle('hidden');
        }
    </script>
</body>

</html>