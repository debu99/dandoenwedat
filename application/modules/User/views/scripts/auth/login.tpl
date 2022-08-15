<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: login.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="user_login_page">
  <?php $image = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.landingimage', ''); ?>
  <?php $image = $image ? Engine_Api::_()->core()->getFileUrl($image) : 'application/modules/User/externals/images/login-bg.jpg'; ?>
  <div class="user_login_bg" style="background-image:url(<?php echo $image; ?>);"></div>
  <div class="user_login_form">
    <h3>
      <?php echo $this->translate('Enter Details to Login', '<a href="'.$this->url(array(), "user_signup").'" class="user_signup_link">', '</a>'); ?>
    </h3>
    <?php echo $this->form->render($this) ?>
    <?php echo $this->htmlLink(array('route' => 'user_signup'), $this->translate('Not a Member? <b>Join</b>')) ?>
  </div>
</div>
<style type="text/css">
  div#captcha-wrapper {
    display: none !important;
  }
</style>
<script type="text/javascript">
    var captchValue = jQuery('.captch-cls').html();
    jQuery('#captcha-input').attr('value',captchValue);
</script>
