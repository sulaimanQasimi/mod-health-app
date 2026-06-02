<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepotRequestStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'depot_request_id',
        'from_status',
        'to_status',
        'user_id',
        'notes',
    ];

    public function depotRequest()
    {
        return $this->belongsTo(DepotRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
