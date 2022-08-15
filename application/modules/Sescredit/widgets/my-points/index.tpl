<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<div class="sescredit_my_points sesbasic_bxs">
  <?php if(!empty($this->firstActivityDate)):?>
    <div class="sescredit_my_points_total">
      <div class="sescredit_my_points_cont">
        <div class="_points">
          <article class="sesbasic_lbg">
            <span><?php echo $this->translate("Credit Points");?></span>
            <span class="sesbasic_text_hl"><?php $credit = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'total_credit'));?><?php echo $credit;?></span>
          </article>
        </div>
        <?php $debit =  Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'total_debit'));?>
        <div class="_points">
          <article class="sesbasic_lbg">
            <span><?php echo $this->translate("Debit Points");?></span>
            <span class="sesbasic_text_hl"><?php echo empty($debit) ? 0 : $debit;?></span>
          </article>
        </div>
        <div class="_points">
          <article class="sesbasic_lbg">
            <span><?php echo $this->translate("Remaining Points");?></span>
            <span class="sesbasic_text_hl"><?php echo $credit - $debit;?></span>
          </article>
        </div>
        <?php $month = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.month',0);?>
        <?php $year = Engine_Api::_()->getApi('settings','core')->getSetting('sescredit.year',0);?>
        <?php $date1 = strtotime('+'.$month.' months',strtotime($this->firstActivityDate));?>
        <?php $date1 = strtotime('+'.$year.' years',($date1));?>
        <?php $validityFinalDate = date("Y-m-d H:i:s", $date1);?>
        <div class="_date">
          <article class="sesbasic_lbg">
            <span><?php echo $this->translate("Validity Date");?></span>
            <span class="sesbasic_text_hl"></strong><?php echo date('jS M', strtotime($validityFinalDate));?>,&nbsp;<?php echo date('Y', strtotime($validityFinalDate));?></span>
          </article>
        </div>
      </div>
    </div>
  <?php endif;?>  
  <div class="sescredit_my_points_table">
      <div class="_mypointstable_header sesbasic_lbg">
        <div class="_left _label"><?php echo $this->translate("Credit Point Type");?></div>
        <div class="_right">
          <div class="_label"><?php echo $this->translate("Credit Points");?></div>
          <div><i class="sescredit_icon_add fa fa-plus"></i></div>
          <div><i class="sescredit_icon_minus fa fa-minus"></i></div>
        </div>
      </div>
      <div class="_mypointstable_content">
        <div class="_mypointstable_content_item">
          <div><?php echo $this->translate("For New Activity");?></div>
          <div><?php echo Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'credit'));?></div>
          <div>-</div>
        </div>
        <div class="_mypointstable_content_item">
          <div><?php echo $this->translate('On Activity Deletion');?></div>
          <div>-</div>
          <div><?php echo Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'deduction'));?></div>
        </div>
        <?php $signupPoint = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'affiliate'));?>
        <?php if(!empty($signupPoint)):?>
          <div class="_mypointstable_content_item">
            <div><?php echo $this->translate('Inviter Affiliation');?></div>
            <div><?php echo $signupPoint;?></div>
            <div>-</div>
          </div>
        <?php endif;?>
        <div class="_mypointstable_content_item">
          <div><?php echo $this->translate('Transferred to Friends');?></div>
          <div>-</div>
          <div><?php $transferToFriend = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'transfer_friend'));?><?php echo ($transferToFriend) ? $transferToFriend : '-';?></div>
        </div>
        <div class="_mypointstable_content_item">
          <div><?php echo $this->translate('Product Purchased');?></div>
          <div>-</div>
          <div><?php $productPurchased = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'sesproduct_product'));?><?php echo ($productPurchased) ? $productPurchased : '-';?></div>
        </div>
        <div class="_mypointstable_content_item">
          <div><?php echo $this->translate('Received from Friends');?></div>
          <div><?php $receiveFromFriend = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'receive_friend'));?><?php echo ($receiveFromFriend) ? $receiveFromFriend : '-';?></div>
          <div>-</div>
        </div>
         <div class="_mypointstable_content_item">
          <div><?php echo $this->translate('Buy from site');?></div>
          <div><?php $buyPoint = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'purchase'));?><?php echo ($buyPoint) ? $buyPoint : '-';?></div>
          <div>-</div>
        </div>
        <div class="_mypointstable_content_item">
          <div><?php echo $this->translate('Reward');?></div>
          <div><?php $rewardPoint = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'reward'));?><?php echo ($rewardPoint) ? $rewardPoint : '-';?></div>
          <div>-</div>
        </div>
        <?php $upgradePoint = Engine_Api::_()->getDbTable('credits','sescredit')->getTotalCreditValue(array('point_type' => 'upgrade_level'));?>
        <?php if(!empty($upgradePoint)):?>
          <div class="_mypointstable_content_item">
            <div><?php echo $this->translate('On Membership Upgrade');?></div>
            <div><?php echo $upgradePoint;?></div>
            <div>-</div>
          </div>
        <?php endif;?>
      </div>
  </div>
</div>
