<?php


class FlexiLinkField extends FormField
{


    /* necessary for passing TreeDropdownField ajax to composite field */
    private static $url_handlers = array(
        '$Action!/$ID' => '$Action'
    );

    private static $allowed_actions = array(
        'tree'
    );

    protected $composite_fields = array();

    public function __construct($name, $title = null, $value = null, $form = null)
    {
        $allowed_types = $this->stat('allowed_types');
        $field_types = $this->stat('field_types');

        if (empty($allowed_types)) {
            $allowed_types = array_keys($field_types);
        }

        $field = new DropdownField("{$name}[Type]", '', array_combine($allowed_types, $allowed_types));
        $field->setEmptyString('Please choose the Link Type');
        $this->composite_fields['Type'] = $field;

        foreach ($allowed_types as $type) {
            $def = $field_types[$type];
            $field_name = "{$name}[{$type}]";

            switch ($def['field']) {
                case 'TreeDropdownField':
                    $field =  new TreeDropdownField($field_name, '', 'SiteTree', 'ID', 'Title');
                    break;

                default:
                    $field = new TextField($field_name, '');
                    break;
            }


            $field->setDescription($def['description']);
            $field->addExtraClass('FlexiLinkCompositeField');

            $this->composite_fields[$type] = $field;
        }

        $this->setForm($form);

        parent::__construct($name, $title, $value, $form);
    }

    public function setForm($form)
    {
        foreach ($this->composite_fields as $type => $field) {
            $field->setForm($form);
        }
        return parent::setForm($form);
    }

    public function setName($name)
    {
        foreach ($this->composite_fields as $type => $field) {
            $field->setName("{$name}[{$type}]");
        }

        return parent::setName($name);
    }

    /**
     * @return string
     */
    public function Field($properties = array())
    {
        $module_dir = basename(dirname(dirname(__DIR__)));

        Requirements::javascript($module_dir . '/js/FlexiLinkField.js');
        Requirements::css($module_dir . '/css/FlexiLinkField.css');

        $str = '<div class="fieldgroup FlexiLinkField">';

        foreach ($this->composite_fields as $type => $field) {
            $str .= '<div class="fieldgroupField FlexiLinkField' . $type . '">';
            $str .= $field->FieldHolder();
            $str .= '</div>';
        }

        $str .= '</div>';

        return $str;
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            foreach (array_intersect_key($value, $this->composite_fields) as $key => $val) {
                $this->composite_fields[$key]->setValue($val);
            }
        } elseif ($value instanceof FlexiLink) {
            $type = $value->getLinkType();
            $this->composite_fields['Type']->setValue($type);

            if (isset($this->composite_fields[$type])) {
                $this->composite_fields[$type]->setValue($value->getLinkValue());
            }
        }
    }

    /**
     * SaveInto checks if set-methods are available and use them instead of setting the values directly. saveInto
     * initiates a new LinkField class object to pass through the values to the setter method.
     */
    public function saveInto(DataObjectInterface $dataObject)
    {
        $db_field = $dataObject->dbObject($this->name);

        $type = $this->composite_fields['Type']->Value();
        $db_field->setLinkType($type);

        if (isset($this->composite_fields[$type])) {
            $db_field->setLinkValue($this->composite_fields[$type]->Value());
        }
    }



    public function performReadonlyTransformation()
    {
        return new ReadonlyField($this->Name, $this->Title, $this->Value);
    }


    public function setReadonly($bool)
    {
        parent::setReadonly($bool);

        if ($bool) {
            foreach ($this->composite_fields as $field) {
                $field->performReadonlyTransformation();
            }
        }
    }



    public function tree($request)
    {
        $type = $request->getVar('type');

        return $this->composite_fields[$type]->tree($request);
    }
}
