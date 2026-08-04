<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::query()
            ->withCount('users')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('admin.tenants.form', ['tenant' => new Tenant()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $tenant = Tenant::create($data);

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with('success', 'Tenant berhasil dibuat.');
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.form', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $this->validateData($request, $tenant->id);

        $tenant->update($data);

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with('success', 'Tenant berhasil diperbarui.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()
            ->route('admin.tenants.index')
            ->with('success', 'Tenant dihapus.');
    }

    public function updateStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Tenant::statuses())],
            'verification_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $tenant->update($data);

        return redirect()
            ->route('admin.tenants.edit', $tenant)
            ->with('success', 'Status tenant diperbarui.');
    }

    protected function validateData(Request $request, ?string $ignoreId = null): array
    {
        $subdomainUnique = 'unique:tenants,subdomain';
        if ($ignoreId) {
            $subdomainUnique .= ',' . $ignoreId;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9\-]*$/', $subdomainUnique],
            'custom_domain' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:' . implode(',', Tenant::statuses())],
            'storage_quota' => ['nullable', 'integer', 'min:0'],
            'verification_note' => ['nullable', 'string', 'max:2000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['subdomain'] = Str::lower($data['subdomain']);

        return $data;
    }
}