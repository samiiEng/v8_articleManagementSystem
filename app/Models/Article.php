<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $primaryKey = 'article_id';

    public function usersAuthor()
    {
        return $this->belongsTo(User::class, 'user_ref_id', 'user_id');
    }

    public function articlesRevisionParent(){
        return $this->hasOne(Article::class, 'revision_ref_id', 'article_id');
    }


}
