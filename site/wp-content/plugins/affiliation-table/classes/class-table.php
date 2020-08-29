<?php

class Table
{
    private $defaultTableColumnNumber = 4;

    private $id;
    private $name;
    private $withHeader;
    private $content;

    function __construct($id = null, $name = null, $withHeader = null, $content = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->withHeader = $withHeader == 'on' || $withHeader == 1 ? 1 : 0;
        $this->content = $content;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isWithHeader()
    {
        return $this->withHeader;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function initDefaultContent() {
        $headerRow = array();

        for ($i = 0; $i < $this->defaultTableColumnNumber; $i++) {
            array_push($headerRow, (object) [
                'type' => 'html',
                'value' => '',
            ]);
        }

        $this->content = array($headerRow);
    }
}