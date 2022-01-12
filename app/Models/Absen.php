<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    use HasFactory;

    protected $table = 'absen';
  
    protected $fillable = ['id','user_id','shift','present','starting','finish','manhour','starting_photo','starting_lat','starting_lng','creator','updated','shift_code','finish_lat','finish_lng','finish_photo','updater','status'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    
}
