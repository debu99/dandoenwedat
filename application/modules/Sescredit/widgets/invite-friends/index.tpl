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
<div class="sescredit_invite_friend sesbasic_bxs sesbasic_sidebar_block">
  <div class="_head"><?php echo $this->translate("Invite Friends");?></div>
  <div class="_des"><?php echo $this->translate("Invite your friends.") ?></div>
  <div class="sescredit_referral_field">
    <div><input type="type" value="<?php echo $this->affiliate;?>" id="myreferrallink" /></div>
    <button onclick="myReferrallink()"><?php echo $this->translate("Copy");?></button>
  </div>
  <div class="sescredit_invite_btn">
    <a class="smoothbox sesbasic_animation" href="<?php echo $this->url(array('action' => 'invite'),'sescredit_general',true);?>"><?php echo $this->translate("Invite");?></a>
  </div>
</div>
<script type="text/javascript">
  function myReferrallink() {
    var copyURL = document.getElementById("myreferrallink");
    copyURL.select();
    document.execCommand("copy");
  }
</script>