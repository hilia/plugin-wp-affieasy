<?php

namespace affieasy;

class AFES_Link
{
    private $id;
    private $webshopId;
    private $label;
    private $category;
    private $parameters;
    private $url;
    private $noFollow;
    private $openInNewTab;

    function __construct(
        $id = null,
        $webshopId = null,
        $label = null,
        $category = null,
        $parameters = null,
        $url = null,
        $noFollow = null,
        $openInNewTab = null)
    {
        $this->id = $id;
        $this->webshopId = $webshopId;
        $this->label = $label;
        $this->category = $category;
        $this->parameters = $parameters;
        $this->url = $url;
        $this->noFollow = $noFollow;
        $this->openInNewTab = $openInNewTab;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getWebshopId()
    {
        return $this->webshopId;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function isNoFollow()
    {
        return $this->noFollow;
    }

    public function isOpenInNewTab()
    {
        return $this->openInNewTab;
    }
}