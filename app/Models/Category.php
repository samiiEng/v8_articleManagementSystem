<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';
    protected $primaryKey = 'category_id';

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'category_department', 'department_ref_id', 'department_id');
    }

    public function categoriesParent()
    {
        return $this->hasOne(Category::class, 'category_ref_id', 'category_id');
    }
}
