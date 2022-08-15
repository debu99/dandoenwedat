<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: MiniMenuIcons.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Form_Admin_MiniMenuIcons extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Manage Mini Menu Icons')
            ->setDescription('Here, you can add icons for the Main Navigation Menu Items of your website. You can also edit and delete the icons.');

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

    //Notification Icons
    $minimenu_notification_normal = $settings->getSetting('sespwaminimenu.notification.normal', '');
    $minimenu_notification_mouseover = $settings->getSetting('sespwaminimenu.notification.mouseover', 0);
    $this->addElement('Dummy', 'sespwaminimenu_icons', array(
        'label' => 'Notifications Icons',
    ));
    $this->addElement('File', 'sespwaminimenu_notification_normal', array(
        'label' => 'Normal Icon',
    ));
    $this->sespwaminimenu_notification_normal->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');
    if ($minimenu_notification_normal) {
      $img_path = Engine_Api::_()->storage()->get($minimenu_notification_normal, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'sespwaminimenu_notification_normal_preview', array(
            'src' => $path,
            'width' => 17,
            'height' => 17,
        ));
      }
      $this->addElement('Checkbox', 'sespwaminimenu_notification_normalremove', array(
          'label' => 'Remove this icon and apply default icon.'
      ));
    }

    $this->addElement('File', 'sespwaminimenu_notification_mouseover', array(
        'label' => 'Mouse Over Icon',
    ));
    $this->sespwaminimenu_notification_mouseover->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');
    if ($minimenu_notification_mouseover) {
      $img_path = Engine_Api::_()->storage()->get($minimenu_notification_mouseover, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'sespwaminimenu_notification_mouseover_preview', array(
            'src' => $path,
            'width' => 17,
            'height' => 17,
        ));
      }
      $this->addElement('Checkbox', 'sespwaminimenu_notification_mouseoverremove', array(
          'label' => 'Remove this icon and apply default icon.'
      ));
    }

    //Message icons
    $minimenu_message_normal = $settings->getSetting('sespwaminimenu.message.normal', 0);
    $minimenu_message_mouseover = $settings->getSetting('sespwaminimenu.message.mouseover', 0);
    $this->addElement('Dummy', 'sespwaminimenu_message_icons', array(
        'label' => 'Messages Icons',
    ));
    $this->addElement('File', 'sespwaminimenu_message_normal', array(
        'label' => 'Normal Icon',
    ));
    $this->sespwaminimenu_message_normal->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');

    if ($minimenu_message_normal) {
      $img_path = Engine_Api::_()->storage()->get($minimenu_message_normal, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'sespwaminimenu_message_normal_preview', array(
            'src' => $path,
            'width' => 17,
            'height' => 17,
        ));
      }
      $this->addElement('Checkbox', 'sespwaminimenu_message_normalremove', array(
          'label' => 'Remove this icon and apply default icon.'
      ));
    }

    $this->addElement('File', 'sespwaminimenu_message_mouseover', array(
        'label' => 'Mouse Over Icon',
    ));
    $this->sespwaminimenu_message_mouseover->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');
    if ($minimenu_message_mouseover) {
      $img_path = Engine_Api::_()->storage()->get($minimenu_message_mouseover, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'sespwaminimenu_message_mouseover_preview', array(
            'src' => $path,
            'width' => 17,
            'height' => 17,
        ));
      }
      $this->addElement('Checkbox', 'sespwaminimenu_message_mouseoverremove', array(
          'label' => 'Remove this icon and apply default icon.'
      ));
    }


    //Friend Requests
    $minimenu_frrequest_normal = $settings->getSetting('sespwaminimenu.frrequest.normal', 0);
    $minimenu_frrequest_mouseover = $settings->getSetting('sespwaminimenu.frrequest.mouseover', 0);
    $this->addElement('Dummy', 'sespwaminimenu_frrequest_icons', array(
        'label' => 'Friend Requests Icons',
    ));
    $this->addElement('File', 'sespwaminimenu_frrequest_normal', array(
        'label' => 'Normal Icon',
    ));
    $this->sespwaminimenu_frrequest_normal->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');
    if ($minimenu_frrequest_normal) {
      $img_path = Engine_Api::_()->storage()->get($minimenu_frrequest_normal, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'sespwaminimenu_frrequest_normal_preview', array(
            'src' => $path,
            'width' => 17,
            'height' => 17,
        ));
      }
      $this->addElement('Checkbox', 'sespwaminimenu_frrequest_normalremove', array(
          'label' => 'Remove this icon and apply default icon.'
      ));
    }

    $this->addElement('File', 'sespwaminimenu_frrequest_mouseover', array(
        'label' => 'Mouse Over Icon',
    ));
    $this->sespwaminimenu_frrequest_mouseover->addValidator('Extension', false, 'jpg,png,gif,jpeg,JPG,PNG,GIF,JPEG');
    if ($minimenu_frrequest_mouseover) {
      $img_path = Engine_Api::_()->storage()->get($minimenu_frrequest_mouseover, '')->getPhotoUrl();
      $path = 'http://' . $_SERVER['HTTP_HOST'] . $img_path;
      if (isset($path) && !empty($path)) {
        $this->addElement('Image', 'sespwaminimenu_frrequest_mouseover_preview', array(
            'src' => $path,
            'width' => 17,
            'height' => 17,
        ));
      }
      $this->addElement('Checkbox', 'sespwaminimenu_frrequest_mouseoverremove', array(
          'label' => 'Remove this icon and apply default icon.'
      ));
    }

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
