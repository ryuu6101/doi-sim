<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    //model muốn tương tác
    protected $model;

    //khởi tạo
    public function __construct()
    {
        $this->setModel();
    }

    //lấy model tương ứng
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->model = app()->make(
            $this->getModel()
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function find($id)
    {
        $result = $this->model->find($id);

        return $result;
    }

    public function create($attributes = [])
    {
        return $this->model->create($attributes);
    }

    public function update($id, $attributes = [])
    {
        $result = $this->find($id);
        if ($result) {
            $result->update($attributes);
            return $result;
        }

        return false;
    }

    public function delete($id)
    {
        $result = $this->find($id);
        if ($result) {
            $result->delete();

            return true;
        }

        return false;
    }

    public function getColumn($column) {
        return $this->model->distinct()->pluck($column);
    }

    public function filter($params = [], $paginate = 0, $sort = 'asc', $sort_by = 'created_at') {
        $class_name = class_basename($this->model);
        $filter_class = "App\\Filters\\{$class_name}Filter";
        $filter = new $filter_class();

        $list = $this->model->filter($filter, $params)->orderBy($sort_by, $sort);

        if ($paginate > 0) return $list->paginate($paginate);
        else return $list->get();
    }
}
