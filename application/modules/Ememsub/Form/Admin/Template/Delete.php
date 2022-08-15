<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Delete.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Template_Delete extends Engine_Form {

  public function init() {
		  $this->setTitle('Delete Template?')
            ->setDescription('Are you sure to delete this Template? It will not be recovered after being deleted.');
      $this->addElement('Button', 'save', array(
        'label' => 'Delete',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
      ));
  }
}
