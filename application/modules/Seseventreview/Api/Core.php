<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Core.php 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class Seseventreview_Api_Core extends Core_Api_Abstract {

  public function getCustomFieldMapData($item) {
    if ($item) {
      $db = Engine_Db_Table::getDefaultAdapter();
      return $db->query("SELECT GROUP_CONCAT(value) AS `valuesMeta`,IFNULL(TRIM(TRAILING ', ' FROM GROUP_CONCAT(DISTINCT(engine4_eventreview_fields_options.label) SEPARATOR ', ')),engine4_eventreview_fields_values.value) AS `value`, `engine4_eventreview_fields_meta`.`label`, `engine4_eventreview_fields_meta`.`type` FROM `engine4_eventreview_fields_values` LEFT JOIN `engine4_eventreview_fields_meta` ON engine4_eventreview_fields_meta.field_id = engine4_eventreview_fields_values.field_id LEFT JOIN `engine4_eventreview_fields_options` ON engine4_eventreview_fields_values.value = engine4_eventreview_fields_options.option_id AND `engine4_eventreview_fields_meta`.`type` = 'multi_checkbox' WHERE (engine4_eventreview_fields_values.item_id = ".$item->getIdentity().") AND (engine4_eventreview_fields_values.field_id != 1) GROUP BY `engine4_eventreview_fields_meta`.`field_id`,`engine4_eventreview_fields_options`.`field_id`")->fetchAll();
    }
    return array();
  }
}