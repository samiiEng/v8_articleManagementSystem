<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class BaseRepository
{

    public function find($model, $conditions)
    {
        return DB::select("SELECT * FROM $model $conditions[0]", [$conditions[1]]);
    }

    public function store($model, $conditions)
    {
        return DB::store("INSERT INTO $model ($conditions[0]) VALUES ($conditions[1])");
    }

    public function update($model, $conditions)
    {
        return DB::update("UPDATE $model SET $conditions[0]", [$conditions[1]]);
    }

    public function destroy($model, $conditions)
    {
        return DB::delete("DELETE FROM $model $conditions[0]", [$conditions[1]]);
    }
}
