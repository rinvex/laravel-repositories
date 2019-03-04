<?php

declare(strict_types=1);

namespace Rinvex\Tests\Stubs;

use Rinvex\Repository\Traits\Criteriable;
use Rinvex\Repository\Repositories\EloquentRepository;

class EloquentUserRepository extends EloquentRepository
{
    use Criteriable;

    protected $model = EloquentUser::class;

    protected $repositoryId = 'rinvex.repository.user';
}
