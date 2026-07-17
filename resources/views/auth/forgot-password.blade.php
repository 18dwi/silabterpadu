<x-guest-layout>
    <div class="mb-6 text-sm text-slate-600 leading-relaxed text-center">
        {{ __('Lupa kata sandi akun Anda? Masukkan alamat email terdaftar Anda untuk menerima link pemulihan kata sandi.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Field -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="Masukkan Email Terdaftar Anda" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-xs text-teal-600 hover:text-teal-800 transition duration-150 font-semibold" href="{{ route('login') }}">
                {{ __('Kembali ke Login') }}
            </a>
            <x-primary-button class="bg-teal-600 hover:bg-teal-700 text-white font-semibold">
                {{ __('Kirim Link Pemulihan') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
