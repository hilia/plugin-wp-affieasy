<?php

class Table
{
    private $defaultTableColumnNumber = 4;
    private $defaultHeaderBackgroundColor = '#707070';
    private $defaultHeaderTextColor = '#ffffff';
    private $defaultHeaderFontWeight = 'bold';
    private $defaultHeaderFontSize = 18;

    private $id;
    private $name;
    private $headerType;
    private $headerOptions;
    private $content;

    function __construct($id = null, $name = null, $headerType = null, $headerOptions = null, $content = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->headerType = $headerType;
        $this->headerOptions = $headerOptions;
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

    public function getHeaderType()
    {
        return $this->headerType;
    }

    public function getHeaderOptions() {
        return $this->headerOptions;
    }

    public function setHeaderOptions($headerOptions) {
        return $this->headerOptions = $headerOptions;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getTag()
    {
        return '[' . Constants::TABLE_TAG . ' id=' . $this->id . ']';
    }

    public function getCellCount()
    {
        if ($this->id == null) {
            return 0;
        }

        return (in_array($this->headerType, array('COLUMN_HEADER', 'BOTH')) ?
                count($this->content) - 1 :
                count($this->content)) * count($this->content[0]);
    }

    public function initDefaultContent()
    {
        $this->headerType = 'COLUMN_HEADER';

        $this->headerOptions = (object)[
          'background' => $this->defaultHeaderBackgroundColor,
          'color' => $this->defaultHeaderTextColor,
          'font-weight' => $this->defaultHeaderFontWeight,
          'font-size' => $this->defaultHeaderFontSize
        ];

        $headerRow = array();

        for ($i = 0; $i < $this->defaultTableColumnNumber; $i++) {
            array_push($headerRow, (object)[
                'type' => 'html',
                'value' => '',
            ]);
        }

        $this->content = array($headerRow);
    }
}