<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;

class EloquentRepositoryCriteriaTests extends AbstractEloquentTests
{
    public function testObjectCriterionPush()
    {
        $userRepository = $this->userRepository();
        $criterion = new FirstTestCriterion();
        $userRepository->pushCriterion($criterion);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertAttributeContains($criterion, 'criteria', $userRepository);
    }

    public function testClosureCriterionPush()
    {
        $userRepository = $this->userRepository();
        $criterion = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };

        $userRepository->pushCriterion($criterion);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertAttributeContains($criterion, 'criteria', $userRepository);
    }

    public function testClassNameCriterionPush()
    {
        $userRepository = $this->userRepository();
        $criterion = FirstTestCriterion::class;
        $userRepository->pushCriterion($criterion);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertContainsOnlyInstancesOf($criterion, $this->getObjectAttribute($userRepository, 'criteria'));
    }

    public function testClassNameWithAssocArgumentsAssocCriterionPush()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion([ThirdTestWithArgumentsCriterion::class => ['to' => '2016-09-30', 'from' => '2016-09-01']]);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertArrayHasKey(ThirdTestWithArgumentsCriterion::class, $userRepository->getCriteria());

        $criterion = $userRepository->getCriterion(ThirdTestWithArgumentsCriterion::class);
        $this->assertAttributeEquals('2016-09-01', 'from', $criterion);
        $this->assertAttributeEquals('2016-09-30', 'to', $criterion);
    }

    public function testClassNameWithAssocArgumentsSequentialCriterionPush()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion([ThirdTestWithArgumentsCriterion::class, ['to' => '2016-09-30', 'from' => '2016-09-01']]);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertArrayHasKey(ThirdTestWithArgumentsCriterion::class, $userRepository->getCriteria());

        $criterion = $userRepository->getCriterion(ThirdTestWithArgumentsCriterion::class);
        $this->assertAttributeEquals('2016-09-01', 'from', $criterion);
        $this->assertAttributeEquals('2016-09-30', 'to', $criterion);
    }

    public function testClassNameWithSequentialArgumentsAssocCriterionPush()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion([ThirdTestWithArgumentsCriterion::class => ['2016-09-01', '2016-09-30']]);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertArrayHasKey(ThirdTestWithArgumentsCriterion::class, $userRepository->getCriteria());

        $criterion = $userRepository->getCriterion(ThirdTestWithArgumentsCriterion::class);
        $this->assertAttributeEquals('2016-09-01', 'from', $criterion);
        $this->assertAttributeEquals('2016-09-30', 'to', $criterion);
    }

    public function testClassNameWithSequentialArgumentsSequentialCriterionPush()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion([ThirdTestWithArgumentsCriterion::class, ['2016-09-01', '2016-09-30']]);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $this->assertArrayHasKey(ThirdTestWithArgumentsCriterion::class, $userRepository->getCriteria());

        $criterion = $userRepository->getCriterion(ThirdTestWithArgumentsCriterion::class);
        $this->assertAttributeEquals('2016-09-01', 'from', $criterion);
        $this->assertAttributeEquals('2016-09-30', 'to', $criterion);
    }

    public function testPushTwoDifferentCriteria()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = FirstTestCriterion::class;
        $secondCriterion = SecondTestCriterion::class;

        $userRepository->pushCriterion([$firstCriterion]);
        $userRepository->pushCriterion([$secondCriterion]);

        $this->assertAttributeCount(2, 'criteria', $userRepository);
    }

    public function testPushTwoDifferentClosureCriteria()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };
        $secondCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 2);
        };

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($secondCriterion);

        $this->assertAttributeCount(2, 'criteria', $userRepository);
    }

    public function testPushTwoSimilarCriteria()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = FirstTestCriterion::class;

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($firstCriterion);

        $this->assertAttributeCount(1, 'criteria', $userRepository);
    }

    public function testPushCriteria()
    {
        $userRepository = $this->userRepository();

        $firstCriterion = FirstTestCriterion::class;
        $secondCriterion = new SecondTestCriterion();
        $thirdCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };

        $userRepository->pushCriteria([
            $firstCriterion,
            $secondCriterion,
            $thirdCriterion,
        ]);

        $this->assertAttributeCount(3, 'criteria', $userRepository);
    }

    public function testRemoveCriterionWhenPushedByClassName()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = FirstTestCriterion::class;
        $secondCriterion = SecondTestCriterion::class;

        $userRepository->pushCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);

        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(0, 'criteria', $userRepository);

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($secondCriterion);
        $this->assertAttributeCount(2, 'criteria', $userRepository);

        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $userRepository->removeCriterion($secondCriterion);
        $this->assertAttributeCount(0, 'criteria', $userRepository);
    }

    public function testRemoveCriterionWhenPushedObject()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = new FirstTestCriterion();
        $secondCriterion = new SecondTestCriterion();

        $userRepository->pushCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);

        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(0, 'criteria', $userRepository);

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($secondCriterion);
        $this->assertAttributeCount(2, 'criteria', $userRepository);

        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $userRepository->removeCriterion($secondCriterion);
        $this->assertAttributeCount(0, 'criteria', $userRepository);

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->removeCriterion(FirstTestCriterion::class);
        $this->assertAttributeCount(0, 'criteria', $userRepository);
    }

    public function testRemoveCriterionWhenPushedClosures()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };

        $secondCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 2);
        };

        $userRepository->pushCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);

        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(0, 'criteria', $userRepository);

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($secondCriterion);
        $this->assertAttributeCount(2, 'criteria', $userRepository);

        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $userRepository->removeCriterion($firstCriterion);
        $this->assertAttributeCount(1, 'criteria', $userRepository);
        $userRepository->removeCriterion($secondCriterion);
        $this->assertAttributeCount(0, 'criteria', $userRepository);
    }

    public function testRemoveCriteria()
    {
        $userRepository = $this->userRepository();
        $firstCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };

        $secondCriterion = function (Builder $builder, $repository) {
            return $builder->where('id', 2);
        };

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($secondCriterion);
        $this->assertAttributeCount(2, 'criteria', $userRepository);

        $userRepository->removeCriteria([$firstCriterion, $secondCriterion]);
        $this->assertAttributeCount(0, 'criteria', $userRepository);

        $userRepository->pushCriterion($firstCriterion);
        $userRepository->pushCriterion($secondCriterion);
        $userRepository->removeCriteria([$firstCriterion]);
        $userRepository->removeCriteria([$secondCriterion]);
        $this->assertAttributeCount(0, 'criteria', $userRepository);
    }

    public function testFlushCriteria()
    {
        $userRepository = $this->userRepository();

        $userRepository->pushCriteria([
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ]);

        $this->assertAttributeCount(2, 'criteria', $userRepository);
        $userRepository->flushCriteria();
        $this->assertAttributeCount(0, 'criteria', $userRepository);
    }

    public function testSetDefaultCriteria()
    {
        $userRepository = $this->userRepository();

        $userRepository->setDefaultCriteria([
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ]);

        $this->assertAttributeCount(2, 'defaultCriteria', $userRepository);
    }

    public function testGetDefaultCriteria()
    {
        $userRepository = $this->userRepository();
        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];

        $userRepository->setDefaultCriteria($criteria);
        $defaultCriteria = $userRepository->getDefaultCriteria();

        $this->assertArrayHasKey(FirstTestCriterion::class, $defaultCriteria);
        $this->assertArrayHasKey(SecondTestCriterion::class, $defaultCriteria);
    }

    public function testGetCriteria()
    {
        $userRepository = $this->userRepository();
        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];

        $userRepository->pushCriteria($criteria);

        $this->assertArrayHasKey(FirstTestCriterion::class, $userRepository->getCriteria());
        $this->assertArrayHasKey(SecondTestCriterion::class, $userRepository->getCriteria());
    }

    public function testGetCriteriaWithDefault()
    {
        $userRepository = $this->userRepository();

        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];

        $defaultCriteria = [
            function (Builder $builder, $repository) {
                return $builder->where('id', 1);
            },
        ];

        $userRepository->setDefaultCriteria($defaultCriteria)->pushCriteria($criteria);

        $this->assertCount(3, $userRepository->getCriteria());
    }

    public function testGetCriteriaWithSkipCriteria()
    {
        $userRepository = $this->userRepository();

        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];

        $userRepository->pushCriteria($criteria)->skipCriteria();

        $this->assertAttributeEquals(true, 'skipCriteria', $userRepository);
        $this->assertEmpty($userRepository->getCriteria());
    }

    public function testGetCriteriaWithDefaultWithSkipCriteria()
    {
        $userRepository = $this->userRepository();

        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];
        $defaultCriteria = [
            function (Builder $builder, $repository) {
                return $builder->where('id', 1);
            },
        ];

        $userRepository->setDefaultCriteria($defaultCriteria)->pushCriteria($criteria)->skipCriteria();

        $this->assertAttributeEquals(true, 'skipCriteria', $userRepository);
        $this->assertEmpty($userRepository->getCriteria());
    }

    public function testGetCriteriaWithSkipDefaultCriteria()
    {
        $userRepository = $this->userRepository();

        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];
        $defaultCriteria = [
            function (Builder $builder, $repository) {
                return $builder->where('id', 1);
            },
        ];

        $userRepository->setDefaultCriteria($defaultCriteria)->pushCriteria($criteria)->skipDefaultCriteria();
        $this->assertAttributeEquals(true, 'skipDefaultCriteria', $userRepository);
        $this->assertCount(2, $userRepository->getCriteria());
    }

    public function testHasCriterion()
    {
        $userRepository = $this->userRepository();

        $criteria = [
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ];

        $userRepository->pushCriteria($criteria);

        $this->assertTrue($userRepository->hasCriterion(FirstTestCriterion::class));
        $this->assertTrue($userRepository->hasCriterion(SecondTestCriterion::class));
    }

    public function testHasCriterionByClosure()
    {
        $userRepository = $this->userRepository();
        $firstCriteria = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };

        $secondCriteria = function (Builder $builder, $repository) {
            return $builder->where('id', 2);
        };

        $userRepository->pushCriteria([$firstCriteria, $secondCriteria]);

        $this->assertTrue($userRepository->hasCriterion($firstCriteria));
        $this->assertTrue($userRepository->hasCriterion($secondCriteria));
    }

    public function testGetCriterion()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriteria([
            FirstTestCriterion::class,
            SecondTestCriterion::class,
        ]);

        $this->assertInstanceOf(FirstTestCriterion::class, $userRepository->getCriterion(FirstTestCriterion::class));
        $this->assertInstanceOf(SecondTestCriterion::class, $userRepository->getCriterion(SecondTestCriterion::class));
    }

    public function testGetCriterionByClosure()
    {
        $userRepository = $this->userRepository();
        $firstCriteria = function (Builder $builder, $repository) {
            return $builder->where('id', 1);
        };

        $secondCriteria = function (Builder $builder, $repository) {
            return $builder->where('id', 2);
        };

        $userRepository->pushCriteria([$firstCriteria, $secondCriteria]);
        $this->assertSame($firstCriteria, $userRepository->getCriterion($firstCriteria));
        $this->assertSame($secondCriteria, $userRepository->getCriterion($secondCriteria));
        $this->assertNotSame($secondCriteria, $userRepository->getCriterion($firstCriteria));
    }

    public function testApplyCriteria()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriteria([
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ]);

        $resultQuery = $userRepository->applyCriteria($userRepository->createModel(), $userRepository);

        $this->assertEquals('select * from "users" where "id" = ? and "id" = ?', $resultQuery->toSql());
        $this->assertEquals([0 => 1, 1 => 2], $resultQuery->getBindings());
    }

    public function testApplyCriteriaWithClosureCriteria()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriteria([
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ])->pushCriterion(function (Builder $builder, $repository) {
            return $builder->where('id', 3);
        });

        $resultQuery = $userRepository->applyCriteria($userRepository->createModel(), $userRepository);

        $this->assertEquals('select * from "users" where "id" = ? and "id" = ? and "id" = ?', $resultQuery->toSql());
        $this->assertEquals([0 => 1, 1 => 2, 2 => 3], $resultQuery->getBindings());
    }
}

class FirstTestCriterion implements \Rinvex\Repository\Contracts\CriterionContract
{
    public function apply($builder, $repository)
    {
        return $builder->where('id', 1);
    }
}

class SecondTestCriterion implements \Rinvex\Repository\Contracts\CriterionContract
{
    public function apply($builder, $repository)
    {
        return $builder->where('id', 2);
    }
}

class ThirdTestWithArgumentsCriterion implements \Rinvex\Repository\Contracts\CriterionContract
{
    protected $from;

    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function apply($builder, $repository)
    {
        return $builder->whereBetween('created_at', [$this->from, $this->to]);
    }
}
