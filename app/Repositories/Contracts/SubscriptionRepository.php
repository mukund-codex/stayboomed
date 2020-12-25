<?php

namespace App\Repositories\Contracts;
use Illuminate\Support\Collection;

interface SubscriptionRepository extends BaseRepository
{
    /**
    * Filter campaign from repository.
    *
    * @param array $filter
    * @param array $operator
    * @return bool
    */
    public function filtered(array $filter, array $operator);

    public function export(Collection $request);
    
    /**
     * Import Data From Repository
     *
     * @param Illuminate\Http\Request $request
     * @return bool
     */
    public function import($request);
}
