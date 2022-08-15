<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: show-send-point-detail.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div class="sescredit_transactions_details_popup sesbasic_bxs">
  <ul>
    <li>
      <span><?php echo $this->translate("Send Point:");?></span>
      <span><?php echo $this->detail->point;?></span>
    </li>
   <li>
      <span><?php echo $this->translate("Point Sending Reason:");?></span>
      <span><?php echo $this->detail->reason;?></span>
    </li>
  </ul>
  <button type="button" onclick="javascript:parent.Smoothbox.close()">Close</button>
</div>