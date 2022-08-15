<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Level.php  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesfeedgif_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {

    parent::init();

    // My stuff
    $this
      ->setTitle('Member Level Settings')
      ->setDescription("These settings are applied as per member level. Start by selecting the member level you want to modify, then adjust the settings for that level from below.");

    if( !$this->isPublic() ) {

      $this->addElement('Radio', 'enablefeedgif', array(
        'label' => 'Enable GIF Images in Status Update',
        'description' => "Do you want users of this level to to share GIF images in their status updates? (If you do not want the GIF images in status updates at all, then configure it from the Global settings on your website.)",
        'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
        'value' => 1,
      ));
      $this->getElement('enablefeedgif')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

      $this->addElement('Radio', 'enablecommentgif', array(
        'label' => 'Enable GIF Images in Comments',
        'description' => "Do you want users of this level to to share GIF images in their comments and replies? (If you do not want the GIF images in comments and replies at all, then configure it from the Global settings on your website.)",
        'multiOptions' => array(
            '1' => 'Yes',
            '0' => 'No',
        ),
        'value' => 1,
      ));
      $this->getElement('enablecommentgif')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    }
  }
}
