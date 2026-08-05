<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Auth::user()->groups()->with('members')->withCount('devices')->orderBy('name')->get();

        return view('groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:60'],
        ]);

        $group = Group::create([
            'name' => $data['name'],
            'code' => Group::generateCode(),
            'owner_id' => Auth::id(),
        ]);

        $group->members()->attach(Auth::id());

        return back()->with('status', "Familia creada. Código para compartir: {$group->code}");
    }

    public function join(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $group = Group::whereRaw('UPPER(code) = ?', [strtoupper($data['code'])])->first();

        if (! $group) {
            return back()->withErrors(['code' => 'No existe una familia con ese código.']);
        }

        if ($group->members()->where('users.id', Auth::id())->exists()) {
            return back()->with('status', 'Ya perteneces a esta familia.');
        }

        $group->members()->attach(Auth::id());

        return back()->with('status', "Te uniste a la familia {$group->name}.");
    }

    public function leave(Group $group)
    {
        abort_unless($group->members()->where('users.id', Auth::id())->exists(), 403);
        $group->members()->detach(Auth::id());

        return back()->with('status', 'Saliste de la familia.');
    }
}
