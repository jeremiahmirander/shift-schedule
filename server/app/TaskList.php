<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int                                                    $id
 * @property string                                                 $name
 * @property int                                                    $user_id
 * @property App\User                                               $user
 * @property \Illuminate\Database\Eloquent\Collection|\App\Item[]   $items
 * @property \Illuminate\Support\Carbon                             $created_at
 * @property \Illuminate\Support\Carbon                             $updated_at
 *
 */

class TaskList extends Model
{
    protected $fillable = [
        'name'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'list_id');
    }
}
