 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit User - {{ $type === 'admin' ? 'Admin' : 'Kuwu' }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">

        <div class="flex">
            <!-- Sidebar -->
            <x-sidebar_ormawa />

            <!-- Main Content -->
            <div class="flex-1 p-8">
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h1 class="text-2xl font-semibold mb-6">
                            Edit {{ $type === 'admin' ? 'Admin' : 'Kuwu' }}
                        </h1>

                        <form action="{{ route('admin.user.update', ['id' => $user->id]) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="type" value="{{ $type }}">

                            @if($type === 'admin')
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Nama Admin
                                    </label>
                                    <input type="text" name="namaAdmin"
                                           value="{{ old('namaAdmin', $user->namaAdmin) }}"
                                           class="w-full px-3 py-2 border rounded-lg @error('namaAdmin') border-red-500 @enderror"
                                           required>
                                    @error('namaAdmin')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @else
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">
                                        Nama Kuwu
                                    </label>
                                    <input type="text" name="nama_kuwu"
                                           value="{{ old('nama_kuwu', $user->nama_kuwu) }}"
                                           class="w-full px-3 py-2 border rounded-lg @error('nama_kuwu') border-red-500 @enderror"
                                           required>
                                    @error('nama_kuwu')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    NIP
                                </label>
                                <input type="text" name="nip"
                                       value="{{ old('nip', $user->nip) }}"
                                       class="w-full px-3 py-2 border rounded-lg @error('nip') border-red-500 @enderror"
                                       required>
                                @error('nip')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    Email
                                </label>
                                <input type="email" name="email"
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full px-3 py-2 border rounded-lg @error('email') border-red-500 @enderror"
                                       required>
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    No HP
                                </label>
                                <input type="text" name="no_hp"
                                       value="{{ old('no_hp', $type === 'admin' ? $user->noHp : $user->no_hp) }}"
                                       class="w-full px-3 py-2 border rounded-lg @error('no_hp') border-red-500 @enderror"
                                       required>
                                @error('no_hp')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    Password Baru (Kosongkan jika tidak ingin mengubah)
                                </label>
                                <input type="password" name="password"
                                       class="w-full px-3 py-2 border rounded-lg @error('password') border-red-500 @enderror">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">
                                    Foto Profil
                                </label>
                                @if($user->profile)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($user->profile) }}"
                                             alt="Profile"
                                             class="w-32 h-32 object-cover rounded-lg">
                                    </div>
                                @endif
                                <input type="file" name="profile"
                                       class="w-full px-3 py-2 border rounded-lg @error('profile') border-red-500 @enderror"
                                       accept="image/*">
                                @error('profile')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <a href="{{ route('admin.kelola-user') }}"
                                   class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg mr-2">
                                    Batal
                                </a>
                                <button type="submit"
                                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
