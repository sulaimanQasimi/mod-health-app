<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Sector;
use App\Models\Recipient;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name_en',
        'email',
        'recipients',
        'document_type',
        'name_en',
        'name_dr',
        'email',
        'last_name_dr',
        'sector_id',
        'recipient_id',
        'password',
        'status',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'recipients' => 'array',
    ];
    

    public function scopeSectorBased($query) {
        $sector_id = auth()->user()->sector_id;
        if(auth()->user()->can('admin')) {
            if ($sector_id != null) {
                return $query->where('sector_id', $sector_id);
            }
        }
        else {
            return $query->whereId(auth()->user()->id);
        }

      return $query;

    }
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }


    public function recipient() {

      return $this->hasOne(Recipient::class, 'id', 'recipient_id');

    }

    public function scopeuserBasedUser($query,$user){
        if($user->can('admin')){
            return $query;
        }else{
            $query->where('id',$user->id);
        }
    }

    public function scopeSectorId($q) {
        $sector_id = currentUser()->sector_id;
        if($sector_id) {
            $q->where('sector_id', $sector_id);
        }
        return $q;
    } 
    

}
