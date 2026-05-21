<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * List staff with search + sorting (no pagination).
     */
    public function index(Request $request)
    {
        $allowed = ['name', 'position', 'rating', 'created_at', 'updated_at'];

        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir',  'asc');
        if (!in_array($sort, $allowed, true))  $sort = 'name';
        if (!in_array(strtolower($dir), ['asc','desc'], true)) $dir = 'asc';

        $q = trim((string) $request->get('q', ''));

        $query = Staff::with('user');

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('position', 'like', "%{$q}%")
                   ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$q}%"));
            });
        }

        $staff = $query->orderBy($sort, $dir)->get();

        return view('staff.index', [
            'staff' => $staff,
            'sort'  => $sort,
            'dir'   => $dir,
            'q'     => $q,
        ]);
    }

    /** --------- CRUD keeps working below --------- */

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        // allow linking to existing user by email
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email'],
            'position' => ['nullable', 'string'],
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'password' => ['nullable', 'string', 'min:6'],  // optional; default to 123456
        ]);

        $password = $data['password'] ?? '123456';

        $user = User::where('email', $data['email'])->first();
        $createdNewUser = false;

        if (!$user) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($password),
                'role'     => 'staff',
            ]);
            $createdNewUser = true;
        } else {
            $user->name = $data['name'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->role = 'staff';
            $user->save();
        }

        Staff::create([
            'name'     => $data['name'],
            'position' => $data['position'] ?? null,
            'rating'   => $data['rating'],
            'user_id'  => $user->id,
        ]);

        $msg = $createdNewUser && empty($data['password'])
            ? 'Staff added (default password: 123456).'
            : 'Staff added.';

        return redirect()->route('staff.index')->with('success', $msg);
    }

    public function edit(Staff $staff)
    {
        $staff->load('user');
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $staff->load('user');

        $incomingEmail   = (string) $request->input('email', '');
        $existingByEmail = $incomingEmail
            ? User::where('email', $incomingEmail)->first()
            : null;

        $ignoreId = optional($staff->user)->id ?? optional($existingByEmail)->id;

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users','email')->ignore($ignoreId)],
            'position' => ['nullable', 'string'],
            'rating'   => ['required', 'integer', 'min:1', 'max:5'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $staff->update([
            'name'     => $data['name'],
            'position' => $data['position'] ?? null,
            'rating'   => $data['rating'],
        ]);

        $user = $staff->user ?: $existingByEmail;

        if (!$user) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password'] ?? '123456'),
                'role'     => 'staff',
            ]);
            $staff->user_id = $user->id;
            $staff->save();
        } else {
            $user->name  = $data['name'];
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->role = 'staff';
            $user->save();

            if (!$staff->user_id) {
                $staff->user_id = $user->id;
                $staff->save();
            }
        }

        return redirect()->route('staff.index')->with('success', 'Staff updated.');
    }

    public function destroy(Staff $staff)
    {
        // Optionally also delete linked user
        // if ($staff->user) { $staff->user->delete(); }

        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'Staff deleted!');
    }
}