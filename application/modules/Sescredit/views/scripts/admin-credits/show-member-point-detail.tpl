<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: show-member-point-detail.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div class="sescredit_transactions_details_popup sesbasic_bxs">
  <ul>
    <li>
      <span><?php echo $this->translate("Total Credit:");?></span>
      <span><?php echo $this->memberPointDetail->total_credit;?></span>
    </li>
    <li>	
      <?php $month = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.month',0);?>
      <?php $year = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.year',0);?>
      <?php $date1 = strtotime('+'.$month.' months',strtotime($this->memberPointDetail->first_activity_date));?>
      <?php $date1 = strtotime('+'.$year.' years',($date1));?>
      <?php $validityFinalDate = date("Y-m-d H:i:s", $date1);?>
      <span><?php echo $this->translate("Credit Expiry Date:");?></span>
      <span class="sesbasic_text_hl"></strong><?php echo date('jS M', strtotime($validityFinalDate));?>,&nbsp;<?php echo date('Y', strtotime($validityFinalDate));?></span>
    </li>
  </ul>
  <button type="button" onclick="javascript:parent.Smoothbox.close()">Close</button>
</div>