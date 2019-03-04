<?php

declare(strict_types=1);

namespace Rinvex\Repository\Exceptions;

use Exception;

class RepositoryException extends Exception
{
    public static function listNotFound($list, $object)
    {
        return new static('Given list "'.$list.'" not found in '.get_class($object).' class');
    }
}
