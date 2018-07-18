<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Rinvex\Tests\Stubs\EloquentPost;
use Rinvex\Tests\Stubs\EloquentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Config\Repository as Config;
use Rinvex\Tests\Stubs\EloquentPostRepository;
use Rinvex\Tests\Stubs\EloquentUserRepository;
use Illuminate\Support\Traits\CapsuleManagerTrait;

abstract class AbstractEloquentTests extends TestCase
{
    use CapsuleManagerTrait;

    /** @var \Illuminate\Container\Container */
    protected $container;

    /** Setup the database schema. */
    protected function setUp()
    {
        $this->setupContainer();
        $this->setupDatabase(new Manager($this->getContainer()));
        $this->migrate();
        $this->seed();
    }

    /** Setup the IoC container instance. */
    protected function setupContainer()
    {
        $config = [
            'models' => 'Models',
            'cache' => [
                'keys_file' => '',
                'lifetime' => 0,
                'clear_on' => [
                    'create',
                    'update',
                    'delete',
                ],
                'skip_uri' => 'skipCache',
            ],
        ];

        $this->container = new Container();
        $this->container->instance('config', new Config());
        $this->getContainer()['config']->offsetSet('rinvex.repository', $config);
    }

    /** Setup the database. */
    protected function setupDatabase(Manager $db)
    {
        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();
    }

    /** Create tables. */
    protected function migrate()
    {
        $this->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email');
            $table->integer('age');
            $table->timestamps();
        });

        $this->schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('parent_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });
    }

    /** Create test users and posts. */
    protected function seed()
    {
        $evsign = EloquentUser::create(['name' => 'evsign', 'email' => 'evsign.alex@gmail.com', 'age' => '25']);
        $omranic = EloquentUser::create(['name' => 'omranic', 'email' => 'me@omranic.com', 'age' => '26']);
        $ionut = EloquentUser::create(['name' => 'ionut', 'email' => 'ionutz2k@gmail.com', 'age' => '24']);
        $anotherIonut = EloquentUser::create(['name' => 'ionut', 'email' => 'ionut@example.com', 'age' => '28']);

        $evsign->posts()->saveMany([
            new EloquentPost(['name' => 'first post']),
            new EloquentPost(['name' => 'second post']),
        ]);

        $omranic->posts()->saveMany([
            new EloquentPost(['name' => 'third post']),
            new EloquentPost(['name' => 'fourth post']),
        ]);

        $ionut->posts()->saveMany([
            new EloquentPost(['name' => 'fifth post']),
            new EloquentPost(['name' => 'sixth post']),
        ]);

        $anotherIonut->posts()->saveMany([
            new EloquentPost(['name' => 'seventh post']),
            new EloquentPost(['name' => 'eighth post']),
        ]);
    }

    /**
     * Get Schema Builder.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema(): Builder
    {
        return Model::resolveConnection()->getSchemaBuilder();
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('users');
        $this->schema()->drop('posts');
        unset($this->container);
    }

    /**
     * @return EloquentUserRepository
     */
    protected function userRepository()
    {
        return (new EloquentUserRepository())
            ->setContainer($this->getContainer());
    }

    /**
     * @return EloquentPostRepository
     */
    protected function postRepository()
    {
        return (new EloquentPostRepository())
            ->setContainer(new Container());
    }
}
