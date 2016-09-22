<?php

namespace Rinvex\Tests\Stubs;

class EloquentPost extends \Illuminate\Database\Eloquent\Model
{
    protected $table    = 'posts';
    protected $fillable = ['user_id', 'parent_id', 'name'];

    public function user()
    {
        $this->belongsTo(EloquentUser::class);
    }
}
