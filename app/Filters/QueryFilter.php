<?php

namespace App\Filters;

use App\Helpers\Common\StringHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QueryFilter
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $search = [];

    /**
     * @var $builder
     */
    protected $builder;

    /**
     * @var string|null
     */
    protected $orderField = null;

    /**
     * @var string
     */
    protected $orderType = 'asc';

    /**
     * @var $filterable
     */
    protected $filterable;

    /**
     * QueryFilter constructor.
     * @param Request $request
     */
    public function __construct(Request $request = null)
    {
        if ($request != null) {
            $this->request = $request;
            $this->filters = $this->request->all();
        } else {
            $this->filters = [];
        }
    }

    /**
     * @param Builder $builder
     * @param array $filterFields
     * @param array $orderFields
     * @return Builder
     */
    public function apply(Builder $builder, $params)
    {
        $this->builder = $builder;
        $filters = $params ?? $this->filters;

        foreach ($filters as $name => $value)
        {
            $method = 'filter' . Str::studly($name);
            if (gettype($value) == 'string') $value = trim($value);
            
            if (is_null($value) || ($value == '' && $value !== false)) {
                continue;
            }

            if (method_exists($this, $method)) {
                $this->{$method}($value);
                continue;
            }

            if (empty($this->filterable) || !is_array($this->filterable)) {
                continue;
            }

            if (in_array($name, $this->filterable)) {
                $this->builder->where($name, $value);
                continue;
            }

            if (key_exists($name, $this->filterable)) {
                $this->builder->where($this->filterable[$name], $value);
                continue;
            }
        }

        return $this->builder;
    }
}

