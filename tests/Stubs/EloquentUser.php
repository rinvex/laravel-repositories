<?php

declare(strict_types=1);

namespace Rinvex\Tests\Stubs;

class EloquentUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'age'];

    public function posts()
    {
        return $this->hasMany(EloquentPost::class, 'user_id');
    }
}
