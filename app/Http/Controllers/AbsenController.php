<?php

namespace Carbon;
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Absen;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;


class AbsenController extends Controller
{
    public function detail($id) {
        $record = Absen::find($id);
        $name = User::find($record->user_id)->name;
        $username = User::find($record->user_id)->username;
        if (!empty($record->id)) {
            return response()->json([
                'success'   => true,
                'message'   => 'OK',
                'data'      => $record,
                'name'      => $name,
                'username'  => $username,              
            ]);
        }
        else {
            return response()->json(['ID Tidak Tersedia'], 400);
        }
    }
    
    
    
    public function  statusabsenmasuk(){
        $user_id = Auth::id();
        $hariini = Carbon::now()->toDateString();
        $kemarin = Carbon::yesterday()->toDateString();
        $absenmasuk =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->where('present','=',$hariini)->whereNull('finish')->orderBy('starting','desc')->first();
        $absenlengkap =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->whereNotNull('present')->whereNotNull('finish')->orderBy('starting','desc')->first();
        $absenselesai =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->whereNotNull('starting')->whereNotNull('finish')->where('present','=',$hariini)->first();
        $absenterakhir =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->whereNotNull('starting')->whereNotNull('finish')->orderBy('starting','desc')->first();
        $absenlemburhariini =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->whereNotNull('starting')->whereNull('finish')->where('present','=',$hariini)->where('shift','=','Shift 2')->first();
        $absenlemburharikemarin =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->whereNotNull('starting')->where('present','=',$kemarin)->whereNull('finish')->where('shift','=','Shift 2')->first();
        $absenlemburselesai =  DB::table('absen')->select('id')->where('user_id','=',$user_id)->whereNotNull('starting')->where('present','=',$kemarin)->whereNotNull('finish')->where('shift','=','Shift 2')->first();
        
        if (!empty($absenmasuk)) {
            return response()->json([
                'status'   => 'sudah absen masuk',
                'lastvalue' => $absenmasuk,
                'tanggal' => $hariini,
            ]);
        
        }
        elseif (!empty($absenlemburhariini)) {
            return response()->json([
                'status'   => 'lembur belum selesai',
                'lastvalue' => $absenlemburhariini,
                'tanggal' => $hariini,
            ]);
        }
        elseif (!empty($absenlemburharikemarin)) {
            return response()->json([
                'status'   => 'lembur selesai',
                'lastvalue' => $absenlemburharikemarin,
                'tanggal' => $hariini,
            ]);
        }
        elseif (!empty($absenselesai) || !empty($absenlemburselesai)) {
            return response()->json([
                'status'   => 'sudah absen selesai',
                'lastvalue' => $absenterakhir,
                'tanggal' => $hariini,
            ]);
        }
        elseif (!empty($absenlengkap)) {
            return response()->json([
                'status'   => 'belum absen masuk',
                'lastvalue' => $absenlengkap,
                'tanggal' => $hariini,
            ]);
        }
        elseif (empty($absenlengkap) && empty($absenmasuk)) {
            return response()->json([
                'status'   => 'belum ada data',
                'lastvalue' => null,
                'tanggal' => $hariini,
            ]);
        }
        else{
            return response()->json([
                'status'   => 'gagal',
            ]);
        }
    }

    //index absen table
    public function index() {
        $id = Auth::id(); 
        $records =  DB::table('absen')
                    ->select('id', 'present','starting','finish','manhour','status','shift')
                    ->where('user_id','=',$id)->get();
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'data'      => $records,
        ]);
    }

    public function laporanuser() {
        $records =  DB::table('users')
                    ->select('users.name', 'users.id')
                    ->where('role','=','user')->get();
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'data'      => $records,
        ]);
    }

    public function detaillaporanuser($id) {
        $records =  DB::table('absen')
                    ->select('absen.id', 'absen.user_id', 'absen.present','absen.starting','absen.finish','absen.manhour','absen.shift','absen.status')
                    ->where('absen.user_id','=',$id)->get();
        $photo =  DB::table('users')
                    ->select('upload_file')
                    ->where('id','=',$id)->get();
        $name =  DB::table('users')
                    ->select('name')
                    ->where('id','=',$id)->get();
        $email =  DB::table('users')
                    ->select('email')
                    ->where('id','=',$id)->get();
                    
                    $absenterlambat = Absen::select('id', 'created_at')
                    ->where('status','=','Terlambat')
                    ->where('user_id','=',$id)
                    ->get()
                    ->groupBy(function($date) {
                        return Carbon::parse($date->created_at)->format('m');
                    });
                
                 $absentepatwaktu = Absen::select('id', 'created_at')
                    ->where('status','=','Tepat Waktu')
                    ->get()
                    ->groupBy(function($date) {
                        return Carbon::parse($date->created_at)->format('m');
                    });
        
                    $absenterlambatmcount = [];
                    $absentepatwaktumcount = [];
                    $absenterlambatArr = [];
                    $absentepatwaktuArr = [];
        
                    foreach ($absenterlambat as $key => $value) {
                        $absenterlambatmcount[(int)$key] = count($value);
                    }
        
                    foreach ($absentepatwaktu as $key => $value) {
                        $absentepatwaktumcount[(int)$key] = count($value);
                    }
        
                    for($i = 1; $i <= 12; $i++){
                        if(!empty($absenterlambatmcount[$i])){
                            $absenterlambatArr[$i] = $absenterlambatmcount[$i];    
                        }else{
                            $absenterlambatArr[$i] = 0;    
                        }
                    }
        
                    for($i = 1; $i <= 12; $i++){
                        if(!empty($absentepatwaktumcount[$i])){
                            $absentepatwaktuArr[$i] = $absentepatwaktumcount[$i];    
                        }else{
                            $absentepatwaktuArr[$i] = 0;    
                        }
                    }       
                          
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'data'      => $records,
            'photo'     => $photo,
            'name'      => $name,
            'email'      => $email,
            'absenterlambat' => $absenterlambatArr,
            'absentepatwaktu' => $absentepatwaktuArr,
        ]);
    }

    public function indexadmin() {
        $hariini = Carbon::now()->toDateString();
        $record = DB::table('users')
                  ->join('absen', 'users.id', '=', 'absen.user_id')
                  ->select('users.id','users.name','absen.shift','absen.id', 'absen.present','absen.starting','absen.finish','absen.manhour','absen.starting_lat','absen.starting_lng','absen.finish_lat','absen.finish_lng','absen.status');
        $records = $record->where('present','=',$hariini)->get();
        $hitungabsen = $records->whereNotNull('finish')->count();
        $hitungabsenmasuk = $records->whereNull('finish')->count();
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'data'      => $records,
            'jumlahabsen' => $hitungabsen,
            'jumlahabsenmasuk' => $hitungabsenmasuk
        ]);
    }

    public function carbon() {
        $user_id = Auth::id();
         
        $lat = '-2.565480546477532';
        $lng = '140.6963049972951';
        $response = Http::get('https://simpeg.tvri.go.id/presensi/ajax/address/geolocationaddress', [
            'lat' => $lat,
            'lng' => $lng
        ]);
        $responseBody = json_decode($response->body());
        $newdat = data_get($responseBody, 'newdate');
        $record = strtotime($newdat);
        $absenlembur =  DB::table('absen')->select('id')->where('user_id','=','11')->whereNotNull('starting')->whereNull('finish')->where('shift','=','Shift 2')->get();
        return response()->json([
             'status'   => 'tes koneksi',
        ]);
        
        
    }

    public function jam() {
        $lat = 0;
        $lng = 0;
        $response = Http::get('https://simpeg.tvri.go.id/presensi/ajax/address/geolocationaddress?lat=${lat}&lng=${lng}');
        $responseBody = json_decode($response->body());
        $newdat = data_get($responseBody, 'newdate');
        $newdate = substr($newdat, 0, 10);
        return response()->json([
            'success'   => true,
            'data'      => $newdate,
        ]);
    }
    
    

    // public function punchin(Request $request) {
    //         $lastValue = DB::table('absen')->select('id')->orderBy('id', 'desc')->first();
    //         if ($lastValue == null){
    //             $records = Absen::create([
    //                 'id' => 1,
    //                 'user_id' => $request->user_id,
    //                 'shift' => $request->shift,
    //                 'present' => $request->present,
    //                 'starting_photo' => $request->starting_photo,
    //                 'starting' => $request->starting,
    //                 'starting_lat' => $request->starting_lat,
    //                 'starting_lng' => $request->starting_lng
    //              ]);
    //         }
            
    //         $records = Absen::create([
    //        'id' => $lastValue->id + 1,
    //        'user_id' => $request->user_id,
    //        'shift' => $request->shift,
    //        'present' => $request->present,
    //        'starting_photo' => $request->starting_photo,
    //        'starting' => $request->starting,
    //        'starting_lat' => $request->starting_lat,
    //        'starting_lng' => $request->starting_lng
    //     ]);

    //     return response()->json([
    //         'success'   => true,
    //         'message'   => 'Penambahan Berhasil',
    //         'data'      => $records
    //     ]);
    // }


    public function punchin(Request $request) {
        $user_id = Auth::id();
        $lastValue = DB::table('absen')->select('id')->orderBy('id', 'desc')->first();
       
        $lat = $request->starting_lat;
        $lng = $request->starting_lng;
        $response = Http::get('https://simpeg.tvri.go.id/presensi/ajax/address/geolocationaddress', [
            'lat' => $lat,
            'lng' => $lng
        ]);
        $responseBody = json_decode($response->body());
        $timestamp = data_get($responseBody, 'newdate');
        $tanggal = substr($timestamp, 0, 10);
        $jam = strtotime($timestamp);

        if($request->shift == 'Normal' ) {
            if($jam <= strtotime('08:15:00') && $jam >= strtotime('07:30:00')){
                $status = 'Tepat Waktu';
            }else{ 
                $status = 'Terlambat';
            }    
        }
        elseif($request->shift == 'Shift 1' ) {
            if($jam < strtotime('10:15:00') && $jam >= strtotime('09:30:00')){
                $status = 'Tepat Waktu';
            }else{ 
                $status = 'Terlambat';
            }    
        }
        elseif($request->shift == 'Shift 2' ) {
            if($jam < strtotime('18:15:00') && $jam >= strtotime('17:30:00')){
                $status = 'Tepat Waktu';
            }else{ 
                $status = 'Terlambat';
            }    
        }
        elseif($request->shift == 'Shift 3' ) {
            if($jam < strtotime('02:15:00') && $jam >= strtotime('01:30:00')){
                $status = 'Tepat Waktu';
            }else{ 
                $status = 'Terlambat';
            }    
        }
        
       
        

        if ($lastValue == null){
            $records = Absen::create([
                'id' => 1,
                'user_id' => $user_id,
                'shift' => $request->shift,
                'present' => $tanggal,
                'starting_photo' => $request->starting_photo,
                'starting' => $timestamp,
                'starting_lat' => $request->starting_lat,
                'starting_lng' => $request->starting_lng,
                'status'    => $status
             ]);
        }
        
        $records = Absen::create([
       'id' => $lastValue->id + 1,
       'user_id' => $user_id,
       'shift' => $request->shift,
       'present' => $tanggal,
       'starting_photo' => $request->starting_photo,
       'starting' => $timestamp,
       'starting_lat' => $request->starting_lat,
       'starting_lng' => $request->starting_lng,
       'status'    => $status
    ]);

    return response()->json([
        'success'   => true,
        'message'   => 'Penambahan Berhasil',
        'data'      => $records
    ]);
}





    public function punchout(Request $request, $id) {
        $user_id = Auth::id();
        $masuk = DB::table('absen')->select('present')->where('user_id','=',$user_id)->orderBy('present', 'desc')->first();
        $record = Absen::find($id);
        $jammasuk = $record->starting;
        $jamkeluar = $record->finish;
        $jammasukk = Carbon::parse($jammasuk);
        $jamkeluarr = Carbon::parse($jamkeluar);
        $lat = $request->finish_lat;
        $lng = $request->finish_lng;
        $response = Http::get('https://simpeg.tvri.go.id/presensi/ajax/address/geolocationaddress', [
            'lat' => $lat,
            'lng' => $lng
        ]);
        $responseBody = json_decode($response->body());
        $newdate = data_get($responseBody, 'newdate');

        if (!empty($record->id) && !empty($masuk)) {
            $record->finish = $newdate;
            $record->finish_lat = $request->finish_lat;
            $record->finish_lng = $request->finish_lng;
            $record->finish_photo = $request->finish_photo;
            $record->manhour = ($jamkeluarr->diffInHours($jammasukk, true));



            $record->save();

            return response()->json([
                'success'   => true,
                'message'   => 'Update Berhasil',
                'data'      => $record,
            ]);
        }
    }

    public function delete($id) {
        $record = Absen::findOrFail($id) ;
        $record->delete() ;

        return response()->json([
            'success'   => true,
            'message'   => 'Data terhapus',
            'data'      => $record,
        ]);
    }

    public function name($id)
    {
        $user = User::findOrFail($id);
        return $user;
    }

    // public function getalamat()
    // {
    //     $response = Http::get('https://us1.locationiq.com/v1/reverse.php', [
    //         'key' => 'pk.ceed539868e5fc38968a5731fc051007',
    //         'lat' => -7.090910999999999,
    //         'lon' => 107.668887,
    //         'for' => 'json',
    //     ]);
    //     $responseBody = json_decode($response->body());
    //     return response()->json([
    //         'success'   => true,
    //         'data'      => $responseBody,
    //     ]);
        
    // }

    public function getalamat($lat, $lng)
    {
        $response = Http::get('https://us1.locationiq.com/v1/reverse.php',[
            'key' => 'pk.ceed539868e5fc38968a5731fc051007',
            'lat' => $lat,
            'lon' => $lng,
            'format' => 'json',
        ]);
        $responseBody = json_decode($response->body());
        return response()->json([
            'success'   => true,
            'data'      => $responseBody,
        ]);
        
    }

    public function getalamatkeluar($lat, $lng)
    {
        $response = Http::get('https://us1.locationiq.com/v1/reverse.php',[
            'key' => 'pk.ceed539868e5fc38968a5731fc051007',
            'lat' => $lat,
            'lon' => $lng,
            'format' => 'json',
        ]);
        $responseBody = json_decode($response->body());
        return response()->json([
            'success'   => true,
            'data'      => $responseBody,
        ]);
        
    }

    public function grafik() {
        $year = Carbon::now()->format('Y');

        $absenterlambat = Absen::select('id', 'created_at')
            ->where('status','=','Terlambat')
            ->whereYear('created_at', '=', $year)
            ->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        
         $absentepatwaktu = Absen::select('id', 'created_at')
            ->where('status','=','Tepat Waktu')
            ->whereYear('created_at', '=', $year)
            ->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

            $absenterlambatmcount = [];
            $absentepatwaktumcount = [];
            $absenterlambatArr = [];
            $absentepatwaktuArr = [];

            foreach ($absenterlambat as $key => $value) {
                $absenterlambatmcount[(int)$key] = count($value);
            }

            foreach ($absentepatwaktu as $key => $value) {
                $absentepatwaktumcount[(int)$key] = count($value);
            }

            for($i = 1; $i <= 12; $i++){
                if(!empty($absenterlambatmcount[$i])){
                    $absenterlambatArr[$i] = $absenterlambatmcount[$i];    
                }else{
                    $absenterlambatArr[$i] = 0;    
                }
            }

            for($i = 1; $i <= 12; $i++){
                if(!empty($absentepatwaktumcount[$i])){
                    $absentepatwaktuArr[$i] = $absentepatwaktumcount[$i];    
                }else{
                    $absentepatwaktuArr[$i] = 0;    
                }
            }

            
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'absenterlambat' => $absenterlambatArr,
            'absentepatwaktu' => $absentepatwaktuArr,
        ]);
    }

    public function grafikpresensiuser($id) {
        $year = Carbon::now()->format('Y');

        $absenterlambat = Absen::select('id', 'created_at')
            ->where('status','=','Terlambat')
            ->where('user_id','=',$id)
            ->whereYear('created_at', '=', $year)
            ->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });
        
         $absentepatwaktu = Absen::select('id', 'created_at')
            ->where('status','=','Tepat Waktu')
            ->whereYear('created_at', '=', $year)
            ->get()
            ->groupBy(function($date) {
                //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
                return Carbon::parse($date->created_at)->format('m'); // grouping by months
            });

            $absenterlambatmcount = [];
            $absentepatwaktumcount = [];
            $absenterlambatArr = [];
            $absentepatwaktuArr = [];

            foreach ($absenterlambat as $key => $value) {
                $absenterlambatmcount[(int)$key] = count($value);
            }

            foreach ($absentepatwaktu as $key => $value) {
                $absentepatwaktumcount[(int)$key] = count($value);
            }

            for($i = 1; $i <= 12; $i++){
                if(!empty($absenterlambatmcount[$i])){
                    $absenterlambatArr[$i] = $absenterlambatmcount[$i];    
                }else{
                    $absenterlambatArr[$i] = 0;    
                }
            }

            for($i = 1; $i <= 12; $i++){
                if(!empty($absentepatwaktumcount[$i])){
                    $absentepatwaktuArr[$i] = $absentepatwaktumcount[$i];    
                }else{
                    $absentepatwaktuArr[$i] = 0;    
                }
            }

            
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'absenterlambat' => $absenterlambatArr,
            'absentepatwaktu' => $absentepatwaktuArr,
        ]);
    }

    public function grafikbulananuser() {
        $user_id = Auth::id();
        $year = Carbon::now()->format('Y');

        $absenterlambat = Absen::select('id', 'created_at')
        ->where('status','=','Terlambat')
        ->where('user_id','=',$user_id)
        ->whereYear('created_at', '=', $year)
        ->get()
        ->groupBy(function($date) {
            //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
            return Carbon::parse($date->created_at)->format('m'); // grouping by months
        });
    
        $absentepatwaktu = Absen::select('id', 'created_at')
        ->where('user_id','=',$user_id)
        ->where('status','=','Tepat Waktu')
        ->whereYear('created_at', '=', $year)
        ->get()
        ->groupBy(function($date) {
            //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
            return Carbon::parse($date->created_at)->format('m'); // grouping by months
        });

        $absenterlambatmcount = [];
        $absentepatwaktumcount = [];
        $absenterlambatArr = [];
        $absentepatwaktuArr = [];

        foreach ($absenterlambat as $key => $value) {
            $absenterlambatmcount[(int)$key] = count($value);
        }

        foreach ($absentepatwaktu as $key => $value) {
            $absentepatwaktumcount[(int)$key] = count($value);
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($absenterlambatmcount[$i])){
                $absenterlambatArr[$i] = $absenterlambatmcount[$i];    
            }else{
                $absenterlambatArr[$i] = 0;    
            }
        }

        for($i = 1; $i <= 12; $i++){
            if(!empty($absentepatwaktumcount[$i])){
                $absentepatwaktuArr[$i] = $absentepatwaktumcount[$i];    
            }else{
                $absentepatwaktuArr[$i] = 0;    
            }
        }
        
        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'absenterlambat'  => $absenterlambatArr,
            'absentepatwaktu'  => $absentepatwaktuArr,
        ]);
    }

    public function grafikbulananadmin($id) {
        $absenterlambat = Absen::select('id', 'created_at')->where('absen.user_id','=',$id)->where('absen.status','=','Terlambat')
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();
        $absentepatwaktu = Absen::select('id', 'created_at')->where('absen.user_id','=',$id)->where('absen.status','=','Tepat Waktu')
                            ->whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();

        return response()->json([
            'success'   => true,
            'message'   => 'OK',
            'absenterlambat'  => $absenterlambat,
            'absentepatwaktu'  => $absentepatwaktu,
        ]);
    }

    
    
}
