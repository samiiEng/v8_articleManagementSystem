<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class MessageRepository
{
    public function create($validated)
    {


    }

    public function update($validated)
    {

    }

    public function find()
    {

    }

    public function softDelete()
    {

    }

    public function forceDelete($value)
    {
        DB::delete("DELETE FROM messages WHERE message_id = ?", [$value]);

    }

    public function restoreDeleted()
    {

    }
}
