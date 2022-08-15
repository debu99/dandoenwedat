<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Mail.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Admin_Mail extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this
            ->setTitle('Reply to Entry')
            ->setDescription("Using this form below, you can reply to user who has contacted you through the form.");

    $this->addElement('Text', 'from_address', array(
        'label' => 'From:',
        'value' => $settings->getSetting('core.mail.from', 'admin@' . $_SERVER['HTTP_HOST']),
        'disable' => true,
        'validators' => array(
            'EmailAddress',
        )
    ));

    $this->addElement('Text', 'from_name', array(
        'label' => 'From (name):',
        'disable' => true,
        'value' => $settings->getSetting('core.mail.name', 'Site Admin'),
    ));

    $this->addElement('Text', 'subject', array(
        'label' => 'Subject:',
        'required' => true,
        'allowEmpty' => false,
    ));

    //upload photo URL
    $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesbasic', 'controller' => 'manage', 'action' => "upload-image"), 'admin_default', true);
    $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';
    $editorOptions = array(
        'upload_url' => $upload_url,
        'html' => (bool) $allowed_html,
    );
    if (!empty($upload_url)) {
      $editorOptions['plugins'] = array(
          'table', 'fullscreen', 'media', 'preview', 'paste',
          'code', 'image', 'textcolor', 'jbimages', 'link'
      );

      $editorOptions['toolbar1'] = array(
          'undo', 'redo', 'removeformat', 'pastetext', '|', 'code',
          'media', 'image', 'jbimages', 'link', 'fullscreen',
          'preview'
      );
    }

    $this->addElement('TinyMce', 'body', array(
        'label' => 'body',
        'description' => "Please enter reply message.",
        'required' => true,
        'allowEmpty' => false,
        'editorOptions' => $editorOptions,
    ));
    
     $this->addElement('Checkbox', 'note', array(
        'label' => 'Add this reply to the Note.',
        'description' => "",
    ));
    
    
    $this->addElement('Button', 'execute', array(
        'label' => 'Reply',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
        'prependText' => ' or ',
        'label' => 'cancel',
        'link' => true,
        'href' => '',
        'onclick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        ),
    ));

    $this->addDisplayGroup(array(
        'execute',
        'cancel'
            ), 'buttons');
  }

}