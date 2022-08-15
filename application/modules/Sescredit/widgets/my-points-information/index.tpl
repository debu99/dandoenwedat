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
<div class="sescredit_mypointsinfo sesbasic_bxs">
  <div class="_currentp sesbasic_text_hl">Current Points : <?php echo $this->currentPoint;?></div>
  <?php if(!empty($this->currentBadge)):?>
    <div class="_badges centerT">
      <span class="_badge">
          <a href="javascript:void(0);"><img src="<?php echo Engine_Api::_()->getItem('storage_file',$this->currentBadge->photo_id)->getPhotoUrl(); ?>" /></a>
          <span class="_badgename"><?php echo $this->currentBadge->title;?></span>
      </span>
      <?php if(!empty($this->nextBadge)):?>
        <span class="_nxtarrow sesbasic_text_hl">
            <i class="fa fa-arrow-right"></i>
        </span>
        <span class="_badge">
          <a href="javascript:void(0);"><img src="<?php echo Engine_Api::_()->getItem('storage_file',$this->nextBadge->photo_id)->getPhotoUrl(); ?>" /></a>
          <span class="_badgename"><?php echo $this->nextBadge->title;?></span>
        </span>
      <?php endif;?>
    </div>
    <div class="_pointsbar">
      <div class="_stats sesbasic_clearfix">
        <span class="sesbasic_clearfix floatL">Current Points</span>
        <?php if(!empty($this->nextBadge)):?>
          <span class="sesbasic_clearfix floatR">For Next Badge</span>
        <?php endif;?>
      </div>
      <div class="_bar"><span style="width:25%;"></span></div>
      <div class="_stats sesbasic_clearfix">
        <div class="sesbasic_clearfix floatL"><?php echo $this->currentPoint;?></div>
        <?php if(!empty($this->nextBadge)):?>
          <div class="sesbasic_clearfix floatR"><?php echo $this->nextBadge->credit_value;?></div>	
        <?php endif;?>
      </div>
    </div>
  <?php elseif(!empty($this->nextBadge)):?>
    <div class="_single_badge">
      <a href="javascript:void(0);"><img src="<?php echo Engine_Api::_()->getItem('storage_file',$this->nextBadge->photo_id)->getPhotoUrl(); ?>" /></a>
      <span class="_badgename"><?php echo $this->nextBadge->title;?></span>
      <span class="_msg"><?php echo $this->translate("You will be eligible for above badge, if you earn below point.");?></span>
      <span class="_topoints"><span><?php echo $this->nextBadge->credit_value;?></span></span>
    </div>
  <?php endif;?>
</div>
