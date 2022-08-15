<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Offers.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Offers extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Offer";

  public function getOffer($params = array()) {
    $offerTable = $this;
    $offerTableName = $offerTable->info('name');
    $orderDetailTable = Engine_Api::_()->getDbTable('orderdetails', 'sescredit');
    $orderDetailTableName = $orderDetailTable->info('name');
    $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();

    $availoffer = "Case when limit_offer = 0 then true when limit_offer > (SELECT count(*) from " . $orderDetailTableName . " where offer_id = " . $offerTableName . ".offer_id) then true else false end";
    $availUser = "Case when user_avail = 0 then true when user_avail > (SELECT count(*) from " . $orderDetailTableName . " where owner_id = " . $viewerId . ") then true else false end";
    $offerValidity = "Case when offer_time = 0 then true when date_format(starttime,'%Y-%m-%d') <= '" . date('Y-m-d') . "' and date_format(endtime,'%Y-%m-%d') >= '" . date('Y-m-d') . "' then true else false end";
    $select = $offerTable->select()
            ->from($offerTableName, array('*'))
            ->where('enable =?', 1)
            ->where($availoffer)
            ->where($availUser)
            ->where($offerValidity);
    if (isset($params['offer_id']) && $params['offer_id'])
      $select->where($offerTableName . '.offer_id =?', $params['offer_id']);
    return $offerTable->fetchAll($select);
  }

}
