<?php

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
