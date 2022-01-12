<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * login
     *
     * @param  mixed $request
     * @return void
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $user = User::where('username', $request->username)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Login Gagal!',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil!',
            'data'    => $user,
            'token'   => $user->createToken('authToken')->accessToken
        ]);
    }

    public function ubahPassword(Request $request)
    {
        $datauser = $this->validate($request, [
            'password_lama' => 'required',
            'password_baru' => 'required',
        ]);

        $user = Auth::user();

        if (Hash::check($datauser['password_lama'], $user->password)) {
            $datauser['password_baru'] = bcrypt($datauser['password_baru']);
            $user->update(['password' => $datauser['password_baru']]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil di ubah',
            ]);
        }
        else if ( !Hash::check($datauser['password_lama'], $user->password)) {
            return response()->json([
                'message' => 'Password Salah',
            ]);
        }
        else {
            return response()->json([
                'message' => 'Password Salah',
            ]);
        }
    }


    /**
     * logout
     *
     * @param  mixed $request
     * @return void
     */
    public function logout(Request $request)
    {
        $removeToken = $request->user()->tokens()->delete();

        if ($removeToken) {
            return response()->json([
                'success' => true,
                'message' => 'Logout Success!',
            ]);
        }
    }
}
