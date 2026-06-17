<?php

namespace App\Services;

use App\Models\UserDependent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserDependentService
{

    /**
     * create a user dependent in the storage
     */
    public function add(array $data): UserDependent|Exception
    {
        try {
           
            $userDependent = UserDependent::create($data);
            return $userDependent;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * update a particular user dependent info From the Storage
     */

    public function update(UserDependent $userDependent, array $data): UserDependent|Exception
    {
        try {
            $userDependent->update($data);
            return $userDependent;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
