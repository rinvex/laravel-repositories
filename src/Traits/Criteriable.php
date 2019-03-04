<?php

declare(strict_types=1);

namespace Rinvex\Repository\Traits;

use Closure;
use Illuminate\Support\Arr;
use Rinvex\Repository\Contracts\CriterionContract;
use Rinvex\Repository\Contracts\RepositoryContract;
use Rinvex\Repository\Exceptions\CriterionException;
use Rinvex\Repository\Exceptions\RepositoryException;

trait Criteriable
{
    /**
     * List of repository criteria.
     *
     * @var array
     */
    protected $criteria = [];

    /**
     * List of default repository criteria.
     *
     * @var array
     */
    protected $defaultCriteria = [];

    /**
     * Skip criteria flag.
     * If setted to true criteria will not be apply to the query.
     *
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Skip default criteria flag.
     * If setted to true default criteria will not be added to the criteria list.
     *
     * @var bool
     */
    protected $skipDefaultCriteria = false;

    /**
     * Return name for the criterion.
     * If as criterion in parameter passed string we assume that is criterion class name.
     *
     * @param CriterionContract|Closure|string $criteria
     *
     * @return string
     */
    public function getCriterionName($criteria): string
    {
        if ($criteria instanceof Closure) {
            return spl_object_hash($criteria);
        }

        return is_object($criteria) ? get_class($criteria) : $criteria;
    }

    /**
     * Try to instantiate given criterion class name with this arguments.
     *
     * @param $class
     * @param $arguments
     *
     * @throws CriterionException
     *
     * @return mixed
     */
    protected function instantiateCriterion($class, $arguments)
    {
        $reflection = new \ReflectionClass($class);

        if (! $reflection->implementsInterface(CriterionContract::class)) {
            throw CriterionException::classNotImplementContract($class);
        }

        // If arguments is an associative array we can assume their order and parameter existence
        if (Arr::isAssoc($arguments)) {
            $parameters = array_column($reflection->getConstructor()->getParameters(), 'name');

            $arguments = array_filter(array_map(function ($parameter) use ($arguments) {
                return $arguments[$parameter] ?? null;
            }, $parameters));
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Return class and arguments from passed array criterion.
     * Extracting class and arguments from array.
     *
     * @param array $criterion
     *
     * @throws CriterionException
     *
     * @return array
     */
    protected function extractCriterionClassAndArgs(array $criterion): array
    {
        if (count($criterion) > 2 || empty($criterion)) {
            throw CriterionException::wrongArraySignature($criterion);
        }

        // If an array is assoc we assume that the key is a class and value is an arguments
        if (Arr::isAssoc($criterion)) {
            $criterion = [array_keys($criterion)[0], array_values($criterion)[0]];
        } elseif (count($criterion) === 1) {
            // If an array is not assoc but count is one, we can assume there is a class without arguments.
            // Like when a string passed as criterion
            array_push($criterion, []);
        }

        return $criterion;
    }

    /**
     * Add criterion to the specific list.
     * low-level implementation of adding criterion to the list.
     *
     * @param Closure|CriterionContract|array|string $criterion
     * @param string                                 $list
     *
     * @throws CriterionException
     * @throws RepositoryException
     *
     * @return $this
     */
    protected function addCriterion($criterion, $list)
    {
        if (! property_exists($this, $list)) {
            throw RepositoryException::listNotFound($list, $this);
        }

        if (! $criterion instanceof Closure &&
            ! $criterion instanceof CriterionContract &&
            ! is_string($criterion) &&
            ! is_array($criterion)
        ) {
            throw CriterionException::wrongCriterionType($criterion);
        }

        //If criterion is a string we will assume it is a class name without arguments
        //and we need to normalize signature for instantiation try
        if (is_string($criterion)) {
            $criterion = [$criterion, []];
        }

        //If the criterion is an array we will assume it is an array of class name with arguments
        //and try to instantiate this
        if (is_array($criterion)) {
            $criterion = call_user_func_array([$this, 'instantiateCriterion'], $this->extractCriterionClassAndArgs($criterion));
        }

        $this->{$list}[$this->getCriterionName($criterion)] = $criterion;

        return $this;
    }

    /**
     * Add criteria to the specific list
     * low-level implementation of adding criteria to the list.
     *
     * @param array $criteria
     * @param $list
     */
    protected function addCriteria(array $criteria, $list)
    {
        array_walk($criteria, function ($value, $key) use ($list) {
            $criterion = is_string($key) ? [$key, $value] : $value;
            $this->addCriterion($criterion, $list);
        });
    }

    /**
     * Push criterion to the criteria list.
     *
     * @param CriterionContract|Closure|array|string $criterion
     *
     * @return $this
     */
    public function pushCriterion($criterion)
    {
        $this->addCriterion($criterion, 'criteria');

        return $this;
    }

    /**
     * Remove provided criterion from criteria list.
     *
     * @param CriterionContract|Closure|string $criterion
     *
     * @return $this
     */
    public function removeCriterion($criterion)
    {
        unset($this->criteria[$this->getCriterionName($criterion)]);

        return $this;
    }

    /**
     * Remove provided criteria from criteria list.
     *
     * @param array $criteria
     *
     * @return RepositoryContract
     */
    public function removeCriteria(array $criteria)
    {
        array_walk($criteria, function ($criterion) {
            $this->removeCriterion($criterion);
        });

        return $this;
    }

    /**
     * Push array of criteria to the criteria list.
     *
     * @param array $criteria
     *
     * @return $this
     */
    public function pushCriteria(array $criteria)
    {
        $this->addCriteria($criteria, 'criteria');

        return $this;
    }

    /**
     * Flush criteria list.
     * We can flush criteria only when they is not skipped.
     *
     * @return $this
     */
    public function flushCriteria()
    {
        if (! $this->skipCriteria) {
            $this->criteria = [];
        }

        return $this;
    }

    /**
     * Set default criteria list.
     *
     * @param array $criteria
     *
     * @return $this
     */
    public function setDefaultCriteria(array $criteria)
    {
        $this->addCriteria($criteria, 'defaultCriteria');

        return $this;
    }

    /**
     * Return default criteria list.
     *
     * @return array
     */
    public function getDefaultCriteria(): array
    {
        return $this->defaultCriteria;
    }

    /**
     * Return current list of criteria.
     *
     * @return array
     */
    public function getCriteria(): array
    {
        if ($this->skipCriteria) {
            return [];
        }

        return $this->skipDefaultCriteria ? $this->criteria : array_merge($this->getDefaultCriteria(), $this->criteria);
    }

    /**
     * Set skipCriteria flag.
     *
     * @param bool|true $flag
     *
     * @return $this
     */
    public function skipCriteria($flag = true)
    {
        $this->skipCriteria = $flag;

        return $this;
    }

    /**
     * Set skipDefaultCriteria flag.
     *
     * @param bool|true $flag
     *
     * @return $this
     */
    public function skipDefaultCriteria($flag = true)
    {
        $this->skipDefaultCriteria = $flag;

        return $this;
    }

    /**
     * Check if a given criterion name now in the criteria list.
     *
     * @param CriterionContract|Closure|string $criterion
     *
     * @return bool
     */
    public function hasCriterion($criterion): bool
    {
        return isset($this->getCriteria()[$this->getCriterionName($criterion)]);
    }

    /**
     * Return criterion object or closure from criteria list by name.
     *
     * @param $criterion
     *
     * @return CriterionContract|Closure|null
     */
    public function getCriterion($criterion)
    {
        if ($this->hasCriterion($criterion)) {
            return $this->getCriteria()[$this->getCriterionName($criterion)];
        }
    }

    /**
     * Apply criteria list to the given query.
     *
     * @param $query
     * @param $repository
     *
     * @return mixed
     */
    public function applyCriteria($query, $repository)
    {
        foreach ($this->getCriteria() as $criterion) {
            if ($criterion instanceof CriterionContract) {
                $query = $criterion->apply($query, $repository);
            } elseif ($criterion instanceof Closure) {
                $query = $criterion($query, $repository);
            }
        }

        return $query;
    }
}
