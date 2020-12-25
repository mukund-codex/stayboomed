<?php

namespace App\Repositories\Contracts;

interface UserRepository extends BaseRepository
{
    /**
    * Check User Login
    *
    * @param string $request_password
    * @param string $encrypt_password
    * @return Boolean
    */
    // public function isLoginCheck(string $request_password, string $encrypt_password);

    /**
    * Filter speciality from repository.
    *
    * @param array $filter
    * @param array $operator
    * @return bool
    */
    public function filtered(array $filter, array $operator);
}
