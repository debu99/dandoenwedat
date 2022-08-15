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
class Sesevent_Form_Admin_Slide_Edit extends Sesevent_Form_Admin_Slide_Create {

  public function init() {
    parent::init();

    $this->setTitle('Edit This Slide')->setDescription('Edit this slide here.');
    $this->submit->setLabel('Save Changes');
  }

}
