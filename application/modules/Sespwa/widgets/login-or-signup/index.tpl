<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-11-22 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core');?>
<?php $facebook = Engine_Api::_()->getDbtable('facebook', 'user')->getApi();?>
<div class="sespwa_home_login sesbasic_bxs">
	<div class="sespwa_home_login_form">
    <?php if( !$this->noForm ): ?>
      <?php echo $this->form->render($this) ?>
      <?php if( !empty($this->fbUrl) ): ?>
        <script type="text/javascript">
          var openFbLogin = function() {
            Smoothbox.open('<?php echo $this->fbUrl ?>');
          }
          var redirectPostFbLogin = function() {
            window.location.href = window.location;
            Smoothbox.close();
          }
        </script>
        <?php // <button class="user_facebook_connect" onclick="openFbLogin();"></button> ?>
      <?php endif; ?>
    <?php else: ?>
      <?php echo $this->form->setAttrib('class', 'global_form_box no_form')->render($this) ?>  
    <?php endif; ?>

    <?php if(Engine_Api::_()->getDbTable('modules','core')->isModuleEnabled('sessociallogin')):?>
      <?php $numberOfLogin = Engine_Api::_()->sessociallogin()->iconStyle();?>
      <div class="sm_social_login_btns sespwa_home_login_btns">
        <?php  echo $this->partial('_socialLoginIcons.tpl','sessociallogin',array()); ?>
      </div>
    <?php else: ?>
      <div class="_socialloginbtns">
        <?php if ('none' != $settings->getSetting('core_facebook_enable', 'none') && $settings->core_facebook_secret):?>
        <?php if (!$facebook):?>
          <?php return; ?>
        <?php endif;?>
          <?php $facebookhref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth','action' => 'facebook'), 'default', true);?>
          <a href="<?php echo $facebookhref;?>" id="fbLogin" class="_facebook"><i class="fa fa-facebook"></i><span><?php echo $this->translate("Facebook")?></span></a>
        <?php endif; ?>
        <?php if ('none' != $settings->getSetting('core_twitter_enable', 'none') && $settings->core_twitter_secret):?>
          <?php $twitterhref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'twitter'), 'default', true);?>
          <a href="<?php echo $twitterhref;?>" id="twitterLogin" class="_twitter"><i class="fa fa-twitter"></i><span><?php echo $this->translate("Twitter")?></span></a>
        <?php endif; ?>
      </div>
    <?php endif;?>
  </div>
  <div class="_signup_section">
  	<p><?php echo $this->translate("New to %s", Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE'))); ?></p>
    <a href="signup" class="_signup_btn"><?php echo $this->translate("Create New Account"); ?></a>
  </div>
</div>
