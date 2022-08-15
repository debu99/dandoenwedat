<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Edit.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesevent_Form_Edit extends Sesevent_Form_Create
{
    protected $_timeChangeTitleRemain;

    /**
     * @return mixed
     */
    public function getTimeChangeTitleRemain()
    {
        return $this->_timeChangeTitleRemain;
    }

    public function setTimeChangeTitleRemain($_timeChangeTitleRemain)
    {
        $this->_timeChangeTitleRemain = $_timeChangeTitleRemain;
    }

    public function init()
    {
        parent::init();
        $view = Zend_Registry::get('Zend_View');

        $this->removeElement('cancel');
        $this->setTitle('Edit Event');

        $this->getElement('title')->setDescription($view->translate('You have %d times remain to change event title.', $this->_timeChangeTitleRemain));
  }

}
