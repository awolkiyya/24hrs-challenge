<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;

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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        // Generate String-based Unique Tenant ID
        $tenantId = Str::uuid()->toString(); // Example: "9b2b7c3c-8f9a-43a4-b7bc-d7f4f72e5e36"
    
        // Create Tenant Record
        $tenant = Tenant::create([
            'id' => $tenantId,
            'data' => json_encode(['plan' => 'basic']), // Optional metadata
        ]);
        error_log($tenantId);
    
        // Create User Linked to Tenant
        $user = User::create([
            'tenant_id' => $tenantId, // Associate with newly created tenant
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        event(new Registered($user));
    
        Auth::login($user);
    
        return redirect(route('dashboard'));
    }
    
}
