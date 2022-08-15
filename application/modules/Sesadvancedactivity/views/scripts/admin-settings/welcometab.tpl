<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';
?>
<div class="settings sesbasic_admin_form">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script>
  tabvisibility(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.tabvisibility', 0); ?>);
  
  friendrequest(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.friendrequest', 1); ?>);
  
  findfriendssearch(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.findfriends', 1); ?>);

  showwelcometab(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.showwelcometab', 1); ?>);
  
  //profilephotoupload(<?php //echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.profilephotoupload', 0); ?>);
  
  function friendrequest(value) {
    if(value == 1) {
      $('sesadvancedactivity_countfriends-wrapper').style.display = 'block';
    } else {
      $('sesadvancedactivity_countfriends-wrapper').style.display = 'none';
    }
  }
  
  function findfriendssearch(value) {
    if(value == 1) {
      $('sesadvancedactivity_searchnumfriend-wrapper').style.display = 'block';
    } else {
      $('sesadvancedactivity_searchnumfriend-wrapper').style.display = 'none';
    }
  }
  
  function showwelcometab(value) {

    if(value == 1) {
      $('sesadvancedactivity_tabvisibility-wrapper').style.display = 'block';
      $('sesadvancedactivity_makelandingtab-wrapper').style.display = 'block';
      //$('sesadvancedactivity_profilephotoupload-wrapper').style.display = 'block';
      $('sesadvancedactivity_friendrequest-wrapper').style.display = 'block';
      $('sesadvancedactivity_countfriends-wrapper').style.display = 'block';
      $('sesadvancedactivity_findfriends-wrapper').style.display = 'block';
      $('sesadvancedactivity_tabsettings-wrapper').style.display = 'block';
    } else {
      $('sesadvancedactivity_tabvisibility-wrapper').style.display = 'none';
      $('sesadvancedactivity_makelandingtab-wrapper').style.display = 'none';
      //$('sesadvancedactivity_profilephotoupload-wrapper').style.display = 'none';
      $('sesadvancedactivity_friendrequest-wrapper').style.display = 'none';
      $('sesadvancedactivity_countfriends-wrapper').style.display = 'none';
      $('sesadvancedactivity_findfriends-wrapper').style.display = 'none';
      $('sesadvancedactivity_tabsettings-wrapper').style.display = 'none';
    }
  }

  function tabvisibility(value) {
    if(value == 2) {
      $('sesadvancedactivity_numberoffriends-wrapper').style.display = 'none';
      $('sesadvancedactivity_numberofdays-wrapper').style.display = 'block';
    } else if(value == 1) {
      $('sesadvancedactivity_numberoffriends-wrapper').style.display = 'block';
      $('sesadvancedactivity_numberofdays-wrapper').style.display = 'none';
    } else if(value == 0) {
      $('sesadvancedactivity_numberoffriends-wrapper').style.display = 'none';
      $('sesadvancedactivity_numberofdays-wrapper').style.display = 'none';
    }
  }

  
  function profilephotoupload(value) {
    if(value == 1) {
      $('sesadvancedactivity_canphotoshow-wrapper').style.display = 'block';
    } else if(value == 0) {
      $('sesadvancedactivity_canphotoshow-wrapper').style.display = 'none';
    }
  }
</script>
