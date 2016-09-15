<?php

class EloquentRepositoryCriteriaIntegrationTests extends AbstractEloquentTests
{
    public function testFindAllByClassNameCriterion()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion(FirstTestCriterion::class);
        $result = $userRepository->findAll();

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals($result->first()->id, 1);
    }

    public function testFindAllByObjectCriterion()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion(new FirstTestCriterion());
        $result = $userRepository->findAll();

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals($result->first()->id, 1);
    }

    public function testFindAllByClosureCriterion()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion(function ($query, $repository) {
            return $query->where('id', 2);
        });
        $result = $userRepository->findAll();

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals($result->first()->id, 2);
    }

    public function testFindAllWithDefaultCriteria()
    {
        $userRepository = $this->userRepository();
        $userRepository->pushCriterion(function ($query, $repository) {
            return $query->orWhere('id', 1);
        })->setDefaultCriteria([new SecondTestCriterion()]);

        $result = $userRepository->findAll();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
    }

    public function testFindAllWithSkipCriteria()
    {
        $userRepository = $this->userRepository();

        $userRepository->pushCriteria([
            new FirstTestCriterion(),
            new SecondTestCriterion(),
        ]);

        $result = $userRepository->skipCriteria()->findAll();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
    }

    public function testFindAllAfterRemoveSkipCriteria()
    {
        $userRepository = $this->userRepository();

        $userRepository->pushCriteria([
            new FirstTestCriterion(),
        ]);

        $result = $userRepository->skipCriteria()->findAll();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);

        $result = $userRepository->skipCriteria(false)->findAll();
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
    }

    public function testFindAllWithSkipDefaultCriteria()
    {
        $userRepository = $this->userRepository();

        $userRepository->pushCriteria([
            new FirstTestCriterion(),
        ])->setDefaultCriteria([new SecondTestCriterion()]);

        $result = $userRepository->skipDefaultCriteria()->findAll();

        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
    }
}
