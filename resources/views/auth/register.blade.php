<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Nomor Induk (NIM/NIDN) -->
        <div class="mt-4">
            <x-input-label for="nomor_induk" :value="__('Nomor Induk (NIM/NIDN)')" />
            <x-text-input id="nomor_induk" class="block mt-1 w-full" type="text" name="nomor_induk" :value="old('nomor_induk')" required />
            <x-input-error :messages="$errors->get('nomor_induk')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role_select" :value="__('Peran (Role)')" />
            <select id="role_select" name="role_select" class="block mt-1 w-full border-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-md shadow-sm" required>
                <option value="ultraadmin" {{ old('role_select') === 'ultraadmin' ? 'selected' : '' }}>Ultra Admin</option>
                <option value="superadmin_keperawatan" {{ old('role_select') === 'superadmin_keperawatan' ? 'selected' : '' }}>Super Admin Keperawatan</option>
                <option value="superadmin_kebidanan" {{ old('role_select') === 'superadmin_kebidanan' ? 'selected' : '' }}>Super Admin Kebidanan</option>
                <option value="superadmin_kesehatan_gigi" {{ old('role_select') === 'superadmin_kesehatan_gigi' ? 'selected' : '' }}>Super Admin Kesehatan Gigi</option>
                <option value="superadmin_ortotik_prostetik" {{ old('role_select') === 'superadmin_ortotik_prostetik' ? 'selected' : '' }}>Super Admin Ortotik Prostetik</option>
            </select>
            <x-input-error :messages="$errors->get('role_select')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
