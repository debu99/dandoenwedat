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
class Sesevent_Form_Post_Edit extends Engine_Form {

  public function init() {
    
    $this
            ->setTitle('Edit Post');

    $viewer = Engine_Api::_()->user()->getViewer();
    $settings = Engine_Api::_()->getApi('settings', 'core');

    $allowHtml = (bool) $settings->getSetting('sesevent_html', 0);
    $allowBbcode = (bool) $settings->getSetting('sesevent_bbcode', 0);

    if (!$allowHtml) {
      $filter = new Engine_Filter_HtmlSpecialChars();
    } else {
      $filter = new Engine_Filter_Html();
      $filter->setForbiddenTags();
      $allowed_tags = array_map('trim', explode(',', Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sesevent', 'commentHtml')));
      $filter->setAllowedTags($allowed_tags);
    }

    if ($allowHtml || $allowBbcode) {
			//UPLOAD PHOTO URL
      $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesbasic', 'controller' => 'index', 'action' => "upload-image"), 'default', true);

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
          'disableLoadDefaultDecorators' => true,
          'required' => true,
          'allowEmpty' => false,
          'decorators' => array(
              'ViewHelper'
          ),
          'editorOptions' => $editorOptions,
      ));
    } else {
      $this->addElement('textarea', 'body', array(
          'filters' => array(
              new Engine_Filter_Censor(),
          )
      ));
    }

    $this->addElement('Button', 'submit', array(
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'label' => 'Edit Post',
    ));

    $this->addElement('Cancel', 'cancel', array(
        'prependText' => ' or ',
        'label' => 'cancel',
        'link' => true,
        'href' => '',
        'onclick' => 'parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel'
            ), 'buttons');
  }

}
