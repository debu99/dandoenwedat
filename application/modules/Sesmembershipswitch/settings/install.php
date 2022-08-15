<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: install.php  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesmembershipswitch_Installer extends Engine_Package_Installer_Module {

//   public function onPreinstall() {
//
//     $db = $this->getDb();
//     $plugin_currentversion = '4.10.3p15';
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

  public function onInstall() {
    $db = $this->getDb();
    $db->query("UPDATE engine4_core_tasks SET plugin = 'Sesmembershipswitch_Plugin_Task_Cleanup',module='sesmembershipswitch',timeout='7200' WHERE plugin = 'Payment_Plugin_Task_Cleanup'");
    parent::onInstall();
  }

  public function onDisable(){
    $db = $this->getDb();
    $db->query("UPDATE engine4_core_tasks SET plugin = 'Payment_Plugin_Task_Cleanup',module='payment' WHERE plugin = 'Sesmembershipswitch_Plugin_Task_Cleanup'");
    parent::onDisable();
  }

  public function onEnable(){
    $db = $this->getDb();
    $db->query("UPDATE engine4_core_tasks SET plugin = 'Sesmembershipswitch_Plugin_Task_Cleanup',module='sesmembershipswitch' WHERE plugin = 'Payment_Plugin_Task_Cleanup'");
    parent::onEnable();
  }
}
