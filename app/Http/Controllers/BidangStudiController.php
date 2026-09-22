<?php

namespace App\Http\Controllers;

use App\Helpers\IdEncryptor;
use Illuminate\Http\Request;
use App\Models\BidangStudi;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class BidangStudiController extends Controller
{
    public function index()
    {
        return view('bidang_studi.index');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = ['created_at', 'created_at', 'created_at', 'nama_bidang_studi'];
        $orderColumn     = $sortableColumns[$orderColIndex] ?? 'created_at';

        $totalRecords = BidangStudi::count();

        $query = BidangStudi::query();

        if($search !== '') {
            $query->where('nama_bidang_studi', 'like', "%{$search}%");
        }

        $filteredRecords = $query->count();

        $bidangStudis = $query->orderBy($orderColumn, $orderDir)
                            ->skip($start)
                            ->take($length)
                            ->get();
        
        $no = $start;
        $currentUser = auth()->user();

        $data = $bidangStudis->map(function($item) use (&$no, $currentUser) {
            $no++;
            $encId = IdEncryptor::encrypt($item->id);

            $editBtn = '';
            $deleteBtn = '';

            if ($currentUser->hasMenuAccess('bidang-studi', 'edit')) {
                $editBtn = '<a href="' . route('bidang-studi.edit', $encId) . '" class="btn btn-sm btn-outline-primary-600 d-inline-flex align-items-center gap-1"><i class="ri-edit-line text-sm"></i> Edit</a>';
            }

            if ($currentUser->hasMenuAccess('bidang-studi', 'delete')) {
                $deleteBtn = '<button type="button" class="btn btn-sm btn-outline-danger-600 d-inline-flex align-items-center gap-1 btn-delete" data-id="' . $encId . '" data-name="' . e($item->nama_bidang_studi) . '"><i class="ri-delete-bin-6-line text-sm"></i> Hapus</button>';
            }

            return [
                'no' => $no,
                'nama_bidang_studi' => $item->nama_bidang_studi,
                'actions' => '<div class="d-flex flex-wrap align-items-center gap-2">' . $editBtn . $deleteBtn . '</div>',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data->values(),
        ]);
    }

    public function create() : View
    {
        $bidangStudis = BidangStudi::all();

        return view('bidang_studi.create', compact('bidangStudis'));
    }

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate([
            'nama_bidang_studi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
        ], $this->validationMessages());


        DB::beginTransaction();

        try {
            BidangStudi::create($validated);

            DB::commit();
            Cache::forget('master:bidang_studi:by_name');
            Cache::forget('master:bidang_studi:by_id');

            return redirect()->route('bidang-studi.index')->with('success', 'Bidang studi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data bidang studi: ' . $e->getMessage());
        }
    }

    public function edit(string $encryptedId) : View
    {
        $id = IdEncryptor::decrypt($encryptedId);
        $bidangStudi = BidangStudi::findOrFail($id);

        return view('bidang_studi.edit', compact('bidangStudi'));
    }

    public function update(Request $request, string $encryptedId) : RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        $bidangStudi = BidangStudi::findOrFail($id);

        $validated = $request->validate([
            'nama_bidang_studi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
        ], $this->validationMessages());

        DB::beginTransaction();

        try {
            $bidangStudi->update($validated);

            DB::commit();
            Cache::forget('master:bidang_studi:by_name');
            Cache::forget('master:bidang_studi:by_id');

            return redirect()->route('bidang-studi.index')->with('success', 'Bidang studi berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data bidang studi: ' . $e->getMessage());
        }
    }

    public function destroy(string $encryptedId) : RedirectResponse
    {
        $id = IdEncryptor::decrypt($encryptedId);
        if (!$id) {
            abort(404);
        }

        $bidangStudi = BidangStudi::findOrFail($id);

        DB::beginTransaction();
        try {
            $bidangStudi->delete();
            DB::commit();
            Cache::forget('master:bidang_studi:by_name');
            Cache::forget('master:bidang_studi:by_id');
            return redirect()->route('bidang-studi.index')
                ->with('success', 'Bidang studi berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus bidang studi.');
        }
    }

    private function validationMessages() : array
    {
        return [
            'nama_bidang_studi.required' => 'Nama bidang studi wajib diisi.',
            'nama_bidang_studi.string' => 'Nama bidang studi harus berupa teks.',
            'nama_bidang_studi.max' => 'Nama bidang studi tidak boleh lebih dari 255 karakter.',
        ];
    }
}
