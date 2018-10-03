<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 01.10.2018
 * Time: 12:29
 */

namespace Shore\Framework\Http\Request;

use Shore\Framework\DataProvider;

class Query extends DataProvider
{
    /**
     * Query constructor.
     *
     * @param array $queryParams
     */
    public function __construct(array $queryParams = [])
    {
        parent::__construct($queryParams);
    }

    public function addArgs(array $args = [])
    {
        $this->data = array_merge($this->data, $args);
    }
}
