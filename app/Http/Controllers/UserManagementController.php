<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Grup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        return view('user-management.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['nama', 'username', 'email', 'is_active', 'created_at'];
        $orderColumn     = $sortableColumns[max(0, $orderColIndex - 1)] ?? 'nama';

        $totalRecords = User::count();

        $query = User::with('grup');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $users = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $currentUser = auth()->user();
        $data = $users->map(function ($user) use (&$no, $currentUser) {
            $no++;
            $isSelf = $currentUser->id === $user->id;
            $encId = IdEncryptor::encrypt($user->id);

            $statusBadge = $user->is_active
                ? '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-success-100 text-success-600">Aktif</span>'
                : '<span class="px-12 py-4 rounded-pill fw-medium text-sm bg-warning-100 text-warning-600">Nonaktif</span>';

            $grupBadge = $user->grup
                ? '<span class="fw-medium text-sm text-warning-600">' . e($user->grup->nama_grup) . '</span>'
                : '-';

            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('user-management', 'edit')) {
                $editBtn = '<a href="' . route('user-management.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('user-management', 'delete') && !$isSelf) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($user->nama) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'       => $no,
                'nama'     => e($user->nama),
                'username' => e($user->username),
                'email'    => e($user->email),
                'grup'     => $grupBadge,
                'status'   => $statusBadge,
                'aksi'     => '<div class="d-inline-flex align-items-center gap-8">' . $editBtn . $deleteBtn . '</div>',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data->values(),
        ]);
    }

    public function create(): View
    {
        $grups = Grup::orderBy('nama_grup')->get();
        return view('user-management.create', compact('grups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
            'grup_id'  => ['required', 'exists:grup,id'],
            'is_active' => ['boolean'],
            'foto'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama.required'      => 'Nama wajib diisi.',
            'nama.max'           => 'Nama maksimal 255 karakter.',
            'username.required'  => 'Username wajib diisi.',
            'username.min'       => 'Username minimal 3 karakter.',
            'username.max'       => 'Username maksimal 50 karakter.',
            'username.regex'     => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'username.unique'    => 'Username sudah digunakan.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.max'          => 'Email maksimal 255 karakter.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'grup_id.required'   => 'Grup wajib dipilih.',
            'grup_id.exists'     => 'Grup tidak valid.',
            'foto.image'         => 'File harus berupa gambar.',
            'foto.mimes'         => 'Format gambar harus: jpg, jpeg, png, atau webp.',
            'foto.max'           => 'Ukuran foto maksimal 2MB.',
        ]);

        DB::beginTransaction();
        try {
            $fotoName = null;
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $fotoName = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('upload/foto-user'), $fotoName);
            }

            User::create([
                'nama'      => $validated['nama'],
                'username'  => strtolower(trim($validated['username'])),
                'email'     => $validated['email'],
                'password'  => $validated['password'],
                'grup_id'   => $validated['grup_id'],
                'is_active' => $request->boolean('is_active', true),
                'foto'      => $fotoName,
            ]);

            DB::commit();
            return redirect()->route('user-management.index')
                ->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Clean up uploaded file on error
            if ($fotoName && file_exists(public_path('upload/foto-user/' . $fotoName))) {
                unlink(public_path('upload/foto-user/' . $fotoName));
            }
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan user.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $user = User::findOrFail($id);
        $grups = Grup::orderBy('nama_grup')->get();
        return view('user-management.edit', compact('user', 'grups'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9._]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6', 'max:255', 'confirmed'],
            'grup_id'  => ['required', 'exists:grup,id'],
            'is_active' => ['boolean'],
            'foto'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nama.required'      => 'Nama wajib diisi.',
            'nama.max'           => 'Nama maksimal 255 karakter.',
            'username.required'  => 'Username wajib diisi.',
            'username.min'       => 'Username minimal 3 karakter.',
            'username.max'       => 'Username maksimal 50 karakter.',
            'username.regex'     => 'Username hanya boleh berisi huruf, angka, titik, dan underscore.',
            'username.unique'    => 'Username sudah digunakan.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.max'          => 'Email maksimal 255 karakter.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'grup_id.required'   => 'Grup wajib dipilih.',
            'grup_id.exists'     => 'Grup tidak valid.',
            'foto.image'         => 'File harus berupa gambar.',
            'foto.mimes'         => 'Format gambar harus: jpg, jpeg, png, atau webp.',
            'foto.max'           => 'Ukuran foto maksimal 2MB.',
        ]);

        DB::beginTransaction();
        try {
            $payload = [
                'nama'      => $validated['nama'],
                'username'  => strtolower(trim($validated['username'])),
                'email'     => $validated['email'],
                'grup_id'   => $validated['grup_id'],
                'is_active' => $request->boolean('is_active', true),
            ];

            if (!empty($validated['password'])) {
                $payload['password'] = $validated['password'];
            }

            if ($request->hasFile('foto')) {
                // Delete old photo
                if ($user->foto && file_exists(public_path('upload/foto-user/' . $user->foto))) {
                    unlink(public_path('upload/foto-user/' . $user->foto));
                }
                $foto = $request->file('foto');
                $fotoName = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('upload/foto-user'), $fotoName);
                $payload['foto'] = $fotoName;
            }

            $user->update($payload);

            DB::commit();
            return redirect()->route('user-management.index')
                ->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui user.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        DB::beginTransaction();
        try {
            // Delete photo file
            if ($user->foto && file_exists(public_path('upload/foto-user/' . $user->foto))) {
                unlink(public_path('upload/foto-user/' . $user->foto));
            }
            $user->delete();
            DB::commit();
            return redirect()->route('user-management.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus user.');
        }
    }
}
