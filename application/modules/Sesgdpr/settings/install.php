<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: install.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesgdpr_Installer extends Engine_Package_Installer_Module {

//   public function onPreinstall() {
//
//     $db = $this->getDb();
//     $plugin_currentversion = '4.10.3p21';
//
//     //Check: Basic Required Plugin
//     $select = new Zend_Db_Select($db);
//     $select->from('engine4_core_modules')
//             ->where('name = ?', 'sesbasic');
//     $results = $select->query()->fetchObject();
//     if (empty($results)) {
//       return $this->_error('<div class="global_form"><div><div><p style="color:red;">The required SocialEngineSolutions Basic Required Plugin is not installed on your website. Please download the latest version of this FREE plugin from <a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions.com</a> website.</p></div></div></div>');
//     } else {
//       $error = include APPLICATION_PATH . "/application/modules/Sesbasic/controllers/checkPluginVersion.php";
//       if($error != '1') {
//         return $this->_error($error);
//       }
// 		}
//     parent::onPreinstall();
//   }

  public function onPostInstall() {
    //WORK FOR GOOGLE FONT LOAD AND WRITE IN TO XML FILE
    //Taken this code from here: /application/modules/Activity/controllers/NotificationsController.php
    $front = Zend_Controller_Front::getInstance();
    $action = $front->getRequest()->getActionName();
    $controller = $front->getRequest()->getControllerName();
    if ($controller == 'manage' && ($action == 'query' || $action == 'install')) {
      $view = new Zend_View();
      $installURL =(!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on' ? "https://" : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('install/', '', $view->url(array(), 'default', true));
      $redirectorHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
      if ($action != 'install')
        $redirectorHelper->gotoUrl($installURL . 'admin/sesgdpr/settings/usesettingschange/referralurl/query');
      else
        $redirectorHelper->gotoUrl($installURL . 'admin/sesgdpr/settings/usesettingschange/referralurl/install');
    }
  }

  public function onInstall() {

    $db = $this->getDb();
    parent::onInstall();
  }
}
