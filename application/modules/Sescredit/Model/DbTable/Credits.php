<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Credits.php  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

class Sescredit_Model_DbTable_Credits extends Core_Model_Item_DbTable_Abstract {

  protected $_rowClass = "Sescredit_Model_Credit";

  public function getTotalCreditValue($params = array()) {
    $select = $this->select()
            ->from($this->info('name'), 'SUM(credit)');
    if (isset($params['point_type']) && $params['point_type']) {
      if ($params['point_type'] == 'total_credit') {
        $select->where("point_type = 'credit' OR point_type = 'affiliate' OR point_type = 'receive_friend' OR point_type = 'purchase' OR point_type = 'reward'");
      } elseif ($params['point_type'] == 'total_debit') {
        $select->where("point_type = 'deduction' OR point_type = 'transfer_friend' OR point_type = 'sesproduct_order'");
      } else {
        $select->where('point_type =?', $params['point_type']);
      }
      $select->where('owner_id =?', Engine_Api::_()->user()->getViewer()->getIdentity());
    }
    return $select->query()->fetchColumn();
  }

  public function insertUpdatePoint($params = array()) {
    $activityOwnerId = $params['owner_id'];
    $levelId = isset($params['level_id']) ? $params['level_id'] : 0;
    $activityType = $params['type'];
    $objectId = $params['object_id'];
    $actionId = $params['action_id'];
    $pointType = $params['point_type'];
    $creditTable = Engine_Api::_()->getDbTable('credits', 'sescredit');
    if ($pointType != 'affiliate' && $pointType != 'receive_friend' && $pointType != "sesproduct_order" && $pointType != 'transfer_friend' && $pointType != 'purchase' && $pointType != 'upgrade_level' && $pointType != 'reward') {
      $creditValueTable = Engine_Api::_()->getDbTable('values', 'sescredit');
      $selectCreditValueTable = $creditValueTable->select()
              ->from($creditValueTable->info('name'), array('*'))
              ->where('type =?', $activityType)
              ->where('status =?', 1)
              ->where('member_level =?', $levelId);
      $creditValue = $creditValueTable->fetchRow($selectCreditValueTable);
      if ($pointType == 'deduction') {
        $deduction = !empty($creditValue) ? ($creditValue->deduction) : 0;
        if (empty($deduction))
          return;
        $creditPoint = $deduction;
      } else {
        if (empty($creditValue))
          return;
        $firstActivity = $creditValue->firstactivity;
        if ($activityType == 'login' && empty($firstActivity))
          return;
        $nextActivity = $creditValue->nextactivity;
        $maxperday = $creditValue->maxperday;
        $currentDate = date('Y-m-d');
        $creditPoint = empty($firstActivity) ? 0 : $firstActivity;
        if (empty($firstActivity) && empty($nextActivity))
          return;
        $Credit = $creditTable->select()
                ->from($creditTable->info('name'), 'credit_id')
                ->where('owner_id =?', $activityOwnerId)
               // ->where('object_id =?', $objectId)
                ->where('type =?', $activityType)
                ->query()
                ->fetchColumn();
        if ($Credit) {
          if (empty($nextActivity))
            return;
          if (!empty($maxperday)) {
            $CreditSum = $creditTable->select()
                    ->from($creditTable->info('name'), 'SUM(credit)')
                    ->where('owner_id =?', $activityOwnerId)
                    ->where('point_type =?', 'credit')
                    ->where("creation_date LIKE  ?", $currentDate . '%')
                    ->where('type =?', $activityType)
                    ->query()
                    ->fetchColumn();
            if ($CreditSum >= $maxperday)
              return;
            $remainingCredit = $maxperday - $CreditSum;
            if ($remainingCredit < $nextActivity) {
              $creditPoint = $remainingCredit;
            } else {
              $creditPoint = $nextActivity;
            }
          } else {
            $creditPoint = $nextActivity;
          }
        }
      }
    } else {
      $creditPoint = $params['point'];
    }
    $db = Engine_Db_Table::getDefaultAdapter();
    $db = $creditTable->getAdapter();
    $db->beginTransaction();
    try {
      $credit = $creditTable->createRow();
      $credit->type = $activityType;
      $credit->owner_id = $activityOwnerId;
      $credit->action_id = $actionId;
      $credit->object_id = $objectId;
      $credit->credit = $creditPoint;
      $credit->point_type = $pointType;
      $credit->save();
      $userCreditDetailTable = Engine_Api::_()->getDbTable('details', 'sescredit');
      if ($pointType == 'transfer_friend' || $pointType == "sesproduct_order" || $pointType == 'deduction' || $pointType == 'upgrade_level') {
        $userCreditDetailTable->update(array('total_credit' => new Zend_Db_Expr('total_credit - ' . $creditPoint)), array('owner_id =?' => $activityOwnerId));
      } else {
        $select = $userCreditDetailTable->select()
                ->from($userCreditDetailTable->info('name'), array('detail_id', 'first_activity_date'))
                ->where('owner_id =?', $activityOwnerId);
        $totalCredit = $userCreditDetailTable->fetchRow($select);
        $firstActivityDate = date('Y-m-d');
        if (empty($totalCredit)) {
          $userCreditDetailTable->insert(array('owner_id' => $activityOwnerId, 'total_credit' => $creditPoint, 'first_activity_date' => $firstActivityDate));
        } else {
          if ($totalCredit->first_activity_date == '0000-00-00 00:00:00') {
            $userCreditDetailTable->update(array('total_credit' => new Zend_Db_Expr('total_credit + ' . $creditPoint), 'first_activity_date' => $firstActivityDate), array('owner_id =?' => $activityOwnerId));
          } else {
            $userCreditDetailTable->update(array('total_credit' => new Zend_Db_Expr('total_credit + ' . $creditPoint)), array('owner_id =?' => $activityOwnerId));
          }
        }
      }
      Engine_Api::_()->getDbTable('userbadges', 'sescredit')->assignBadge(array('user_id' => $activityOwnerId));
      // Commit
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

}
