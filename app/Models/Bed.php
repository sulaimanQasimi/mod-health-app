<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bed extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['number','room_id'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
