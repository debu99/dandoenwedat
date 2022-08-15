<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Note.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Form_Admin_Note extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this
            ->setTitle('Note')
            ->setDescription("Using this form below, you can add note.");

   
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

    $this->addElement('TinyMce', 'note', array(
        'label' => 'Note',
        'description' => "Please enter reply message.",
        'required' => true,
        'allowEmpty' => false,
        'editorOptions' => $editorOptions,
    ));
        
    $this->addElement('Button', 'execute', array(
        'label' => 'Add Note',
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