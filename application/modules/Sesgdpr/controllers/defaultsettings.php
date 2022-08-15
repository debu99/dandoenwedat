<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: defaultsettings.php 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

$db = Zend_Db_Table_Abstract::getDefaultAdapter();

// Header Work
$parent_content_id = $db->select()
      ->from('engine4_core_content', 'content_id')
      ->where('type = ?', 'container')
      ->where('page_id = ?', '1')
      ->where('name = ?', 'main')
      ->limit(1)
      ->query()
      ->fetchColumn();
if (empty($content_id)) {
  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesgdpr.cookie-banner',
    'page_id' => 1,
    'parent_content_id' => $parent_content_id,
    'order' => 999,
  ));
}

//Privacy Page
$page_id = $db->select()
        ->from('engine4_core_pages', 'page_id')
        ->where('name = ?', 'core_help_privacy')
        ->limit(1)
        ->query()
        ->fetchColumn();
if($page_id) {
  $left_id = $db->select()
    ->from('engine4_core_content', 'content_id')
    ->where('page_id = ?', $page_id)
    ->where('type = ?', 'container')
    ->where('name = ?', 'middle')
    ->limit(1)
    ->query()
    ->fetchColumn();

  $db->insert('engine4_core_content', array(
    'type' => 'widget',
    'name' => 'sesgdpr.cookies-privacy-consent',
    'page_id' => $page_id,
    'parent_content_id' => $left_id,
    'order' => 999,
  ));
}



$page_id = $db->select()
    ->from('engine4_core_pages', 'page_id')
    ->where('name = ?', 'sesgdpr_index_index')
    ->limit(1)
    ->query()
    ->fetchColumn();
if(!$page_id){
  // Insert page
  $db->insert('engine4_core_pages', array(
      'name' => 'sesgdpr_index_index',
      'displayname' => 'SES - Privacy Center Page',
      'title' => 'Privacy Center Page',
      'description' => 'This page is the view privacy center page.',
      'custom' => 0,
  ));      
    $page_id = $db->lastInsertId();
   // Insert main
    $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'main',
      'page_id' => $page_id,
    ));
    $main_id = $db->lastInsertId();

    // Insert middle
    $db->insert('engine4_core_content', array(
      'type' => 'container',
      'name' => 'middle',
      'page_id' => $page_id,
      'parent_content_id' => $main_id,
      'order' => 2,
    ));
    $middle_id = $db->lastInsertId();

    // Insert content
    $db->insert('engine4_core_content', array(
      'type' => 'widget',
      'name' => 'sesgdpr.privacy-center',
      'page_id' => $page_id,
      'parent_content_id' => $middle_id,
      'order' => 1,
    ));

}

$db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES ("sesgdpr_footer_consent", "sesgdpr", "Privacy Center", "", \'{"route":"sesgdpr_view","module":"sesgdpr"}\', "core_footer", "", "3");');

$db->query('INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
("sesgdpr_admin_reply", "sesgdpr", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[host],[object_link],[subject],[body]"),
("sesgdpr_consent_user", "sesgdpr", "[host],[email],[recipient_title],[recipient_link],[recipient_photo],[host],[object_link],[subject],[body]");');

$db->query('INSERT IGNORE INTO `engine4_sesgdpr_services` ( `name`, `url`, `description`, `enabled`, `creation_date`, `modified_date`) VALUES
("Facebook", "https://www.facebook.com/", "We currently use Facebook to login on our website.", 1, NOW(), NOW()),
("Youtube", "https://www.youtube.com/", "We currently allow you to create videos from Youtube and view them on our site.", 1, NOW(), NOW()),
("Twitter", "https://twitter.com", "We currently use Twitter to login on our website.", 1, NOW(), NOW());');