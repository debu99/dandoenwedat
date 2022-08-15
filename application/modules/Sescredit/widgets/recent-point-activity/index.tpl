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
<div class="sescredit_recent_activity sesbasic_bxs">
  <ul>
    <?php foreach($this->activities as $activity):?>
      <?php $transaction = $activity;?>
      <li class="sesbasic_clearfix">
          <?php if($activity->point_type == 'affiliate'):?>
            <?php $text = $this->translate("You have received points for inviter affiliation.");?>
          <?php elseif($activity->point_type == 'transfer_friend'):?>
            <?php $text = $this->translate("You have transferred points to your friend.");?>
          <?php else:?>
            <?php $text = $activity->language;?>
          <?php endif;?>
          <div class="_text"><?php echo $text;?></div>
        <div class="_date sesbasic_text_light sesbasic_clearfix">
          <span class="floatL"><i class="sescredit_icon16 sescredit_icon_points"></i><span><?php echo $activity->credit;?></span></span>
          <span class="floatR"><?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/_date.tpl';?></span>
        </div>
      </li>
    <?php endforeach;?>
  </ul>	
</div>
