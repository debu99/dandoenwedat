<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: BuysellComposer.php  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sesadvancedactivity_Plugin_BuysellComposer extends Core_Plugin_Abstract
{
  public function onAttachBuysell($data,$location = '',$postData)
  {

    $table = Engine_Api::_()->getDbTable('buysells','sesadvancedactivity');
    //$db = $table->getAdapter();
    //$db->beginTransaction();
    try {
      $buysell = $table->createRow();
      $viewer = Engine_Api::_()->user()->getViewer();
      $buysell->user_id = $viewer->getIdentity();
      $buysell->title = $postData['buysell-title'];
      $buysell->buy = $postData['buy-url'];
      $buysell->description = $postData['buysell-description'];
      $buysell->price = $postData['buysell-price'];
      $buysell->currency = !empty($postData['buysell-currency']) ? $postData['buysell-currency']: Engine_Api::_()->sesadvancedactivity()->defaultCurrency();
      $buysell->save();
      //location in post
      if(!empty($postData['buysell-location']) && !empty($postData['activitybuyselllng']) && !empty($postData['activitybuyselllat'])){
         $dbGetInsert = Engine_Db_Table::getDefaultAdapter();
         $dbGetInsert->query('INSERT INTO engine4_sesbasic_locations (resource_id, lat, lng , resource_type,venue) VALUES ("' . $buysell->getIdentity() . '", "' . $postData['activitybuyselllat'] . '","' . $postData['activitybuyselllng'] . '","sesadvancedactivity_buysell","'.$postData['buysell-location'].'")	ON DUPLICATE KEY UPDATE	 lat = "' . $postData['activitybuyselllat'] . '" , lng = "' . $postData['activitybuyselllng'] . '",venue="'.$postData['buysell-location'].'"');
      }
      $buysell->save();
    } catch( Exception $e ) {
      //throw $e;
      return;
    }
    return $buysell;
  }
}
