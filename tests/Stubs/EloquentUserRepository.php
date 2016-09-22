<?php

namespace Rinvex\Tests\Stubs;

use Rinvex\Repository\Repositories\EloquentRepository;
use Rinvex\Repository\Traits\Criteriable;

class EloquentUserRepository extends EloquentRepository
{
    use Criteriable;

    protected $model        = EloquentUser::class;
    protected $repositoryId = 'rinvex.repository.user';
}
