<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Datebase\Eloquent\Model;

interface BaseRepository
{
    /**
    * Find a resource by id
    *
    * @param $id
    * @return Model|null
    */
    public function find($id);


    /**
    * Find a resource by criteria
    *
    * @param array $criteria
    * @return Model|null
    */
    public function findOneBy(array $criteria);


    /**
    * Search All resources by criteria
    *
    * @param array $searchCriteria
    * @return Collection
    */
    public function findBy(array $searchCriteria);


    /**
    * Search All resources by any values of a key
    *
    * @param string $key
    * @param array $values
    * @return Collection
    */
    public function findIn($key, array $values);


    /**
    * Save a resource
    *
    * @param array $data
    * @return Model
    */
    public function save(array $data);


    /**
    * Update a resource
    *
    * @param string $key
    * @param array $data
    * @return Model
    */
    public function update(string $key, array $data);


    /**
    * Delete a resource
    *
    * @param Model $model
    * @return mixed
    */
    public function delete(Model $model);
}
