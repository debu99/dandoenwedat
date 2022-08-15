<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sessociallogin
 * @package    Sessociallogin
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2017-07-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core');?>
<?php $returnUrl = (((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST']) .Zend_Controller_Front::getInstance()->getRequest()->getRequestUri(); ?>
<?php $facebookHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'facebook'), 'default', true);?>
<?php $twitterHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'auth', 'action' => 'twitter'), 'default', true);?>
<?php if(!Engine_Api::_()->user()->getViewer()->getIdentity()): ?>
  <?php $linkdinHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth', 'action' => 'linkedin'), 'default', true).'?return_url='.$returnUrl;?>     
  <?php $likedinTable = Engine_Api::_()->getDbtable('linkedin', 'sessociallogin');
  $linkedinApi = $likedinTable->getApi();?>
  <?php $instagramHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'instagram'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $instagramTable = Engine_Api::_()->getDbtable('instagram', 'sessociallogin');
  $instagram = $instagramTable->getApi('auth');?>
  <?php $googleHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'google'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $googleTable = Engine_Api::_()->getDbtable('google', 'sessociallogin');
  $google = $googleTable->getApi();?>
  <?php $pinterestHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'pinterest'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $pinterestTable = Engine_Api::_()->getDbtable('pinterest', 'sessociallogin');?>
  <?php $yahooHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'yahoo'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $yahooTable = Engine_Api::_()->getDbtable('yahoo', 'sessociallogin');?>
  <?php $hotmailHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'hotmail'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $hotmailTable = Engine_Api::_()->getDbtable('hotmail', 'sessociallogin');?>
   <?php $flickrHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'flickr'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $flickrTable = Engine_Api::_()->getDbtable('flickr', 'sessociallogin');?>
  
  <?php $vkHref = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'sessociallogin', 'controller' => 'auth','action' => 'vk'), 'default', true).'?return_url='.$returnUrl;?>
  <?php $vkTable = Engine_Api::_()->getDbtable('vk', 'sessociallogin');?>
<?php endif;?>

<?php $numberOfLogin = Engine_Api::_()->sessociallogin()->iconStyle();?>
<div class="sessocial_login_btns sidebar_social_login_icons sesbasic_bxs">
  <ul>
    <?php if(Engine_Api::_()->getDbtable('facebook', 'sessociallogin')->getApi()):?>
      <li class="sessl_facebook" title="<?php echo $this->translate('Log in with Facebook');?>"><a href="<?php echo $facebookHref; ?>"><i class="fab fa-facebook-f"></i></a></li>
    <?php endif;?>
    <?php if( 'none' != $settings->getSetting('core_twitter_enable', 'none')
    && $settings->core_twitter_secret):?>
      <li title="<?php echo $this->translate('Log in with Twitter');?>" class="sessl_twitter"><a href="<?php echo $twitterHref; ?>"><i class="fab fa-twitter"></i></a></li>
    <?php endif;?>
    <?php if($linkedinApi && $likedinTable->isConnected()):?>
      <li title="<?php echo $this->translate('Log in with Linkedin');?>" class="sessl_linkedin"><a href="<?php echo $linkdinHref;?>"><i class="fab fa-linkedin "></i></a></li>
    <?php endif;?>
    <?php if($instagramTable->isConnected() && $instagram):?>
      <li title="<?php echo $this->translate('Log in with Instagram');?>" class="sessl_instagram"><a href="<?php echo $instagramHref;?>"><i class="fab fa-instagram"></i></a></li>
    <?php endif;?>
    <?php if($googleTable->isConnected()):?>
      <li title="<?php echo $this->translate('Log in with Google Plus');?>" class="sessl_google"><a href="<?php echo $googleHref;?>"><i class="fab fa-google-plus-g"></i></a></li>
    <?php endif;?>
    <?php if($pinterestTable->isConnected()):?>
      <li title="<?php echo $this->translate('Log in with Pinterest');?>" class="sessl_pinterest"><a href="<?php echo $pinterestHref;?>"><i class="fab fa-pinterest-p"></i></a></li>
    <?php endif;?>
    <?php if($yahooTable->isConnected()):?>
      <li title="<?php echo $this->translate('Log in with Yahoo');?>" class="sessl_yahoo"><a href="<?php echo $yahooHref;?>"><i></i></a></li>
    <?php endif;?>
    <?php if($hotmailTable->isConnected()): ?>
      <li title="<?php echo $this->translate('Log in with Hot Mail');?>" class="sessl_hotmail"><a href="<?php echo $hotmailHref;?>"><i class="fa fa-envelope"></i></a></li>
    <?php endif;?>
    <?php if($flickrTable->isConnected()):?>
      <li class="sessl_flickr"><a href="<?php echo $flickrHref;?>"><i class="fab fa-flickr"></i><span><?php echo $this->translate('Log in with Flickr');?></span></a></li>
    <?php endif;?>
    <?php if($vkTable->isConnected()):?>
      <li class="sessl_vk"><a href="<?php echo $vkHref;?>"><i class="fab fa-vk"></i><span><?php echo $this->translate('Log in with Vkontakte');?></span></a></li>
    <?php endif;?>
  </ul>
</div>
<style>
#facebook-wrapper {
display:none;
}
#twitter-wrapper {
display:none;
}
/*
#global_page_user-auth-login .layout_middle,
#global_page_user-signup-index .layout_middle,
#global_page_core-error-requireuser .layout_middle {
	position:relative;
}
#global_page_user-auth-login .layout_sesbasic_login_social_icons,
#global_page_core-error-requireuser .layout_sesbasic_login_social_icons,
#global_page_user-signup-index .layout_sesbasic_login_social_icons{
	padding:0px !important;
	margin:0px !important;
	border:none;
}
#global_page_user-auth-login .global_form > div > div,
#global_page_core-error-requireuser .global_form > div > div, 
#global_page_user-signup-index .global_form > div > div{
	padding:20px 20px 70px 20px;
}
#global_page_user-auth-login .layout_sessociallogin_sidebar_social_login,
#global_page_core-error-requireuser .layout_sessociallogin_sidebar_social_login, 
#global_page_user-signup-index .layout_sessociallogin_sidebar_social_login{
	padding:0px !important;
	margin:0px !important;
	border:none;
}
#global_page_user-auth-login .layout_sessociallogin_sidebar_social_login .sessocial_login_btns{
	position: absolute;
	bottom: 50px;
	left: 203px;
}
#global_page_core-error-requireuser .layout_sessociallogin_sidebar_social_login .sessocial_login_btns{
	position: absolute;
	bottom: 46px;
	left: 198px;
}
#global_page_user-signup-index .layout_sessociallogin_sidebar_social_login .sessocial_login_btns{
	position: absolute;
	bottom: 30px;
	left: 187px;
}*/
</style>
