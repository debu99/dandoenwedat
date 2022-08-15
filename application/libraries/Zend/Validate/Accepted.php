<?php
class Zend_Validate_Accepted extends Zend_Validate_Abstract
{
    const NOT_ACCEPTED = 'notAccepted';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ACCEPTED => "You have to accept the terms and conditions.",
    );


    public function isValid($value)
    {
        if ($value != 1) {
            $this->_error(self::NOT_ACCEPTED);
            return false;
        }

        return true;
    }
}
