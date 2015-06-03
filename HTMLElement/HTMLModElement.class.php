<?php
// https://html.spec.whatwg.org/multipage/semantics.html#htmlmodelement
// https://html.spec.whatwg.org/multipage/semantics.html#the-ins-element
// https://html.spec.whatwg.org/multipage/semantics.html#the-del-element

require_once 'HTMLElement.class.php';

class HTMLModElement extends HTMLElement {
    private $mCite;
    private $mDateTime;

    public function __construct($aTagName) {
        parent::__construct();

        $this->mCite = '';
        $this->mNodeName = $aTagName;
        $this->mTagName = $aTagName;
        $this->mDateTime = '';
    }

    public function __get($aName) {
        switch ($aName) {
            case 'cite':
                return $this->mCite;

            case 'dateTime':
                return $this->mDateTime;

            default:
                return parent::__get($aName);
        }
    }

    public function __set($aName, $aValue) {
        switch ($aName) {
            case 'cite':
                if (!is_string($aValue)) {
                    break;
                }

                $this->mCite = $aValue;
                $this->_updateAttributeOnPropertyChange($aName, $aValue);

                break;

            case 'dateTime':
                if (!is_string($aValue)) {
                    break;
                }

                $this->mDateTime = $aValue;
                $this->_updateAttributeOnPropertyChange($aName, $aValue);

                break;

            default:
                parent::__set($aName, $aValue);
        }
    }
}