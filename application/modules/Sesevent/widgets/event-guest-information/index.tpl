<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<style>
.sesevent_guests_block_user  {
  overflow:hidden;
}

<?php if(!$this->isLoggedIn) { ?>
  .attending .sesevent_guest_photo, 
  .waiting .sesevent_guest_photo,
  .host .sesevent_guest_photo  {
    filter: blur(8px);
  }
  .sesevent_guests_block_heading_link {
    display: none;
  }
<?php } ?>
.sesevent_guest_photo img{height:100%;width:100%;}
</style>
<?php $host = Engine_Api::_()->getItem('user', $this->subject->user_id); ?>
<ul class="sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block sesevent_guests_block">
  <li class="sesbasic_clearfix">
    <div class="sesevent_guests_block_heading sesbasic_clearfix sesbm">
      <span class="sesevent_guests_block_heading_label floatL"><?php echo $this->translate("Host"); ?></span>
    </div>
    <div class="sesevent_guests_block_user host" style="height: 60px;width:60px">
       <?php echo $this->htmlLink($host->getHref(), $this->itemPhoto($host, 'thumb.profile', $host->getTitle()), array('class' => 'sesevent_guest_photo')) ?>
    </div>
  </li>
</ul>

<ul class="sesbasic_clearfix sesbasic_bxs sesbasic_sidebar_block sesevent_guests_block">
 <?php if($this->attending->getTotalItemCount() > 0){ ?>
  <li class="sesbasic_clearfix">
    <div class="sesevent_guests_block_heading sesbasic_clearfix sesbm">
      <span class="sesevent_guests_block_heading_label floatL"><?php echo $this->translate("Attending"); ?></span>
    <?php if($this->attending->getTotalItemCount() > $this->guestCount){ ?>
      <a href="javascript:;" onclick="getGuestDetails('attending')" class="floatR sesevent_guests_block_heading_link"><?php echo $this->translate("See All"); ?> &raquo;</a>
    <?php } ?>
    </div>
  <?php 
  	$counterAttending = 0;
  	foreach($this->attending as $userAttending){ 
    	 $user = Engine_Api::_()->getItem('user', $userAttending->user_id);
     ?>
      <div class="sesevent_guests_block_user attending" style="height:<?php echo $this->height.'px' ?>;width:<?php echo $this->width.'px' ?>">
       <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.profile', $user->getTitle()), array('class' => 'sesevent_guest_photo')) ?>
      </div>
    <?php 
    	$counterAttending++;
    if($counterAttending == $this->guestCount) break; ?>
  <?php } ?>
  </li>  
 <?php } ?>
 <?php if($this->maybeattending->getTotalItemCount() > 0){ ?>
  <li class="sesbasic_clearfix">
    <div class="sesevent_guests_block_heading sesbasic_clearfix sesbm">
      <span class="sesevent_guests_block_heading_label floatL"><?php echo $this->translate("Maybe Attending"); ?></span>
    <?php if($this->maybeattending->getTotalItemCount() > $this->guestCount){ ?>
      <a href="javascript:;" onclick="getGuestDetails('maybeattending')"  class="floatR sesevent_guests_block_heading_link"><?php echo $this->translate("See All"); ?> &raquo;</a>
    <?php } ?>
    </div>
  <?php 
  	$countermaybeattending = 0;
  	foreach($this->maybeattending as $userAttending){ 
    	 $user = Engine_Api::_()->getItem('user', $userAttending->user_id);
     ?>
      <div class="sesevent_guests_block_user host" style="height:<?php echo $this->height.'px' ?>;width:<?php echo $this->width.'px' ?>">
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.profile', $user->getTitle()), array('class' => 'sesevent_guest_photo')) ?>
      </div>
    <?php 
    $countermaybeattending++;
    if($countermaybeattending == $this->guestCount) break; ?>
  <?php } ?>
  </li>  
 <?php } ?>
   <?php if($this->notattending->getTotalItemCount() > 0){ ?>
  <li class="sesbasic_clearfix">
    <div class="sesevent_guests_block_heading sesbasic_clearfix sesbm">
      <span class="sesevent_guests_block_heading_label floatL"><?php echo $this->translate("Not Attending"); ?></span>
    <?php if($this->notattending->getTotalItemCount() > $this->guestCount){ ?>
      <a href="javascript:;" onclick="getGuestDetails('notattending')" class="floatR sesevent_guests_block_heading_link"><?php echo $this->translate("See All"); ?> &raquo;</a>
    <?php } ?>
    </div>
  <?php 
  	$counternotattending = 0;
  	foreach($this->notattending as $userAttending){ 
    	 $user = Engine_Api::_()->getItem('user', $userAttending->user_id);
     ?>
      <div class="sesevent_guests_block_user" style="height:<?php echo $this->height.'px' ?>;width:<?php echo $this->width.'px' ?>">
       <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.profile', $user->getTitle()), array('class' => 'sesevent_guest_photo')) ?>
      </div>
    <?php 
    	$counternotattending++;
    if($counternotattending == $this->guestCount) break; ?>
  <?php } ?>
  </li>  
 <?php } ?>
<?php if($this->onwaitinglist->getTotalItemCount() > 0){ ?>
  <li class="sesbasic_clearfix">
    <div class="sesevent_guests_block_heading sesbasic_clearfix sesbm">
      <span class="sesevent_guests_block_heading_label floatL"><?php echo $this->translate("On Waiting List"); ?></span>
    <?php if($this->onwaitinglist->getTotalItemCount() > $this->guestCount){ ?>
      <a href="javascript:;" onclick="getGuestDetails('onwaitinglist')" class="floatR sesevent_guests_block_heading_link"><?php echo $this->translate("See All"); ?> &raquo;</a>
    <?php } ?>
    </div>
  <?php 
  	$countWaitingList = 0;
  	foreach($this->onwaitinglist as $userWaiting){ 
    	 $user = Engine_Api::_()->getItem('user', $userWaiting->user_id);
     ?>
      <div class="sesevent_guests_block_user waiting" style="height:<?php echo $this->height.'px' ?>;width:<?php echo $this->width.'px' ?>">
       <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.profile', $user->getTitle()), array('class' => 'sesevent_guest_photo')) ?>
      </div>
    <?php 
    	$countWaitingList++;
    if($countWaitingList == $this->guestCount) break; ?>
  <?php } ?>
  </li>  
 <?php } ?>

</ul>
<script type="application/javascript">
function getGuestDetails(value){
	if(value){
		url = en4.core.staticBaseUrl+'sesevent/index/guest-info/event_id/<?php echo $this->subject->getIdentity(); ?>/value/'+value;
		openURLinSmoothBox(url);	
		return;
	}
}
</script>