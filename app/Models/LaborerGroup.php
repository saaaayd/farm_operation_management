<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaborerGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'user_id',
    ];

    /**
     * Get the laborers that belong to the group.
     */
    public function laborers()
    {
        return $this->belongsToMany(Laborer::class, 'group_laborer');
    }

    /**
     * Get the tasks assigned to the group.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
