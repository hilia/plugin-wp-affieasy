<?php

class Webshop
{
    private $id;
    private $name;
    private $url;
    private $parameters;

    function __construct($id = null, $name = null, $url = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;

        preg_match_all('/\\[\\[(.*?)]]/', $url, $parseUrlResult);
        $this->parameters = $parseUrlResult[1];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}