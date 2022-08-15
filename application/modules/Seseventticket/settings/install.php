<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventticket
 * @package    Seseventticket
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: install.php 2016-03-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventticket_Installer extends Engine_Package_Installer_Module {

  public function onPreinstall() {

    $db = $this->getDb();

    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_modules')
            ->where('name = ?', 'sesevent')
            ->where('enabled = ?', 1);
    $sesevent_Check = $select->query()->fetchObject();

    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_modules')
            ->where('name = ?', 'sesevent');
    $seseventCheck = $select->query()->fetchAll();

    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_settings')
            ->where('name = ?', 'sesevent.pluginactivated')
            ->limit(1);
    $event_activate = $select->query()->fetchAll();

    if(!empty($sesevent_Check) && !empty($event_activate[0]['value'])) {
      $plugin_currentversion = '4.9.0';
      $error = include APPLICATION_PATH . "/application/modules/Sesbasic/controllers/checkPluginVersion.php";
      if($error != '1') {
        return $this->_error($error);
      }
		} elseif(!empty($sesevent_Check) && empty($event_activate[0]['value'])) {
      return $this->_error('<div class="global_form"><div><div><p style="color:red;">The "<a href="http://www.socialenginesolutions.com/social-engine/advanced-events-plugin/" target="_blank">Advanced Events Plugin</a>" is installed on your website, but is not yet activated. So, please first activate it before installing the Advanced Events - Tickets Extension.</p></div></div></div>');
		} elseif(!empty($seseventCheck) && empty($sesevent_Check)) {
      return $this->_error('<div class="global_form"><div><div><p style="color:red;">The "<a href="http://www.socialenginesolutions.com/social-engine/advanced-events-plugin/" target="_blank">Advanced Events Plugin</a>" is installed on your website, but is not yet enabled. So, please first enable it from the "Manage" >> "Packages & Plugins" section to proceed further.</p></div></div></div>');
		} elseif(empty($seseventCheck)) {
      return $this->_error('<div class="global_form"><div><div><p style="color:red;">The required "<a href="http://www.socialenginesolutions.com/social-engine/advanced-events-plugin/" target="_blank">Advanced Events Plugin</a>" is not installed on your website. Please download the latest version of "<a href="http://www.socialenginesolutions.com/social-engine/advanced-events-plugin/" target="_blank">Advanced Events Plugin</a>" from <a href="http://www.socialenginesolutions.com" target="_blank">SocialEngineSolutions.com</a> website.</p></div></div></div>');
		}

    parent::onPreinstall();
  }

	public function onInstall() {

    $db = $this->getDb();
    parent::onInstall();
  }

  public function onEnable() {
		$db = $this->getDb();
		$db->query("UPDATE `engine4_core_menuitems` SET `enabled` = '1' WHERE `engine4_core_menuitems`.`name` = 'sesevent_admin_main_seseventtickets';");
    parent::onEnable();
  }

  public function onDisable() {
		$db = $this->getDb();
		$db->query("UPDATE `engine4_core_menuitems` SET `enabled` = '0' WHERE `engine4_core_menuitems`.`name` = 'sesevent_admin_main_seseventtickets';");
    parent::onDisable();
  }
}
