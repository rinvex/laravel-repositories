<?php

class EloquentRepositoryTests extends \AbstractEloquentTests
{
    public function testFindAll()
    {
        $userRepository = $this->userRepository();
        $result         = $userRepository->findAll();
        $this->assertCount(4, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
    }

    public function testFindAllUsingGroupBy()
    {
        $userRepository = $this->userRepository();
        $result = $userRepository->groupBy('name')->findAll();
        $this->assertCount(3, $result);
    }

    public function testFind()
    {
        $userRepository = $this->userRepository();
        $result         = $userRepository->find(1);
        $this->assertInstanceOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testFindBy()
    {
        $userRepository = $this->userRepository();
        $result         = $userRepository->findBy('name', 'evsign');
        $this->assertInstanceOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals('evsign', $result->name);
    }

    public function testFindFirst()
    {
        $userRepository = $this->userRepository();
        $result         = $userRepository->findFirst();
        $this->assertInstanceOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testFindWhere()
    {
        $userRepository = $this->userRepository();
        $result         = $userRepository->findWhere(['name', '=', 'omranic']);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals('omranic', $result->first()->name);
    }

    public function testFindWhereIn()
    {
        $userRepository = $this->userRepository();
        $result         = $userRepository->findWhereIn(['name', ['omranic', 'evsign']]);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertContainsOnlyInstancesOf(\Rinvex\Tests\Stubs\EloquentUser::class, $result);
        $this->assertEquals(['evsign', 'omranic'], $result->pluck('name')->toArray());
    }
}
