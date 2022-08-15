<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Links.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Sesadvancedactivity_Model_DbTable_Links extends Engine_Db_Table
{
  protected $_rowClass = 'Sesadvancedactivity_Model_Link';
  protected $_serializedColumns = array('params');

  public function rowExists($core_link_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $select = $this->select()
                    ->where('core_link_id = ?', $core_link_id)
                    ->limit(1);
    $results = $this->fetchRow($select);
    return $results;
  }

  public function removeExists($link_id) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $db->query('DELETE FROM `engine4_sesadvancedactivity_links` WHERE `engine4_sesadvancedactivity_links`.`core_link_id` = "'.$link_id.'";');
  }

  public function isRowExists($link_id, $ses_aaf_gif) {

    $db = Engine_Db_Table::getDefaultAdapter();

    $core_link_id = $this->select()
            ->from($this->info('name'), 'core_link_id')
            ->where('core_link_id =?', $link_id)
            ->query()
            ->fetchColumn();

    if(empty($core_link_id)) {
        $row = $this->createRow();
        $row->core_link_id = $link_id;
        $row->ses_aaf_gif = $ses_aaf_gif;
        $row->save();
        return $row;
    } else {

        $db->update('engine4_sesadvancedactivity_links', array('type' => $type), array('core_link_id =?' => $link_id));
        return $core_link_id;
    }
  }
}
