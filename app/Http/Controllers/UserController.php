<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\Divisi;
use App\Models\Position;
use App\Models\Director;
use App\Models\Department;
use App\Models\Section;
use App\Models\Unit;
use App\Models\Memo;
use App\Models\Undangan;
use App\Models\Risalah;
use App\Models\BagianKerja;

class UserController extends Controller
{
    public function showRole()
    {
        $role = Role::all();
        return view('user.role', compact('role'));
    }

    // ─── show() — halaman detail ──────────────────────────────────────────────
    public function show($id)
    {
        $user = User::withTrashed()->with([
            'role', 'position', 'divisi',
            'department', 'section', 'unit', 'director',
        ])->findOrFail($id);

        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        return view('superadmin.user-manage.show', compact('user', 'bagianKerja'));
    }

    // ─── edit() — halaman form edit ───────────────────────────────────────────
    public function edit($id)
    {
        // Load user + SEMUA relasi organisasi agar blade bisa membaca FK-nya
        $user = User::withTrashed()->with([
            'role', 'position',
            'unit', 'section', 'department', 'divisi', 'director',
        ])->findOrFail($id);

        $positions   = Position::all();
        $bagianKerja = BagianKerja::orderBy('kode_bagian')->get();

        $mainDirector = Director::with([
            'subDirectors.divisi.department.section.unit',
            'subDirectors.divisi.department.unit',
            'subDirectors.department.section.unit',
            'subDirectors.department.unit',
            'divisi.department.section.unit',
            'divisi.department.unit',
            'department.section.unit',
            'department.unit',
        ])->where('is_main', 1)->first();

        // Build orgOptions langsung di sini tanpa ReflectionMethod
        $orgOptions = $mainDirector ? $this->buildOrgOptions($mainDirector) : [];

        return view('superadmin.user-manage.edit',
            compact('user', 'positions', 'bagianKerja', 'orgOptions'));
    }

    // ─── update() ─────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $request->validate([
            'firstname'            => 'nullable|string|max:50',
            'lastname'             => 'nullable|string|max:50',
            'nip'                  => 'nullable|string|max:25',
            'email'                => 'nullable|string|email|max:70|unique:users,email,' . $id,
            'password'             => 'nullable|min:8|confirmed',
            'phone_number'         => 'nullable',
            'role_id_role'         => 'nullable',
            'position_id_position' => 'nullable|exists:position,id_position',
            'parent_id'            => 'nullable',
            'parent_type'          => 'nullable',
            'kode_bagian'          => 'nullable|array',
            'kode_bagian.*'        => 'nullable|string',
        ]);

        // Sinkronisasi nama di dokumen jika nama berubah
        $newFullname = trim($request->firstname . ' ' . $request->lastname);
        $oldFullname = trim($user->firstname . ' ' . $user->lastname);

        if ($newFullname !== $oldFullname) {
            Memo::where('nama_bertandatangan', $oldFullname)
                ->update(['nama_bertandatangan' => $newFullname]);

            Undangan::where('nama_bertandatangan', $oldFullname)
                ->update(['nama_bertandatangan' => $newFullname]);

            Risalah::where('nama_pemimpin_acara', $oldFullname)
                ->update(['nama_pemimpin_acara' => $newFullname]);

            Risalah::where('nama_notulis_acara', $oldFullname)
                ->update(['nama_notulis_acara' => $newFullname]);
        }

        // Update field dasar
        foreach (['firstname', 'lastname', 'nip', 'email', 'phone_number'] as $field) {
            if ($request->filled($field)) {
                $user->{$field} = $request->{$field};
            }
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->filled('position_id_position')) {
            $user->position_id_position = $request->position_id_position;
        }

        if ($request->filled('role_id_role')) {
            $user->role_id_role = $request->role_id_role;
        }

        // Kode Bagian — filter empty value dari hidden fallback
        if ($request->has('kode_bagian')) {
            $kodeBagian = array_filter(
                $request->kode_bagian,
                fn($v) => !empty($v)
            );
            $user->kode_bagian = !empty($kodeBagian) ? implode(';', $kodeBagian) : null;
        }

        // Organisasi — set FK sesuai level yang dipilih
        $parentId   = $request->parent_id;
        $parentType = $request->parent_type;

        if ($parentId && $parentType) {
            // Reset semua FK dulu, lalu isi yang relevan
            $user->director_id_director    = null;
            $user->divisi_id_divisi        = null;
            $user->department_id_department = null;
            $user->section_id_section      = null;
            $user->unit_id_unit            = null;

            switch ($parentType) {
                case 'director':
                    $user->director_id_director = $parentId;
                    break;

                case 'divisi':
                    $div = Divisi::find($parentId);
                    $user->divisi_id_divisi     = $parentId;
                    $user->director_id_director = $div?->director_id_director;
                    break;

                case 'department':
                    $dept = Department::find($parentId);
                    $user->department_id_department = $parentId;
                    $user->divisi_id_divisi         = $dept?->divisi_id_divisi;
                    $user->director_id_director     = $dept?->director_id_director;
                    break;

                case 'section':
                    $sec  = Section::find($parentId);
                    $dept = Department::find($sec?->department_id_department);
                    $user->section_id_section       = $parentId;
                    $user->department_id_department = $sec?->department_id_department;
                    $user->divisi_id_divisi         = $dept?->divisi_id_divisi;
                    $user->director_id_director     = $dept?->director_id_director;
                    break;

                case 'unit':
                    $unit = Unit::find($parentId);
                    // Unit bisa langsung di bawah department ATAU section
                    $deptId = $unit?->department_id_department
                        ?? Section::find($unit?->section_id_section)?->department_id_department;
                    $dept = Department::find($deptId);

                    $user->unit_id_unit             = $parentId;
                    $user->section_id_section       = $unit?->section_id_section;
                    $user->department_id_department = $deptId;
                    $user->divisi_id_divisi         = $dept?->divisi_id_divisi;
                    $user->director_id_director     = $dept?->director_id_director;
                    break;
            }
        }

        $user->save();

        return redirect()->route('user.manage')
            ->with('success', 'Data user berhasil diperbarui');
    }

    // ─── destroy() ────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['success' => 'User berhasil dinonaktifkan'], 200);
    }

    // ─── restore() ────────────────────────────────────────────────────────────
    public function restore($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->restore();

        return response()->json(['success' => 'User berhasil diaktifkan'], 200);
    }

    // ─── buildOrgOptions() ────────────────────────────────────────────────────
    /**
     * Build flat array of org options dari tree Director.
     * Dipakai oleh edit() untuk mengisi dropdown organisasi.
     */
    private function buildOrgOptions($node, &$result = [], int $level = 0, &$seen = [])
    {
        $value = null;
        $type  = null;
        $label = null;

        if (isset($node->name_director)) {
            $value = $node->id_director;
            $type  = 'director';
            $label = "Direktur: {$node->name_director}";
        } elseif (isset($node->nm_divisi)) {
            $value = $node->id_divisi;
            $type  = 'divisi';
            $label = "Divisi: {$node->nm_divisi}";
        } elseif (isset($node->name_department)) {
            $value = $node->id_department;
            $type  = 'department';
            $label = "Departemen: {$node->name_department}";
        } elseif (isset($node->name_section)) {
            $value = $node->id_section;
            $type  = 'section';
            $label = "Bagian: {$node->name_section}";
        } elseif (isset($node->name_unit)) {
            $value = $node->id_unit;
            $type  = 'unit';
            $label = "Unit: {$node->name_unit}";
        }

        if ($value !== null && $type !== null) {
            $key = "{$type}:{$value}";
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = [
                    'value' => $value,
                    'type'  => $type,
                    'label' => str_repeat('— ', $level) . $label,
                ];
            }
        }

        foreach (['subDirectors', 'divisi', 'department', 'section', 'unit'] as $rel) {
            if (!empty($node->{$rel})) {
                foreach ($node->{$rel} as $child) {
                    $this->buildOrgOptions($child, $result, $level + 1, $seen);
                }
            }
        }

        return $result;
    }
}
