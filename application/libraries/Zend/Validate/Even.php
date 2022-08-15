<?php
class Zend_Validate_Even extends Zend_Validate_Abstract
{
    const INVALID   = 'evenInvalid';
    const NOT_EVEN = 'notEven';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given. Integer expected",
        self::NOT_EVEN => "'%value%' does not appear to be an even number",
    );


    public function isValid($value)
    {
        if (!is_int((int)$value)) {
            $this->_error(self::INVALID);
            return false;
        }

        if ($value % (int)2 != 0) {
            $this->_error(self::NOT_EVEN);
            return false;
        }

        return true;
    }
}
