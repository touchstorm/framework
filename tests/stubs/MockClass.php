<?php

require_once 'MockContract.php';

class MockClass implements MockContract
{
    private $value;

    public function __construct($fooValue = '')
    {
        $this->value = $fooValue;
    }

    public function give()
    {
        return $this->value;
    }

    public function find()
    {
        return 'found';
    }
}