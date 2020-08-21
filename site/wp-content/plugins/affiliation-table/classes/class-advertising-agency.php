<?php

class AdvertisingAgency
{
    private $name;
    private $label;

    function __construct($_name, $_label)
    {
        $this->name = $_name;
        $this->label = $_label;
    }

    public function getName() {
        return $this->name;
    }

    public function getLabel() {
        return $this->label;
    }
}