<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Edit.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sescredit_Form_Admin_Badge_Edit extends Sescredit_Form_Admin_Badge_Add {

  function init() {
    parent::init();
    $this->submit->setLabel('Save Changes');
  }

}
