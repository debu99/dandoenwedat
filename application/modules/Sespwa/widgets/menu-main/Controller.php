<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Controller.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sespwa_Widget_MenuMainController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_main');

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();

    //Cover Photo work
    $cover = 0;
    if(Engine_Api::_()->getApi('core', 'sesbasic')->isModuleEnable(array('sesusercoverphoto')) && $viewerId) {
      if($viewer->coverphoto) {
        $this->view->menuinformationimg = $cover =	Engine_Api::_()->storage()->get($viewer->coverphoto, '');
        if($cover) {
          $this->view->menuinformationimg = $cover->getPhotoUrl();
        }
      }
    }
    if(empty($cover)) {
      $this->view->menuinformationimg = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.menuinformation.img', '');
    }

    $this->view->backgroundImg = Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.menu.img', '');

    $this->view->submenu = 1; //Engine_Api::_()->getApi('settings', 'core')->getSetting('sespwa.submenu', 1);

    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.browse', 1);
    if (!$require_check && !$viewerId) {
      $navigation->removePage($navigation->findOneBy('route', 'user_general'));
    }

    $this->view->storage = Engine_Api::_()->storage();

    $this->view->homelinksnavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_home');

    $this->view->settingNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_settings', array());

    $this->view->footernavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_footer');
    $this->view->socialsharenavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_social_sites');

    // Languages
    $languagePath = APPLICATION_PATH . '/application/languages';
    $translate    = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();

    //$currentLocale = Zend_Registry::get('Locale')->__toString();

    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if ($defaultLanguage == 'auto') {
        $defaultLanguage = 'en';
    }

    // Init default locale
    $localeObject = Zend_Registry::get('Locale');
    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $territories = Zend_Locale::getTranslationList('territory', $localeObject);

    $localeMultiOptions = array();
    foreach ($languageList as $key) {
        $dir = $languagePath . '/' . $key;
        if (!is_dir($dir)) {
            continue;
        }

        $languageName = null;
        if (!empty($languages[$key])) {
            $languageName = $languages[$key];
        } else {
            $tmpLocale = new Zend_Locale($key);
            $region = $tmpLocale->getRegion();
            $language = $tmpLocale->getLanguage();
            if (!empty($languages[$language]) && !empty($territories[$region])) {
                $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
            }
        }

        if ($languageName) {
            $localeMultiOptions[$key] = $languageName . '';
        }
    }

    if (!isset($localeMultiOptions[$this->view->defaultLanguage])) {
        $defaultLanguage = 'en';
    }

    $this->view->defaultLanguage = $defaultLanguage;
    $this->view->languageNameList = $localeMultiOptions;
  }

    public function getCacheKey()
    {
        //return true;
    }

    public function setLanguage()
    {
    }
}
