<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: facebook.tpl 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sessociallogin/views/scripts/dismiss_message.tpl';
$this->headScript()->prependFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
?>
<div class="settings sesbasic_admin_form sesact_global_setting">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<?php $facebook_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.enable', 0);?>
<?php $facebook_quick = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.facebook.quick.signup', 0);?>
<script>

  window.addEvent('domready',function() {
    showoption('<?php echo $facebook_enable;?>', '');
    //showsignupoption('<?php echo $facebook_quick ?>', '');
  });

  function showoption(value, params) {

    if(value == 1) {
      $('sessociallogin_facebook_quick_signup-wrapper').style.display = 'block';
      if(params == 'form') {
        if('<?php echo $facebook_quick ?>' == 1) {
          $('sessociallogin_facebook_profile_type-wrapper').style.display = 'block';
          $('sessociallogin_facebook_member_level-wrapper').style.display = 'block';
          $('sessociallogin_facebook_redirect_user-wrapper').style.display = 'block';
        } else {
          $('sessociallogin_facebook_profile_type-wrapper').style.display = 'none';
          $('sessociallogin_facebook_member_level-wrapper').style.display = 'none';
          $('sessociallogin_facebook_redirect_user-wrapper').style.display = 'none';
        }

      } else {
        showsignupoption('<?php echo $facebook_quick ?>');
      }
    } else {
      $('sessociallogin_facebook_quick_signup-wrapper').style.display = 'none';
      if(params == 'form') {
        $('sessociallogin_facebook_profile_type-wrapper').style.display = 'none';
        $('sessociallogin_facebook_member_level-wrapper').style.display = 'none';
        $('sessociallogin_facebook_redirect_user-wrapper').style.display = 'none';
      } else {
        if('<?php echo $facebook_enable;?>' == 1 && '<?php echo $facebook_quick;?>' == 1) {
          showsignupoption(1);
        } else {
          showsignupoption(0);
        }
      }
    }
  }
  
  function showsignupoption(value) {
    if(value == 1) {
      $('sessociallogin_facebook_profile_type-wrapper').style.display = 'block';
      $('sessociallogin_facebook_member_level-wrapper').style.display = 'block';
      $('sessociallogin_facebook_redirect_user-wrapper').style.display = 'block';
    } else {
      $('sessociallogin_facebook_profile_type-wrapper').style.display = 'none';
      $('sessociallogin_facebook_member_level-wrapper').style.display = 'none';
      $('sessociallogin_facebook_redirect_user-wrapper').style.display = 'none';
    }
  }

</script>