<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';

    public function usersFrom()
    {
        return $this->belongsTo(User::class, 'from_ref_id', 'user_id');
    }

    public function usersTo()
    {
        return $this->belongsTo(User::class, 'to_ref_id', 'user_id');
    }
}
