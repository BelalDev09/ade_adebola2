<?php

namespace App\Http\Controllers\WEB\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    // Show page
    public function index()
    {
        return view('backend.admin.user.index');
    }

    // DataTable AJAX
public function data()
{
    $users = User::select(['id','name','first_name','last_name','email','avatar','created_at']);
    dd($users->toArray());

    return DataTables::of($users)
    ->addIndexColumn()
    ->addColumn('name', fn($row) => $row->full_name ?: $row->name)
    ->addColumn('action', function($row){
        $edit = '<button data-bs-toggle="modal" data-bs-target="#userModal"
        data-action="edit"
        data-id="'.$row->id.'"
        data-first_name="'.$row->first_name.'"
        data-last_name="'.$row->last_name.'"
        data-email="'.$row->email.'"
        data-avatar="'.($row->avatar ? asset("storage/".$row->avatar) : '').'"
        class="btn btn-sm btn-warning me-1">Edit</button>';
        $del = '<button data-id="'.$row->id.'" class="btn btn-sm btn-danger delete-user">Delete</button>';
        return $edit.$del;
            })
            ->rawColumns(['action'])
            ->make(true);
            }


    // Store
    public function store(Request $request){
        $request->validate([
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:6',
            'avatar'=>'nullable|image|max:2048'
        ]);
        $data = $request->only('first_name','last_name','email');
        $data['password'] = Hash::make($request->password);
        if($request->hasFile('avatar')) $data['avatar'] = $request->file('avatar')->store('avatars','public');
        User::create($data);
        return response()->json(['success'=>'User added successfully']);
    }

    // Update
    public function update(Request $request, User $user){
        $request->validate([
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email,'.$user->id,
            'avatar'=>'nullable|image|max:2048'
        ]);
        $data = $request->only('first_name','last_name','email');
        if($request->hasFile('avatar')){
            if($user->avatar && Storage::disk('public')->exists($user->avatar)) 
                Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars','public');
        }
        $user->update($data);
        return response()->json(['success'=>'User updated successfully']);
    }

    // Delete
    public function destroy(User $user){
        if($user->avatar && Storage::disk('public')->exists($user->avatar)) 
            Storage::disk('public')->delete($user->avatar);
        $user->delete();
        return response()->json(['success'=>'User deleted successfully']);
    }
}
