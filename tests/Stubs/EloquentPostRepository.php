<?php

declare(strict_types=1);

namespace Rinvex\Tests\Stubs;

use Rinvex\Repository\Repositories\EloquentRepository;

class EloquentPostRepository extends EloquentRepository
{
    protected $model = EloquentPost::class;

    protected $repositoryId = 'rinvex.repository.post';
}
