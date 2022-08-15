<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: ContentFromSimple.php 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesbasic_Form_ContentFormSimple extends Engine_Form {

  public function init() {

    $languages = Zend_Locale::getTranslationList('language', Zend_Registry::get('Locale'));
    $languageList = Zend_Registry::get('Zend_Translate')->getList();

    foreach ($languageList as $key => $language) {
      if ($language == 'en')
        $coulmnName = 'bodysimple';
      else
        $coulmnName = $language . '_bodysimple';

      $this->addElement('Textarea', $coulmnName, array(
          'label' => 'Content for ' . $languages[$key],
      ));
    }

    $this->addElement('Radio', 'show_content', array(
        'label' => 'Do you want to show this block to non-logged in users?',
        'multiOptions' => array("1" => "Yes", "0" => "No"),
        'value' => '1'
    ));
  }

}