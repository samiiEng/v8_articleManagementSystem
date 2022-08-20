<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultMessages extends Model
{
    use HasFactory;
    protected $table = 'default_messages';
    protected $keyType = "smallInteger";
}
