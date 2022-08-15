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

<div class="sescredit_member_level sesbasic_bxs">
	<div class="_currentlavel"><?php echo $this->label;?></div>
	<div id="show_upgrade_option" class="sescredit_member_level_options">
	  <?php if($this->upgradeInfo && $this->upgradeInfo->status == 0):?>
	    <?php echo $this->translate("You have already sent request for membership upgrade.");?>
      <?php elseif($this->upgradeInfo && $this->upgradeInfo->status == 2):?>
	    <?php echo $this->translate("Site Admin has been rejected your membership upgrade request.");?>
	  <?php else:?>
	    <a href="<?php echo $this->url(array('action' => 'show-member-level'),'sescredit_general',true);?>" class='sessmoothbox'>
	      <?php echo $this->translate("Show Memberships");?> &rsaquo;
	    </a>
	  <?php endif;?>
	</div>
</div>