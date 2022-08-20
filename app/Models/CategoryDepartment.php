<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryDepartment extends Pivot
{
    protected $table = 'category_department';
    protected $primaryKey = 'category_department_id';

    public function tags(){
        return $this->hasMany(Tag::class, 'category_department_ref_id', 'category_department_id');
    }
}
