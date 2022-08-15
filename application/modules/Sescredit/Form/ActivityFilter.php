<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ActivityFilter.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_ActivityFilter extends Engine_Form {

  public function init() {
    $this->setMethod('get');
    $this->addElement('select', 'module', array(
        'label' => 'Modules',
        'multiOptions' => array(''),
        'description' => '',
        'value' => '',
        'onchange' => 'fetchLevelSettings(this);',
    ));
  }

}
