<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _homesuggestions.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
if(!$this->viewer()->getIdentity())
  return; ?>

<?php
if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendnotificationbirthday',1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendnotificationbirthdaytext','') && $birthdayFriend = Engine_Api::_()->sesadvancedactivity()->loggedinFriendBirthday(array('single'=>true),$this->viewer())){ 
  $birthdayuser = Engine_Api::_()->getItem('user',$birthdayFriend->item_id);
?>
<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_sendwish sesbasic_bxs parent_notification_sesadv">
<a href="javascript:;" class="sesact_addbday_close close_parent_notification_sesadv"><i class="fas fa-times sesbasic_text_light"></i></a>
  <?php 
  $token = array('BIRTHDAY_USER_NAME','BIRTHDAY_USER_IMAGE');
   $replace = array($birthdayuser->getTitle(),$this->htmlLink($birthdayuser->getHref(), $this->itemPhoto($birthdayuser, 'thumb.profile', $birthdayuser), array()));
  ?>
	<?php echo str_replace($token,$replace,Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendnotificationbirthdaytext','')); ?>
  <div class="sesact_sendwish_btns">
    	<a href="<?php echo $birthdayuser->getHref(); ?>"><i class="fa fa-edit-square-o"></i><span><?php echo $this->translate("Post on Wall"); ?></span></a>
     <?php if(Engine_Api::_()->sesbasic()->hasCheckMessage($birthdayuser)){ ?>
      <a href="<?php echo "messages/compose/to/".$birthdayuser->user_id  ?>" class="smoothbox"><i class="fa fa-comments-o"></i><span><?php echo $this->translate('Send Message') ; ?></span></a>
     <?php } ?>
    </div>
</div>
<?php
}
if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationbirthday',1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationbirthdaytext','')){
 $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias($this->viewer()); 
 $isBirthday = Engine_Api::_()->sesadvancedactivity()->getBirthdayViewer($this->viewer(),$fields);
 if($isBirthday){
?>
<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_bdaywish centerT sesbasic_bxs parent_notification_sesadv" style="padding:0;background-image:none;">
<a href="javascript:;" class="sesact_addbday_close close_parent_notification_sesadv"><i class="fas fa-times sesbasic_text_light"></i></a>
  <?php $token = array('BIRTHDAY_USER_NAME'); ?>
  <?php $replace = array(ucwords($this->viewer()->getTitle())); ?>
  <?php echo str_replace($token,$replace,Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationbirthdaytext','')); ?>  
</div>
<?php 
	 }
 } ?>
<?php 
if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationday',1) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationdaytext','')){
 $isWelcomeMessage = Engine_Api::_()->sesadvancedactivity()->getWelcomeMessage($this->viewer());
 if($isWelcomeMessage['status']){
?>
<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_wishbox parent_notification_sesadv" style="padding:0;">
<a href="javascript:;" class="sesact_addbday_close close_parent_notification_sesadv"><i class="fas fa-times sesbasic_text_light"></i></a>
  <?php $token = array('NOTIFICATION_TIME','NOTIFICATION_USER','NOTIFICATION_IMAGE'); ?>
  <?php $replace = array($isWelcomeMessage['message'],ucwords($this->viewer()->getTitle()),"application/modules/Sesadvancedactivity/externals/images/".$isWelcomeMessage['image']); ?>
  <?php echo str_replace($token,$replace,Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationdaytext','')); ?>  
</div>
<?php } 
}
?>
<?php 
if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationfriends',1)){
 $friendCountTotal = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.notificationfriendsdays',30);
 if($friendCountTotal)
  $friendCount = $this->viewer()->membership()->getMemberCount($this->viewer());
 if(!$friendCountTotal || $friendCount < $friendCountTotal){ ?>
<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_find_frind parent_notification_sesadv">
<a href="javascript:;" class="sesact_addbday_close close_parent_notification_sesadv"><i class="fas fa-times sesbasic_text_light"></i></a>
	<div class="sesact_find_frind_head sesbm">
  	<?php echo $this->translate("Add Friends to See More Feeds"); ?>
  </div>
  <div class="sesact_find_frind_cont sesbasic_clearfix">
  	<i class="floatL"><img src="application/modules/Sesadvancedactivity/externals/images/feed64.png" alt="" /></i>
    <span class="floatR more_btn">
      <?php if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sespymk')){ ?>
    	  <a href="members" class="sesbasic_button"><?php echo $this->translate("Find Friends"); ?></a>
      <?php }else{ ?>
        <a href="friends/requests" class="sesbasic_button"><?php echo $this->translate("Find Friends"); ?></a>
      <?php } ?>
    </span>
    <span class="des">
    	<?php echo $this->translate("You’ll have more feeds in your Activity Feed wall, once you add more friends here."); ?>
    </span>
  </div>
</div>
<?php } ?>
<?php  } ?>
<?php 
  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.dobadd',1)){
  if(empty($fields))
   $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias($this->viewer());
  if(empty($fields['birthdate'])){
 ?>
<div class="sesact_tip_box sesbasic_clearfix sesbm sesact_addbday centerT parent_notification_sesadv">
	<a href="javascript:;" class="sesact_addbday_close close_parent_notification_sesadv"><i class="fas fa-times sesbasic_text_light"></i></a>
  <span class="sesact_addbday_title"><?php echo $this->translate("Add your birthday to your profile"); ?></span>
  <span class="sesact_addbday_des"><?php echo $this->translate("Let people know when the big day arrives."); ?></span>
	<span class="sesact_addbday_btn"><a href="members/edit/profile" class="sesbasic_link_btn"><?php echo $this->translate("Go now"); ?></a></span>
</div>
<?php 
  }
} ?>

<?php $events = Engine_Api::_()->getDbTable('events','sesadvancedactivity')->getEvent($this->viewer());
  if($events) {
    echo $this->partial('_events.tpl','sesadvancedactivity',array('events'=>$events,'share'=>true));
  } 
?>