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
<div class="sescredit_topmembers sesbasic_bxs">
  <ul>
    <?php foreach($this->topMembers as $member):?>
      <li class="sescredit_topmembers_item sesbasic_clearfix">
          <div class="_thumb"><a href="<?php echo $member->getHref();?>"><?php echo $this->itemPhoto($member, 'thumb.icon', $member->getTitle());?></div>
        <div class="_cont">
          <div class="_title"><a href="<?php echo $member->getHref();?>"><?php echo $member->getTitle();?></a></div>
          <div class="_points"><i class="sescredit_icon16 sescredit_icon_points"></i><span><?php echo $member->total_credit;?></span></div>
        </div>
      </li>
    <?php endforeach;?>
  </ul>
</div>
