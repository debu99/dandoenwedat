<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Edit.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Form_Admin_Template_Edit extends Ememsub_Form_Admin_Template_Create {

  public function init() {
    parent::init();
		$this->setTitle('Edit Settings');
  }
}
