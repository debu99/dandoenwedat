<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: install.php 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

class Ememsub_Installer extends Engine_Package_Installer_Module {
  public function onInstall() { 
    $db = $this->getDb();
    $db->query("UPDATE `engine4_user_signup` SET `enable`= 0 WHERE `class` = 'Payment_Plugin_Signup_Subscription';");
    $db->query("UPDATE `engine4_user_signup` SET `enable`= 1 WHERE `class` = 'Ememsub_Plugin_Signup_Subscription';");
    parent::onInstall();
  }
   public function onDisable() { 
    $db = $this->getDb();
    $db->query("UPDATE `engine4_user_signup` SET `enable`= 1 WHERE `class` = 'Payment_Plugin_Signup_Subscription';");
    $db->query("UPDATE `engine4_user_signup` SET `enable`= 0 WHERE `class` = 'Ememsub_Plugin_Signup_Subscription';");
    $db->commit();
    parent::onDisable();
  }
  function onEnable() {  
    $db = $this->getDb();
    $db->query("UPDATE `engine4_user_signup` SET `enable`= 0 WHERE `class` = 'Payment_Plugin_Signup_Subscription';");
    $db->query("UPDATE `engine4_user_signup` SET `enable`= 1 WHERE `class` = 'Ememsub_Plugin_Signup_Subscription';");
    $db->commit();
    parent::onEnable();
  }
}
