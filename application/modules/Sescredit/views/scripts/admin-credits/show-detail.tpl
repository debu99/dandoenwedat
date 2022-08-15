<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: show-detail.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<div class="sescredit_transactions_details_popup sesbasic_bxs">
  <ul>
    <li>
      <span><?php echo $this->translate("Credit Value:");?></span>
      <span><?php echo $this->creditDetail->credit;?></span>
    </li>
    <li>	
      <span><?php echo $this->translate("Date:");?></span>
      <span><?php echo $this->creditDetail->creation_date;?></span>
    </li>
    <li>			
      <span><?php echo $this->translate("Credit Type:");?></span>
      <span>
        <?php if($this->creditDetail->type == 'sescredit_affiliate'):?>
            <?php echo $this->translate("These points are received for inviter referral.");?>
        <?php elseif($this->creditDetail->type == 'transfer_to_friend'):?>
            <?php echo $this->translate("Transferred the point to your friend.");?>
        <?php elseif($this->creditDetail->type == 'receive_from_friend'):?>
            <?php echo $this->translate("Points received from your friend.");?>
        <?php else:?>
             <?php echo $this->creditDetail->type;?>
        <?php endif;?>
      </span>
    </li>
  </ul>
  <button type="button" onclick="javascript:parent.Smoothbox.close()">Close</button>
</div>
