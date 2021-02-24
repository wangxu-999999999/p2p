<?php

namespace library;

class Base
{
    public function analyse($data)
    {
        if ($this->isJson($data)) {
            return json_decode($data, true);
        }
        return false;
    }

    public function isJson($str)
    {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
