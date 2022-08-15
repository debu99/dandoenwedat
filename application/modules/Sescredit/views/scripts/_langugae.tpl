<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _language.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php 
  $languageNameList = array();
  $languageDataList = Zend_Locale_Data::getList(null, 'language');
  $territoryDataList = Zend_Locale_Data::getList(null, 'territory');
  $languageList = Zend_Registry::get('Zend_Translate')->getList();
  foreach ($languageList as $localeCode) {
    $languageNameList[$localeCode] = Engine_String::ucfirst(Zend_Locale::getTranslation($localeCode, 'language', $localeCode));
    if (empty($languageNameList[$localeCode])) {
      if (false !== strpos($localeCode, '_')) {
        list($locale, $territory) = explode('_', $localeCode);
      } else {
        $locale = $localeCode;
        $territory = null;
      }
      if (isset($territoryDataList[$territory]) && isset($languageDataList[$locale])) {
        $languageNameList[$localeCode] = $territoryDataList[$territory] . ' ' . $languageDataList[$locale];
      } else if (isset($territoryDataList[$territory])) {
        $languageNameList[$localeCode] = $territoryDataList[$territory];
      } else if (isset($languageDataList[$locale])) {
        $languageNameList[$localeCode] = $languageDataList[$locale];
      } else {
        continue;
      }
    }
  }
  $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
  $languageNameList = array_merge(array(
      $defaultLanguage => $defaultLanguage
          ), $languageNameList);
?>