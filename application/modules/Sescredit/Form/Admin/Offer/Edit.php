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
class Sescredit_Form_Admin_Offer_Edit extends Sescredit_Form_Admin_Offer_Create {

  public function init() {
    parent::init();

    $this->setTitle('Edit This Offer')->setDescription('Below, edit this Page’s content and other parameters.');
    $this->save->setLabel('Save Changes');
  }

}
