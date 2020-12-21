<?php

class Webshop
{
    private $id;
    private $name;
    private $url;
    private $parameters;
    private $linkTextPreference;
    private $backgroundColorPreference;
    private $textColorPreference;

    function __construct(
        $id = null,
        $name = null,
        $url = null,
        $linkTextPreference = null,
        $backgroundColorPreference = null,
        $textColorPreference = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;

        preg_match_all('/\\[\\[(.*?)]]/', $url, $parseUrlResult);
        $this->parameters = $parseUrlResult[1];

        $this->linkTextPreference = $linkTextPreference;
        $this->backgroundColorPreference = $backgroundColorPreference;
        $this->textColorPreference = $textColorPreference;
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

    public function getLinkTextPreference() {
        return $this->linkTextPreference;
    }

    public function getBackgroundColorPreference()
    {
        return $this->backgroundColorPreference;
    }

    public function getTextColorPreference()
    {
        return $this->textColorPreference;
    }
}