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

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sessociallogin/externals/styles/styles.css'); ?>

<?php $numberOfLogin = Engine_Api::_()->sessociallogin()->iconStyle();?>

<?php if($this->design == 1 || $this->design == 3){ ?>

	<div class="sessl_w_container">

    <div class="sessl_buttons <?php if($this->label == 1){ echo '_islabel';}  ?> <?php if($this->design == 3){ echo '_plainlabel';}  ?> sesbasic_bxs">

      <?php if($this->title){ ?>

        <div class="sessl_buttons_label"><?php echo $this->translate($this->title)?></div>

      <?php } ?>

      <ul class="sesbasic_clearfix">

        <?php if(Engine_Api::_()->getDbtable('facebook', 'sessociallogin')->getApi()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_facebook"><a href="<?php echo $facebookHref; ?>"><i class="fab fa-facebook-f"></i><span><?php echo $this->translate($this->butontext, 'Facebook');?></span></a></li>

        <?php endif;?>

        <?php if( 'none' != $settings->getSetting('core_twitter_enable', 'none')

        && $settings->core_twitter_secret):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_twitter"><a href="<?php echo $twitterHref; ?>"><i class="fab fa-twitter"></i><span><?php echo $this->translate($this->butontext, 'Twitter');?></span></a></li>

        <?php endif;?>

        <?php if($linkedinApi && $likedinTable->isConnected()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_linkedin"><a href="<?php echo $linkdinHref;?>"><i class="fab fa-linkedin "></i><span><?php echo $this->translate($this->butontext, 'Linkedin');?></span></a></li>

        <?php endif;?>

        <?php if($instagramTable->isConnected() && $instagram):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_instagram"><a href="<?php echo $instagramHref;?>"><i class="fab fa-instagram"></i><span><?php echo $this->translate($this->butontext, 'Instagram');?></span></a></li>

        <?php endif;?>

        <?php if($googleTable->isConnected()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_google"><a href="<?php echo $googleHref;?>"><i class="fab fa-google-plus-g"></i><span><?php echo $this->translate($this->butontext, 'Google Plus');?></span></a></li>

        <?php endif;?>

        <?php if($pinterestTable->isConnected()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_pinterest"><a href="<?php echo $pinterestHref;?>"><i class="fab fa-pinterest-p"></i><span><?php echo $this->translate($this->butontext, 'Pinterest');?></span></a></li>

        <?php endif;?>

        <?php if($yahooTable->isConnected()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_yahoo"><a href="<?php echo $yahooHref;?>"><i></i><span><?php echo $this->translate($this->butontext, 'Yahoo');?></span></a></li>

        <?php endif;?>

        <?php if($hotmailTable->isConnected()): ?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_hotmail"><a href="<?php echo $hotmailHref;?>"><i class="fa fa-envelope"></i><span><?php echo $this->translate($this->butontext, 'Hot Mail');?></span></a></li>

        <?php endif;?>

        <?php if($flickrTable->isConnected()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_flickr"><a href="<?php echo $flickrHref;?>"><i class="fab fa-flickr"></i><span><?php echo $this->translate($this->butontext, 'Flickr');?></span></a></li>

        <?php endif;?>

        <?php if($vkTable->isConnected()):?>

          <li <?php if($this->label == 1): ?> style="width:<?php echo $this->btnwidth ?>px;" <?php endif; ?> class="sessl_vk"><a href="<?php echo $vkHref;?>"><i class="fab fa-vk"></i><span><?php echo $this->translate($this->butontext, 'Vkontakte');?></span></a></li>

        <?php endif;?>

      </ul>

    </div>

  </div>

<?php } elseif($this->design == 2){ ?>

	<div class="sessl_w_container">

    <div class="sessl_buttons_box <?php if($this->label == 1){ echo '_islabel';}  ?>  sesbasic_bxs">

      <?php if($this->title){ ?>

        <h3 class="sessl_buttons_box_head"><?php echo $this->translate($this->title)?></h3>

      <?php } ?>

      <div class="sessl_buttons_box_cont">

        <ul class="sesbasic_clearfix">

          <?php if(Engine_Api::_()->getDbtable('facebook', 'sessociallogin')->getApi()):?>

            <li class="sessl_facebook" title="<?php echo $this->translate('Facebook');?>">

              <a href="<?php echo $facebookHref; ?>"><i class="fab fa-facebook-f"></i></a>

              <span><?php echo $this->translate('Facebook');?></span>

            </li>

          <?php endif;?>

          <?php if( 'none' != $settings->getSetting('core_twitter_enable', 'none')

          && $settings->core_twitter_secret):?>

            <li class="sessl_twitter" title="<?php echo $this->translate('Twitter');?>">

              <a href="<?php echo $twitterHref; ?>"><i class="fab fa-twitter"></i></a>

              <span><?php echo $this->translate('Twitter');?></span>

            </li>

          <?php endif;?>

          <?php if($linkedinApi && $likedinTable->isConnected()):?>

            <li class="sessl_linkedin" title="<?php echo $this->translate('LinkedIn');?>">

              <a href="<?php echo $linkdinHref;?>"><i class="fab fa-linkedin "></i></a>

              <span><?php echo $this->translate('LinkedIn');?></span>

            </li>

          <?php endif;?>

          <?php if($instagramTable->isConnected() && $instagram):?>

            <li class="sessl_instagram" title="<?php echo $this->translate('Instagram');?>">

              <a href="<?php echo $instagramHref;?>"><i class="fab fa-instagram"></i></a>

              <span><?php echo $this->translate('Instagram');?></span>

            </li>

          <?php endif;?>

          <?php if($googleTable->isConnected()):?>

            <li class="sessl_google" title="<?php echo $this->translate('Google Plus');?>">

              <a href="<?php echo $googleHref;?>"><i class="fab fa-google-plus-g"></i></a>

              <span><?php echo $this->translate('Google Plus');?></span>

            </li>

          <?php endif;?>

          <?php if($pinterestTable->isConnected()):?>

            <li class="sessl_pinterest" title="<?php echo $this->translate('Pinterest');?>">

              <a href="<?php echo $pinterestHref;?>"><i class="fab fa-pinterest-p"></i></a>

              <span><?php echo $this->translate('Pinterest');?></span>

            </li>

          <?php endif;?>

          <?php if($yahooTable->isConnected()):?>

            <li class="sessl_yahoo" title="<?php echo $this->translate('Yahoo');?>">

              <a href="<?php echo $yahooHref;?>"><i></i></a>

              <span><?php echo $this->translate('Yahoo');?></span>

            </li>

          <?php endif;?>

          <?php if($hotmailTable->isConnected()): ?>

            <li class="sessl_hotmail" title="<?php echo $this->translate('Hot Mail');?>">

              <a href="<?php echo $hotmailHref;?>"><i class="fa fa-envelope"></i></a>

              <span><?php echo $this->translate('Hot Mail');?></span>

            </li>

          <?php endif;?>

          <?php if($flickrTable->isConnected()):?>

            <li class="sessl_flickr" title="<?php echo $this->translate('Flickr');?>">

              <a href="<?php echo $flickrHref;?>"><i class="fab fa-flickr"></i></a>

              <span><?php echo $this->translate('Flickr');?></span>

            </li>

          <?php endif;?>

          <?php if($vkTable->isConnected()):?>

            <li class="sessl_vk" title="<?php echo $this->translate('Vkontakte');?>">

              <a href="<?php echo $vkHref;?>"><i class="fab fa-vk"></i></a>

              <span><?php echo $this->translate('Vkontakte');?></span>

            </li>

          <?php endif;?>

        </ul>

      </div>

    </div>

  </div>  

<?php }?>