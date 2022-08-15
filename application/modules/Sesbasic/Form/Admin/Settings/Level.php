<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesvideo
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Level.php 2015-10-11 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesbasic_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {

    parent::init();		
    // My stuff
    $this->setTitle('Member Level Settings For Video Lightbox')
         ->setDescription(' .. ');


		// Element: lightbox type
      $this->addElement('Radio', 'videoviewer', array(
          'label' => 'Lightbox Viewer Type',
          'description' => 'Choose the Video lightbox viewer type to be enabled for the members of this level.',
          'multiOptions' => array(
              0 => 'Basic Viewer',
              1 => 'Advanced Viewer'
          ),
          'value' => 1,
      ));
  }

}
