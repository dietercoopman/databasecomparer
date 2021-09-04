<?php

namespace DieterCoopman\DatabaseComparer;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DieterCoopman\DatabaseComparer\DatabaseComparer
 */
class DatabaseComparerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'databasecomparer';
    }
}
