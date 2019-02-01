<?php

class MockClass
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
}