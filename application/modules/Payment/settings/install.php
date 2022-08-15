<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9878 2013-02-13 03:18:43Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Installer extends Engine_Package_Installer_Module
{


  public function onInstall()
  {
    $db = $this->getDb();
    $select = new Zend_Db_Select($db);
    // profile page
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'user_profile_index')
      ->limit(1);
    $pageId = $select->query()->fetchObject()->page_id;
    if($pageId) {
        // container_id (will always be there)
        $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_content')
            ->where('page_id = ?', $pageId)
            ->where('type = ?', 'container')
            ->limit(1);
        $containerId = $select->query()->fetchObject()->content_id;

            $select = new Zend_Db_Select($db);
        $select
            ->from('engine4_core_content')
            ->where('parent_content_id = ?', $containerId)
            ->where('type = ?', 'container')
            ->where('name = ?', 'middle')
            ->limit(1);
        $middleId = $select->query()->fetchObject()->content_id;

        // tab_id (tab container) may not always be there
        $select
            ->reset('where')
            ->where('type = ?', 'widget')
            ->where('name = ?', 'core.container-tabs')
            ->where('page_id = ?', $pageId)
            ->limit(1);
        $tabId = $select->query()->fetchObject();
        if( $tabId && @$tabId->content_id ) {
            $tabId = $tabId->content_id;
        } else {
            $tabId = null;
        }
        if($tabId || $middleId) {
            // music tab on profile
            $db->insert('engine4_core_content', array(
                'page_id' => $pageId,
                'type'    => 'widget',
                'name'    => 'payment.subscribe-plan',
                'parent_content_id' => ($tabId ? $tabId : $middleId),
                'order'   => 10,
                'params'  => '{"show_criteria":["planTitle","expiryDate","nextPayment","currentMember","daysleft"],"paymentButton":"1","title":"Subscription Plan Info","nomobile":"0","name":"payment.subscribe-plan"}',
            ));
        }
    }
    parent::onInstall();
  }
}
?>
