<?php

namespace App\Http\Controllers\WEB\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function index()
    {
        return view('backend.admin.dynamic.support.index');
    }

    public function data()
    {
        $users = User::select(['id','first_name','last_name','description','created_at']);

        return DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('user', fn($row) => trim($row->first_name.' '.$row->last_name))
            ->addColumn('action', function($row){
                $edit = '<a href="'.route("admin.support.edit", $row->id).'" class="btn btn-sm btn-warning">Edit</a>';
                $del = '<form action="'.route("admin.support.destroy", $row->id).'" method="POST" style="display:inline;">
                            '.csrf_field().method_field("DELETE").'
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
                        </form>';
                return $edit.' '.$del;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('backend.admin.dynamic.support.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description'=>'required|string',
        ]);

        $user = Auth::user();
        $user->update(['description'=>$request->description]);

        return redirect()->route('admin.support.index')->with('success','Description added successfully');
    }

    public function edit(User $user)
    {
        return view('backend.admin.dynamic.support.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'description'=>'required|string',
        ]);

        $user->update(['description'=>$request->description]);

        return redirect()->route('admin.support.index')->with('success','Description updated successfully');
    }

    public function destroy(User $user)
    {
        $user->update(['description'=>null]);
        return redirect()->route('admin.support.index')->with('success','Description deleted successfully');
    }
}
