<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $primaryKey = 'tag_id';

    public function departmentsCategories(){
        return $this->belongsTo(CategoryDepartment::class, 'category_department_ref_id', 'category_department_id');
    }
}
