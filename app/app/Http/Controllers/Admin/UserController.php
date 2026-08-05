<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesTargetTenant;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    use ResolvesTargetTenant;

    public const TENANT_ROLES = ['admin_yayasan', 'staff_yayasan', 'donatur'];

    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function index(Request $request): View
    {
        $tenantId = $this->resolveTargetTenantId($request);

        if (! $tenantId) {
            return view('admin.partials.tenant-picker', [
                'pickerTitle' => 'Pengguna',
                'pickerRoute' => 'admin.users.index',
                'tenants' => Tenant::query()->withoutGlobalScopes()->orderBy('name')->get(),
            ]);
        }

        $users = User::query()
            ->with('roles')
            ->where('tenant_id', $tenantId)
            ->when($request->filled('role'), fn ($q) => $q->role($request->role))
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('email', 'like', '%' . $request->q . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'tenantId'));
    }

    public function create(Request $request): View
    {
        $this->requireTargetTenantId($request);

        return view('admin.users.form', ['user' => new User(), 'tenantRoles' => self::TENANT_ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->requireTargetTenantId($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'role' => ['required', 'in:' . implode(',', self::TENANT_ROLES)],
        ]);

        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'is_active' => $request->boolean('is_active'),
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $user->assignRole($data['role']);

        return redirect()
            ->route('admin.users.edit', ['user' => $user, 'tenant_id' => $tenantId])
            ->with('success', 'Pengguna berhasil dibuat.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->requireTargetTenantId($request);
        $this->guardSameTenant($user, $request);

        return view('admin.users.form', ['user' => $user, 'tenantRoles' => self::TENANT_ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $tenantId = $this->requireTargetTenantId($request);
        $this->guardSameTenant($user, $request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:255', 'unique:users,phone,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'role' => ['required', 'in:' . implode(',', self::TENANT_ROLES)],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->is_active = $request->boolean('is_active');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
        $user->syncRoles($data['role']);

        return redirect()
            ->route('admin.users.edit', ['user' => $user, 'tenant_id' => $tenantId])
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $tenantId = $this->requireTargetTenantId($request);
        $this->guardSameTenant($user, $request);

        abort_if($user->id === $request->user()->id, 422, 'Tidak dapat menghapus akun sendiri.');

        $user->delete();

        return redirect()
            ->route('admin.users.index', ['tenant_id' => $tenantId])
            ->with('success', 'Pengguna dihapus.');
    }

    protected function guardSameTenant(User $user, Request $request): void
    {
        abort_unless($user->tenant_id === $this->resolveTargetTenantId($request), 403);
    }
}
