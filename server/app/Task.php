<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 *  @property int                           $id
 *  @property string                        $name
 *  @property bool                          $completed
 *  @property int                           $list_id
 *  @property \App\TaskList                 $task_list
 *  @property \Illuminate\Support\Carbon    $created_at
 *  @property \Illuminate\Support\Carbon    $updated_at
 */
class Task extends Model
{
    protected $fillable = [
        'name',
        'completed'
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    protected $attributes = [
        'completed' => false,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function task_list()
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }
}
