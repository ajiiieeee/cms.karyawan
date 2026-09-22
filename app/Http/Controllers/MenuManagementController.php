<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuManagementController extends Controller
{
    public function index(): View
    {
        return view('menu-management.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['nama_menu', 'link', 'urutan', 'created_at'];
        $orderColumn     = $sortableColumns[max(0, $orderColIndex - 1)] ?? 'urutan';

        $totalRecords = Menu::count();

        $query = Menu::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                  ->orWhere('link', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $menus = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $allMenus = Menu::where('parent', 0)->pluck('nama_menu', 'id');

        $no = $start;
        $user = auth()->user();
        $data = $menus->map(function ($menu) use (&$no, $allMenus, $user) {
            $no++;
            $parentName = $menu->parent > 0 ? ($allMenus[$menu->parent] ?? '-') : '<span class="text-neutral-400">—</span>';
            $iconHtml = $menu->icon ? '<i class="' . e($menu->icon) . ' text-lg"></i> ' : '';
            $encId = IdEncryptor::encrypt($menu->id);

            $editBtn = '';
            $deleteBtn = '';

            if ($user->hasMenuAccess('menu-management', 'edit')) {
                $editBtn = '<a href="' . route('menu-management.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($user->hasMenuAccess('menu-management', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($menu->nama_menu) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'         => $no,
                'nama_menu'  => $iconHtml . e($menu->nama_menu),
                'link'       => $menu->link ? '<code>' . e($menu->link) . '</code>' : '<span class="text-neutral-400">—</span>',
                'parent'     => $parentName,
                'urutan'     => $menu->urutan,
                'aksi'       => '<div class="d-inline-flex align-items-center gap-8">' . $editBtn . $deleteBtn . '</div>',
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
        $parents = Cache::remember('master:menus:parents', 86400, fn() =>
            Menu::where('parent', 0)->orderBy('urutan')->get()
        );
        return view('menu-management.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_menu' => ['required', 'string', 'max:300'],
            'link'      => ['nullable', 'string', 'max:300'],
            'icon'      => ['nullable', 'string', 'max:300'],
            'parent'    => ['required', 'integer', 'min:0'],
            'urutan'    => ['required', 'integer', 'min:0'],
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi.',
            'nama_menu.max'      => 'Nama menu maksimal 300 karakter.',
            'link.max'           => 'Link maksimal 300 karakter.',
            'icon.max'           => 'Icon maksimal 300 karakter.',
            'parent.required'    => 'Parent wajib dipilih.',
            'parent.integer'     => 'Parent harus berupa angka.',
            'urutan.required'    => 'Urutan wajib diisi.',
            'urutan.integer'     => 'Urutan harus berupa angka.',
            'urutan.min'         => 'Urutan minimal 0.',
        ]);

        DB::beginTransaction();
        try {
            Menu::create($validated);
            DB::commit();
            Cache::forget('master:menus:parents');
            Cache::forget('master:menus:children');
            return redirect()->route('menu-management.index')
                ->with('success', 'Menu berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan menu.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $menu = Menu::findOrFail($id);
        $allParents = Cache::remember('master:menus:parents', 86400, fn() =>
            Menu::where('parent', 0)->orderBy('urutan')->get()
        );
        $parents = $allParents->filter(fn($m) => $m->id !== $id)->values();
        return view('menu-management.edit', compact('menu', 'parents'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'nama_menu' => ['required', 'string', 'max:300'],
            'link'      => ['nullable', 'string', 'max:300'],
            'icon'      => ['nullable', 'string', 'max:300'],
            'parent'    => ['required', 'integer', 'min:0'],
            'urutan'    => ['required', 'integer', 'min:0'],
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi.',
            'nama_menu.max'      => 'Nama menu maksimal 300 karakter.',
            'link.max'           => 'Link maksimal 300 karakter.',
            'icon.max'           => 'Icon maksimal 300 karakter.',
            'parent.required'    => 'Parent wajib dipilih.',
            'parent.integer'     => 'Parent harus berupa angka.',
            'urutan.required'    => 'Urutan wajib diisi.',
            'urutan.integer'     => 'Urutan harus berupa angka.',
            'urutan.min'         => 'Urutan minimal 0.',
        ]);

        DB::beginTransaction();
        try {
            $menu->update($validated);
            DB::commit();
            Cache::forget('master:menus:parents');
            Cache::forget('master:menus:children');
            return redirect()->route('menu-management.index')
                ->with('success', 'Menu berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui menu.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $menu = Menu::findOrFail($id);

        // Prevent deleting parent menus that have children
        if (Menu::where('parent', $menu->id)->exists()) {
            return redirect()->back()->with('error', 'Menu ini memiliki sub-menu. Hapus sub-menu terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            $menu->menuAkses()->delete();
            $menu->delete();
            DB::commit();
            Cache::forget('master:menus:parents');
            Cache::forget('master:menus:children');
            return redirect()->route('menu-management.index')
                ->with('success', 'Menu berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus menu.');
        }
    }
}
