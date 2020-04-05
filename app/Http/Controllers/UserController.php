<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::orderBy('created_at', 'DESC')->get();
        return view('welcome', compact('users'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'avatar' => 'required|image|mimes:jpg,jpeg,png'
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = $request->email . '-' . time() . '.' . $file->getClientOriginalExtension(); 
            Storage::disk('s3')->put('images/' . $filename, file_get_contents($file));

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'avatar' => $filename,
                'password' => bcrypt($request->password)
            ]);
            return redirect()->back()->with(['success' => 'Data Berhasil Disimpan']);
        }
        return redirect()->back()->with(['error' => 'Gambar Belum Dipilih']);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        Storage::disk('s3')->delete('images/' . $user->avatar);
        $user->delete();
        return redirect()->back()->with(['success' => 'Data Berhasil Dihapus']);
    }
}
