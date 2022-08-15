<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: hotmail.tpl 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Sessociallogin/views/scripts/dismiss_message.tpl'; ?>
<div class="settings sesbasic_admin_form sesact_global_setting">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<?php $hotmail_enable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.enable', 0);?>
<?php $hotmail_quick = Engine_Api::_()->getApi('settings', 'core')->getSetting('sessociallogin.hotmail.quick.signup', 0);?>
<script>

  window.addEvent('domready',function() {
    showoption('<?php echo $hotmail_enable;?>', '');
    //showsignupoption('<?php echo $hotmail_quick ?>', '');
  });

  function showoption(value, params) {

    if(value == 1) {
      $('sessociallogin_hotmail_quick_signup-wrapper').style.display = 'block';
      if(params == 'form') {
        if('<?php echo $hotmail_quick ?>' == 1) {
          $('sessociallogin_hotmail_profile_type-wrapper').style.display = 'block';
          $('sessociallogin_hotmail_member_level-wrapper').style.display = 'block';
          $('sessociallogin_hotmail_redirect_user-wrapper').style.display = 'block';
        } else {
          $('sessociallogin_hotmail_profile_type-wrapper').style.display = 'none';
          $('sessociallogin_hotmail_member_level-wrapper').style.display = 'none';
          $('sessociallogin_hotmail_redirect_user-wrapper').style.display = 'none';
        }

      } else {
        showsignupoption('<?php echo $hotmail_quick ?>');
      }
    } else {
      $('sessociallogin_hotmail_quick_signup-wrapper').style.display = 'none';
      if(params == 'form') {
        $('sessociallogin_hotmail_profile_type-wrapper').style.display = 'none';
        $('sessociallogin_hotmail_member_level-wrapper').style.display = 'none';
        $('sessociallogin_hotmail_redirect_user-wrapper').style.display = 'none';
      } else {
        if('<?php echo $hotmail_enable;?>' == 1 && '<?php echo $hotmail_quick;?>' == 1) {
          showsignupoption(1);
        } else {
          showsignupoption(0);
        }
      }
    }
  }
  
  function showsignupoption(value) {
    if(value == 1) {
      $('sessociallogin_hotmail_profile_type-wrapper').style.display = 'block';
      $('sessociallogin_hotmail_member_level-wrapper').style.display = 'block';
      $('sessociallogin_hotmail_redirect_user-wrapper').style.display = 'block';
    } else {
      $('sessociallogin_hotmail_profile_type-wrapper').style.display = 'none';
      $('sessociallogin_hotmail_member_level-wrapper').style.display = 'none';
      $('sessociallogin_hotmail_redirect_user-wrapper').style.display = 'none';
    }
  }

</script>