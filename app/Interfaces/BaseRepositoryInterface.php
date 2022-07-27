<?php

namespace App\Interfaces;

interface BaseRepositoryInterface
{
    public function getWhere($column, $value, array $related = null);

    public function create(array $data);

    public function update($id,array $data);

    public function delete($id);

    public function deleteWhere($column, $value);

}
