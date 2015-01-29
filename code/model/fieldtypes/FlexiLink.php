<?php

class FlexiLink extends DBField implements CompositeDBField
{

    /**
     * @var string $getLinkType()
     */
    protected $link_type;

    /**
     * @var string $getLinkValue()
     */
    protected $link_value;

    /**
     * @var boolean $isChanged
     */
    protected $isChanged = false;

    /**
     * @param array
     */
    private static $composite_db = array(
        'Type' => 'Varchar',
        'Value' => 'Varchar(255)'
    );

    public function scaffoldFormField($title = null)
    {
        $field = new FlexiLinkField($this->name);
        return $field;
    }

    public function setValue($value, $record = null, $markChanged = true)
    {
        if ($value instanceof FlexiLink) {
            $this->setLinkType($value->getLinkType(), $markChanged);
            $this->setLinkValue($value->getLinkValue(), $markChanged);
        } elseif ($record && isset($record[$this->name . 'Type'])) {
            if ($record[$this->name . 'Type']) {
                $this->setLinkType($record[$this->name . 'Type'], $markChanged);

                if (isset($record[$this->name . 'Value']) && $record[$this->name . 'Value']) {
                    $this->setLinkValue($record[$this->name . 'Value'], $markChanged);
                }
            } else {
                $this->setLinkType($this->nullValue(), $markChanged);
                $this->setLinkValue($this->nullValue(), $markChanged);
            }
        } elseif (is_array($value)) {
            if (array_key_exists('Type', $value)) {
                $this->setLinkType($value['Type'], $markChanged);
            }
            if (array_key_exists('Value', $value)) {
                $this->setLinkType($value['Value'], $markChanged);
            }
        } else {
            //user_error('Invalid value in FlexiLink->setValue()', E_USER_ERROR);
        }
    }

    public function writeToManipulation(&$manipulation)
    {
        if ($type = $this->getLinkType()) {
            $manipulation['fields'][$this->name . 'Type'] = $this->prepValueForDB($type);
        } else {
            $manipulation['fields'][$this->name . 'Type'] = $this->nullValue();
        }

        if ($value = $this->getLinkValue()) {
            $manipulation['fields'][$this->name . 'Value'] = $this->prepValueForDB($value);
        } else {
            $manipulation['fields'][$this->name . 'Value'] = $this->nullValue();
        }
    }

    public function addToQuery(&$query)
    {
        parent::addToQuery($query);
        $query->selectField(sprintf('"%sType"', $this->name));
        $query->selectField(sprintf('"%sValue"', $this->name));
    }

    public function requireField()
    {
        $fields = $this->compositeDatabaseFields();
        if ($fields)
            foreach ($fields as $name => $type) {
                DB::requireField($this->tableName, $this->name . $name, $type);
            }
    }

    /**
     * @return string
     */
    public function getLinkType()
    {
        return $this->link_type;
    }

    /**
     * @return string
     */
    public function getLinkValue()
    {
        return $this->link_value;
    }

    public function compositeDatabaseFields()
    {
        return self::$composite_db;
    }

    /**
     * @return boolean
     */
    public function isChanged()
    {
        return $this->isChanged;
    }

    public function exists()
    {
        return ($this->getLinkValue());
    }

    /**
     * @param string $type
     */
    public function setLinkType($type, $markChanged = true)
    {
        $this->link_type = (string) $type;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    /**
     * @param string $value
     */
    public function setLinkValue($value, $markChanged = true)
    {
        $this->link_value = (string) $value;
        if ($markChanged) {
            $this->isChanged = true;
        }
    }

    /* Template Methods
     ******************/
    public function Type()
    {
        return $this->getLinkType();
    }

    public function Value()
    {
        return $this->getLinkValue();
    }

    public function URL()
    {
        if ($value = $this->getLinkValue()) {

            switch ($this->getLinkType()) {
                case 'Page':
                    if ($page = DataObject::get_by_id('SiteTree', (int) $value)) {
                        return $page->Link();
                    }
                    break;

                case 'YouTubeID':
                    return '//www.youtube.com/embed/' . $value;
                    break;

                default:
                    return $value;
                    break;
            }
        }
    }
}
