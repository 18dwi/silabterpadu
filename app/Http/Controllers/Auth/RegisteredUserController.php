<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'nomor_induk' => ['required', 'string', 'max:50', 'unique:'.User::class],
            'role_select' => ['required', 'string', 'in:ultraadmin,superadmin_keperawatan,superadmin_kebidanan,superadmin_kesehatan_gigi,superadmin_ortotik_prostetik'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $roleSelect = $request->role_select;
        if ($roleSelect === 'ultraadmin') {
            $role = 'ultraadmin';
            $jurusan = 'keperawatan';
        } else {
            $role = 'superadmin';
            $jurusan = str_replace('superadmin_', '', $roleSelect);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nomor_induk' => $request->nomor_induk,
            'role' => $role,
            'jurusan' => $jurusan,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
