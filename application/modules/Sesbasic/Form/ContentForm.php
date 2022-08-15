<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ContentForm.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Form_ContentForm extends Engine_Form {

  public function init() {

    $upload_url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sesbasic', 'controller' => 'manage', 'action' => "upload-image"), 'admin_default', true);

    $allowed_html = 'strong, b, em, i, u, strike, sub, sup, p, div, pre, address, h1, h2, h3, h4, h5, h6, span, ol, li, ul, a, img, embed, br, hr';

    $editorOptions = array(
        'upload_url' => $upload_url,
        'html' => (bool) $allowed_html,
    );

    if (!empty($upload_url))
    {
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

    $languages = Zend_Locale::getTranslationList('language', Zend_Registry::get('Locale'));
    $languageList = Zend_Registry::get('Zend_Translate')->getList();

    foreach ($languageList as $key => $language) {
      if ($language == 'en')
        $coulmnName = 'body';
      else
        $coulmnName = $language . '_body';

      $this->addElement('TinyMce', $coulmnName, array(
          'label' => 'Content for ' . $languages[$key],
          'required' => true,
          'editorOptions' => $editorOptions,
          'filters' => array(
              new Engine_Filter_Censor(),
              new Engine_Filter_Html(array('AllowedTags' => $allowed_html))),
      ));
    }

    $this->addElement('Text', 'content_height', array(
        'label' => 'Height',
        'description' => '',
        'value' => '',
    ));
    $this->addElement('Text', 'content_width', array(
        'label' => 'Width',
        'description' => '',
        'value' => '',
    ));
    $this->addElement('Text', 'content_class', array(
        'label' => 'CSS Class',
        'description' => '',
        'value' => '',
    ));

    $this->addElement('Radio', 'show_content', array(
        'label' => 'Do you want to show this block to non-logged in users?',
        'multiOptions' => array("1" => "Yes", "0" => "No"),
        'value' => '1'
    ));
  }

}