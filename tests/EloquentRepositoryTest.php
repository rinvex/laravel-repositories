<?php

declare(strict_types=1);

class EloquentRepositoryTests extends \AbstractEloquentTests
{
    public function testFindAll()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->findAll();
        $this->assertCount(4, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
    }

    public function testFindAllUsingGroupBy()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->groupBy('name')->findAll();
        $this->assertCount(3, $result);
    }

    public function testFindAllUsingHaving()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->groupBy('name')->having('age', '>', 24)->findAll();
        $this->assertCount(3, $result);
    }

    public function testFindAllUsingHavingAndOrHaving()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->groupBy('name')->having('age', '>', 24)->orHaving('name', 'like', '%o%')->findAll();
        $this->assertCount(3, $result);
    }

    public function testFindAllUsingMultipleHaving()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->groupBy('name')->having('age', '>', 24)->having('age', '<', 26)->findAll();
        $this->assertCount(1, $result);
    }

    public function testFind()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->find(1);
        $this->assertInstanceOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testFindBy()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->findBy('name', 'evsign');
        $this->assertInstanceOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals('evsign', $result->name);
    }

    public function testFindFirst()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->findFirst();
        $this->assertInstanceOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testFindWhere()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->findWhere(['name', '=', 'omranic']);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals('omranic', $result->first()->name);
    }

    public function testFindWhereIn()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->findWhereIn(['name', ['omranic', 'evsign']]);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals(['evsign', 'omranic'], $result->pluck('name')->toArray());
    }

    public function testCount()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->count();
        $this->assertEquals(4, $result);
    }

    public function testMin()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->min('age');
        $this->assertEquals(24, $result);
    }

    public function testMax()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->max('age');
        $this->assertEquals(28, $result);
    }

    public function testAvg()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->avg('age');
        $this->assertEquals(25.75, $result);
    }

    public function testSum()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->sum('age');
        $this->assertEquals(103, $result);
    }
}
