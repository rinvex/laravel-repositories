<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Rinvex Repository Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Rinvex Repository Package
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

declare(strict_types=1);

namespace Rinvex\Repository\Exceptions;

use Exception;
use Rinvex\Repository\Contracts\CriterionContract;

class CriterionException extends Exception
{
    public static function wrongCriterionType($criterion)
    {
        $type = gettype($criterion);
        $value = $type === 'object' ? get_class($criterion) : $criterion;

        return new static('Given criterion with type '.$type.' and value '.$value.' is not allowed');
    }

    public static function classNotImplementContract($criterionClassName)
    {
        return new static('Given '.$criterionClassName.' class is not implement '.CriterionContract::class.'contract');
    }

    public static function wrongArraySignature(array $criterion)
    {
        return new static(
            'Array signature for criterion instantiating must contain only two elements in case of sequential array and one in case of assoc array. '.
            'Array with length "'.count($criterion).'" given');
    }
}
