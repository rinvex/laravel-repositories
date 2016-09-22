<?php

namespace Rinvex\Tests\Stubs;

class EloquentUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table    = 'users';
    protected $fillable = ['name', 'email'];

    public function posts()
    {
        return $this->hasMany(EloquentPost::class, 'user_id');
    }
}
