<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Filter.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Form_Admin_Settings_Filter extends Engine_Form {

  public function init() {
    $this->setMethod('get');
    $levelOptions = array();
    $levelValues = array();
    foreach (Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level) {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
      if($level_id == $level->level_id) 
        continue;
      $levelOptions[$level->level_id] = $level->getTitle();
      $levelValues[] = $level->level_id;
    }
    $this->addElement('select', 'member_level', array(
        'label' => 'Member Levels',
        'multiOptions' => $levelOptions,
        //'description' => 'Choose the Member Levels to which this Page will be displayed.',
        'value' => $levelValues,
        'onchange' => 'fetchLevelSettings(this);',
    ));
    $this->addElement('select', 'plugin', array(
        'label' => 'Plugin',
        'multiOptions' => array(''),
        'description' => '',
        'value' => '',
        'onchange' => 'fetchLevelSettings(this);',
    ));
  }

}
