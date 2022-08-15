<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Subscription.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Signup_Subscription extends Engine_Form
{
  public function init()
  {
    // Get step and step number
    $stepTable = Engine_Api::_()->getDbtable('signup', 'user');
    $stepSelect = $stepTable->select()->where('class = ?', str_replace('_Form_Admin_', '_Plugin_', get_class($this)));
    $step = $stepTable->fetchRow($stepSelect);
    $stepNumber = 1 + $stepTable->select()
      ->from($stepTable, new Zend_Db_Expr('COUNT(signup_id)'))
      ->where('`order` < ?', $step->order)
      ->query()
      ->fetchColumn();
    $stepString = $this->getView()->translate('Step %1$s', $stepNumber);
    $this->setDisableTranslator(true);
    // Custom
    $this->setTitle($this->getView()->translate('%1$s: Choose Subscription', $stepString));
    // Element: enable
    $this->addElement('Radio', 'enable', array(
      'label' => 'Choose Subscription Plan',
      'description' => 'Do you want your users to be able to choose a ' .
        'subscription plan upon signup?',
      'multiOptions' => array(
        '1' => 'Yes, give users the option to choose upon signup.',
        '0' => 'No, do not allow users to choose upon signup.',
      ),
    ));
    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

    // Populate
    $this->populate(array(
      'enable' => $step->enable,
    ));
  }
}
