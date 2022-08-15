<?php
 /**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesgdpr/externals/styles/styles.css'); ?>

<div class="sesgdpr_privacy_center_page sesbasic_bxs">
	<div class="_header">
    <h2><?php echo $this->translate('Privacy Center'); ?></h2>
    <p><?php echo $this->translate('Welcome to our privacy center! This page provides you all the tools to control how we use your personal data. If you have any questions or specific requests please contact our Data Protection Officer directly using the form below.'); ?></p>
	</div>
  <?php $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('gdpr_content', array('cookie','dataProtection','privacySettings','requestArchive','unsubscribe','forgotMe')); ?>
  <div class="sesgdpr_privacy_content">
  	<ul>
    <?php if(in_array('cookie',$settings)){ ?>
      <li class="sesgdpr_privacy_item">
      	<a href="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('gdpr_privacyurl', 'help/privacy'); ?>">
          <span class="_icon fa fa-check-circle"></span>
          <div class="_title"><?php echo $this->translate('Cookies & Privacy Policy Consent') ?></div>
          <div class="_description"><?php echo $this->translate('In our privacy policy we outline how we use your personal data, who we expose your data to, how long we keep it and other details.'); ?></div>
        </a>
      </li>
      <?php  } ?>
      <?php if(in_array('dataProtection',$settings)){ ?>
      <li class="sesgdpr_privacy_item">
      	<a href="javascript:;" data-url="sesgdpr/index/get-gdpr-data/type/dpo" class="sessmoothbox open">
          <span class="_icon far fa-comments"></span>
          <div class="_title"><?php echo $this->translate('Contact DPO'); ?></div>
          <div class="_description"><?php echo $this->translate('You have a special request concerning your personal data, then use this to contact directly to our Data Protection officer.'); ?></div>
        </a>
      </li>
      <?php  } ?>
      <?php if(in_array('privacySettings',$settings)){ ?>
      <li class="sesgdpr_privacy_item">
      	<a href="javascript:;" data-url="sesgdpr/index/get-gdpr-data/type/privacy" class="sessmoothbox open">
          <span class="_icon fa fa-lock"></span>
          <div class="_title"><?php echo $this->translate('Privacy Settings'); ?></div>
          <div class="_description"><?php echo $this->translate('This tool will be used to control the services and third-parties we share your data with.'); ?></div>
        </a>
      </li>
      <?php  } ?>
      <?php if(in_array('requestArchive',$settings)){ ?>
      <li class="sesgdpr_privacy_item">
      	<a href="javascript:;" data-url="sesgdpr/index/get-gdpr-data/type/request" class="sessmoothbox open">
          <span class="_icon fa fa-folder"></span>
          <div class="_title"><?php echo $this->translate('Request Archive'); ?></div>
          <div class="_description"><?php echo $this->translate('If you want to perform Subject Access Request, then use this. We will send you a copy of all the data we currently possess on you.'); ?></div>
        </a>
      </li>
      <?php  } ?>
      <?php if(in_array('unsubscribe',$settings)){ ?>
      <li class="sesgdpr_privacy_item">
      	<a href="javascript:;" data-url="sesgdpr/index/get-gdpr-data/type/unsubscribe" class="sessmoothbox open">
          <span class="_icon far fa-envelope"></span>
          <div class="_title"><?php echo $this->translate('Unsubscribe'); ?></div>
          <div class="_description"><?php echo $this->translate('Fill up the form to unsubscribe and stop receiving all marketing emails from us.'); ?></div>
        </a>
      </li>
      <?php  } ?>
      <?php if(in_array('forgotMe',$settings)){ ?>
      <li class="sesgdpr_privacy_item">
      	<a href="javascript:;" data-url="sesgdpr/index/get-gdpr-data/type/forget" class="sessmoothbox open">
          <span class="_icon far fa-trash-alt"></span>
          <div class="_title"><?php echo $this->translate('Forget Me'); ?></div>
          <div class="_description"><?php echo $this->translate('Fill up the form to submit the Erasure request to forget and delete all your data on this site.'); ?></div>
        </a>
      </li>
      <?php  } ?>
    </ul>
  </div>
</div>