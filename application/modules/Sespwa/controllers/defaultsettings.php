<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

$db = Zend_Db_Table_Abstract::getDefaultAdapter();

$sesbasic_menusicons_table_exist = $db->query('SHOW TABLES LIKE \'engine4_sesbasic_menusicons\'')->fetch();
if($sesbasic_menusicons_table_exist) {
    $sespwa_icon_id = $db->query('SHOW COLUMNS FROM engine4_sesbasic_menusicons LIKE \'sespwa_icon_id\'')->fetch();
    if (empty($sespwa_icon_id)) {
        $db->query('ALTER TABLE `engine4_sesbasic_menusicons` ADD `sespwa_icon_id` INT(11) NULL DEFAULT "0";');
    }
}

//Header Default Work
$content_id = $this->widgetCheck(array('widget_name' => 'sespwa.header', 'page_id' => '1'));

$parent_content_id = $db->select()
        ->from('engine4_sespwa_content', 'content_id')
        ->where('type = ?', 'container')
        ->where('page_id = ?', '1')
        ->where('name = ?', 'main')
        ->limit(1)
        ->query()
        ->fetchColumn();
if (empty($content_id)) {
  $db->query('DELETE FROM `engine4_sespwa_content` WHERE `engine4_sespwa_content`.`page_id` = "1" AND `engine4_sespwa_content`.`type` = "widget";');
  $db->insert('engine4_sespwa_content', array(
      'type' => 'widget',
      'name' => 'sespwa.header',
      'page_id' => 1,
      'parent_content_id' => $parent_content_id,
      'order' => 20,
  ));
}

//Footer Default Work
$db->query('DELETE FROM `engine4_sespwa_content` WHERE `engine4_sespwa_content`.`page_id` = "2" AND `engine4_sespwa_content`.`type` = "widget";');
// $footerMenu = $this->widgetCheck(array('widget_name' => 'core.menu-footer', 'page_id' => '2'));
// if($footerMenu) {
//     $db->query('DELETE FROM `engine4_sespwa_content` WHERE `engine4_sespwa_content`.`content_id` = "'.$footerMenu.'";');
// }

//Landing Page
$db->query("DELETE FROM `engine4_sespwa_content` WHERE `engine4_sespwa_content`.`page_id` =3 AND `engine4_sespwa_content`.`name` !='main' AND `engine4_sespwa_content`.`name` !='middle' AND `engine4_sespwa_content`.`type`='container';");

$LandingPageOrder = 1;
$db->query("DELETE FROM `engine4_sespwa_content` WHERE `engine4_sespwa_content`.`page_id` =3;");
$page_id = 3;
// Insert top
$db->insert('engine4_sespwa_content', array(
    'type' => 'container',
    'name' => 'top',
    'page_id' => $page_id,
    'order' => $LandingPageOrder++,
));
$top_id = $db->lastInsertId();
// Insert main
$db->insert('engine4_sespwa_content', array(
    'type' => 'container',
    'name' => 'main',
    'page_id' => $page_id,
    'order' => $LandingPageOrder++,
));
$main_id = $db->lastInsertId();
// Insert top-middle
$db->insert('engine4_sespwa_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $top_id,
    'order' => $LandingPageOrder++,
));
$top_middle_id = $db->lastInsertId();
// Insert main-middle
$db->insert('engine4_sespwa_content', array(
    'type' => 'container',
    'name' => 'middle',
    'page_id' => $page_id,
    'parent_content_id' => $main_id,
    'order' => $LandingPageOrder++,
));
$main_middle_id = $db->lastInsertId();

$db->insert('engine4_sespwa_content', array(
    'type' => 'widget',
    'name' => 'sespwa.banner-slideshow',
    'page_id' => 3,
    'order' => $LandingPageOrder++,
    'parent_content_id' => $top_middle_id,
    'params' => '{"full_width":"1","height":"200","title":"","nomobile":"0","name":"sespwa.banner-slideshow"}',
));
$db->insert('engine4_sespwa_content', array(
    'type' => 'widget',
    'name' => 'sespwa.login-or-signup',
    'page_id' => 3,
    'order' => $LandingPageOrder++,
    'parent_content_id' => $main_middle_id,
));
$title = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->view->translate('_SITE_TITLE'));

$db->insert('engine4_sespwa_content', array(
    'type' => 'widget',
    'name' => 'sespwa.startup',
    'page_id' => 3,
    'order' => $LandingPageOrder++,
    'parent_content_id' => $main_middle_id,
    'params' => '{"title":"'.$title.'","copyright":"1","logo":"0","nomobile":"0","name":"sespwa.startup"}',
));


$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sespwa_admin_main_minimenu", "sespwa", "Mini Menu", "", \'{"route":"admin_default","module":"sespwa","controller":"menu"}\', "sespwa_admin_main", "", 3);');
