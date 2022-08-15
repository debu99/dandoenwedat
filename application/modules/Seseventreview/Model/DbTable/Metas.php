<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Metas.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Model_DbTable_Metas extends Engine_Db_Table {

  protected $_name = 'eventreview_fields_meta';
  protected $_rowClass = 'Seseventreview_Model_Meta';

  public function profileFieldId() {

    return $this->select()
                    ->from($this->info('name'), array('field_id'))
                    ->where('alias = ?', 'profile_type')
                    ->where('type = ?', 'profile_type')
                    ->query()
                    ->fetchColumn();
  }

}
