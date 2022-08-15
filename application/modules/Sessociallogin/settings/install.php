<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: install.php 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sessociallogin_Installer extends Engine_Package_Installer_Module {

  public function onPreinstall() {

    $db = $this->getDb();
    $plugin_currentversion = '4.10.3p8';

    //Check: Basic Required Plugin
    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_modules')
            ->where('name = ?', 'sesbasic');
    $results = $select->query()->fetchObject();
    if (empty($results)) {
      return $this->_error('<div class="global_form"><div><div><p style="color:red;">The required SocialEngineSolutions Basic Required Plugin is not installed on your website. Please download the latest version of this FREE plugin from <a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions.com</a> website.</p></div></div></div>');
    } else {
      $error = include APPLICATION_PATH . "/application/modules/Sesbasic/controllers/checkPluginVersion.php";
      if($error != '1') {
        return $this->_error($error);
      }
		}
    parent::onPreinstall();
  }

  public function onInstall() {

    $db = $this->getDb();
    parent::onInstall();
  }

  function onEnable() {

    $db = $this->getDb();

   $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '0' WHERE  `engine4_user_signup`.`class` ='User_Plugin_Signup_Account';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '0' WHERE  `engine4_user_signup`.`class` ='User_Plugin_Signup_Fields';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '0' WHERE  `engine4_user_signup`.`class` ='User_Plugin_Signup_Photo';");

    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '1' WHERE  `engine4_user_signup`.`class` ='Sessociallogin_Plugin_Signup_Account';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '1' WHERE  `engine4_user_signup`.`class` ='Sessociallogin_Plugin_Signup_Fields';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '1' WHERE  `engine4_user_signup`.`class` ='Sessociallogin_Plugin_Signup_Photo';");
    parent::onEnable();
  }

  public function onDisable() {

    $db = $this->getDb();
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '1' WHERE  `engine4_user_signup`.`class` ='User_Plugin_Signup_Account';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '1' WHERE  `engine4_user_signup`.`class` ='User_Plugin_Signup_Fields';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '1' WHERE  `engine4_user_signup`.`class` ='User_Plugin_Signup_Photo';");

    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '0' WHERE  `engine4_user_signup`.`class` ='Sessociallogin_Plugin_Signup_Account';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '0' WHERE  `engine4_user_signup`.`class` ='Sessociallogin_Plugin_Signup_Fields';");
    $db->query("UPDATE  `engine4_user_signup` SET  `enable` =  '0' WHERE  `engine4_user_signup`.`class` ='Sessociallogin_Plugin_Signup_Photo';");

    parent::onDisable();
  }
}
