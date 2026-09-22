<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use App\Models\Grup;
use App\Models\Menu;
use App\Models\MenuAkses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GrupManagementController extends Controller
{
    public function index(): View
    {
        return view('grup-management.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['nama_grup', 'deskripsi', 'created_at'];
        $orderColumn     = $sortableColumns[max(0, $orderColIndex - 1)] ?? 'nama_grup';

        $totalRecords = Grup::count();

        $query = Grup::withCount('users');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_grup', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $grups = $query
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $no = $start;
        $user = auth()->user();
        $data = $grups->map(function ($grup) use (&$no, $user) {
            $no++;
            $encId = IdEncryptor::encrypt($grup->id);

            $hakAksesBtn = '';
            $editBtn = '';
            $deleteBtn = '';

            if ($user->hasMenuAccess('grup-management', 'edit')) {
                $hakAksesBtn = '<a href="' . route('grup-management.hak-akses', $encId) . '" class="btn btn-sm btn-outline-warning-600 d-inline-flex align-items-center gap-1"><i class="ri-shield-keyhole-line text-sm"></i> Hak Akses</a>';
                $editBtn = '<a href="' . route('grup-management.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($user->hasMenuAccess('grup-management', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($grup->nama_grup) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no'          => $no,
                'nama_grup'   => e($grup->nama_grup),
                'deskripsi'   => e($grup->deskripsi ?: '-'),
                'users_count' => $grup->users_count,
                'aksi'        => '<div class="d-inline-flex align-items-center gap-8">' . $hakAksesBtn . $editBtn . $deleteBtn . '</div>',
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
        return view('grup-management.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_grup' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_grup.required' => 'Nama grup wajib diisi.',
            'nama_grup.max'      => 'Nama grup maksimal 255 karakter.',
            'deskripsi.max'      => 'Deskripsi maksimal 255 karakter.',
        ]);

        DB::beginTransaction();
        try {
            Grup::create($validated);
            DB::commit();
            return redirect()->route('grup-management.index')
                ->with('success', 'Grup berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan grup.');
        }
    }

    public function edit(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $grup = Grup::findOrFail($id);
        return view('grup-management.edit', compact('grup'));
    }

    public function update(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $grup = Grup::findOrFail($id);

        $validated = $request->validate([
            'nama_grup' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:255'],
        ], [
            'nama_grup.required' => 'Nama grup wajib diisi.',
            'nama_grup.max'      => 'Nama grup maksimal 255 karakter.',
            'deskripsi.max'      => 'Deskripsi maksimal 255 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $grup->update($validated);
            DB::commit();
            return redirect()->route('grup-management.index')
                ->with('success', 'Grup berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui grup.');
        }
    }

    public function destroy(string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $grup = Grup::findOrFail($id);

        if ($grup->users()->count() > 0) {
            return redirect()->back()->with('error', 'Grup masih memiliki user. Pindahkan user terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            $grup->menuAkses()->delete();
            $grup->delete();
            DB::commit();
            Cache::forget("menu_access:grup:{$id}");
            Cache::forget("menu_akses_form:grup:{$id}");
            return redirect()->route('grup-management.index')
                ->with('success', 'Grup berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus grup.');
        }
    }

    // ==================== Hak Akses ====================

    public function hakAkses(string $encryptedId): View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $grup = Grup::findOrFail($id);

        // Get all menus organized by parent
        $parentMenus = Cache::remember('master:menus:parents', 86400, fn() =>
            Menu::where('parent', 0)->orderBy('urutan')->get()
        );
        $childMenus = Cache::remember('master:menus:children', 86400, fn() =>
            Menu::where('parent', '>', 0)->orderBy('urutan')->get()->groupBy('parent')
        );

        // Get existing access for this grup
        $existingAccess = Cache::remember("menu_akses_form:grup:{$grup->id}", 300, fn() =>
            MenuAkses::where('grup_id', $grup->id)->get()->keyBy('menu_id')
        );

        return view('grup-management.hak-akses', compact('grup', 'parentMenus', 'childMenus', 'existingAccess'));
    }

    public function hakAksesUpdate(Request $request, string $encryptedId): RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $grup = Grup::findOrFail($id);
        $aksesData = $request->input('akses', []);

        DB::beginTransaction();
        try {
            MenuAkses::where('grup_id', $grup->id)->delete();

            $parentAccess = [];

            foreach ($aksesData as $menuId => $actions) {
                $menuId = (int) $menuId;
                $menu = Menu::find($menuId);
                if (!$menu) {
                    continue;
                }

                $viewVal   = isset($actions['view']) ? 1 : 0;
                $addVal    = isset($actions['add']) ? 1 : 0;
                $editVal   = isset($actions['edit']) ? 1 : 0;
                $deleteVal = isset($actions['delete']) ? 1 : 0;

                if ($viewVal || $addVal || $editVal || $deleteVal) {
                    MenuAkses::create([
                        'menu_id' => $menuId,
                        'grup_id' => $grup->id,
                        'view'    => $viewVal,
                        'add'     => $addVal,
                        'edit'    => $editVal,
                        'delete'  => $deleteVal,
                    ]);

                    if ($menu->parent > 0) {
                        $parentId = $menu->parent;
                        if (!isset($parentAccess[$parentId])) {
                            $parentAccess[$parentId] = [
                                'view'   => 0,
                                'add'    => 0,
                                'edit'   => 0,
                                'delete' => 0,
                            ];
                        }
                        $parentAccess[$parentId]['view']   = $parentAccess[$parentId]['view']   || $viewVal;
                        $parentAccess[$parentId]['add']    = $parentAccess[$parentId]['add']    || $addVal;
                        $parentAccess[$parentId]['edit']   = $parentAccess[$parentId]['edit']   || $editVal;
                        $parentAccess[$parentId]['delete'] = $parentAccess[$parentId]['delete'] || $deleteVal;
                    }
                }
            }

            foreach ($parentAccess as $parentId => $access) {
                if ($access['view'] || $access['add'] || $access['edit'] || $access['delete']) {
                    MenuAkses::create([
                        'menu_id' => $parentId,
                        'grup_id' => $grup->id,
                        'view'    => $access['view'],
                        'add'     => $access['add'],
                        'edit'    => $access['edit'],
                        'delete'  => $access['delete'],
                    ]);
                }
            }

            DB::commit();
            Cache::forget("menu_access:grup:{$grup->id}");
            Cache::forget("menu_akses_form:grup:{$grup->id}");
            return redirect()->route('grup-management.index')
                ->with('success', 'Hak akses grup "' . e($grup->nama_grup) . '" berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui hak akses.');
        }
    }
}
