<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'department_id';

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_department', 'category_ref_id', 'category_id');
    }

    public function departmentsParent(){
        return $this->hasOne(Department::class, 'department_ref_id', 'department_id');
    }

}
