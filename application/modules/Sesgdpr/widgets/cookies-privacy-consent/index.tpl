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
<form action="sesgdpr/index/consent" style="display:none;" method="post">
  <input type="hidden" name="type" id="sesconsent_consent">
</form>
<div class="sesgdpr_consent_block sesbasic_bxs">
<?php if(!empty($_SESSION['consent'])){ unset($_SESSION['consent']); ?>
  <div class="sesgdpr_success_tip sesgdpr_clearfix">
  	<span class="_head"><?php echo $this->translate('Consent withdrawn'); ?></span>
    	<span class="_text"><?php echo $this->translate('You have successfully withdrawn your consent to our privacy policy'); ?></span>
  </div>
  <?php } ?>

    <?php $user = $this->viewer(); ?>
    <?php if($user->getIdentity()){ 
      $consent = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($this->viewer(),'user_consent');
      $consentTime = Engine_Api::_()->getDbTable('settings', 'user')->getSetting($this->viewer(),'user_consent_time');  
    }else{
      if(!empty($_COOKIE['user_consent'])){
        $consent = true;
        $consentTime = $_COOKIE['user_consent_date'];
      }
    }
  ?>
  <?php if(!$consent){ ?>
  <div class="sesgdpr_consent_tip sesgdpr_clearfix">
    <div class="_message">
    	<span class="_checkbox"><input type="checkbox" name="consent" id="consent_checknox" /></span>
    	<span class="_text"><?php echo $this->translate('By checking this checkbox you are providing explicit consent as detailed in the privacy policy below. You have not provided consent yet.'); ?></span>
    </div>
    <div class="_btn"><button class="sesgdpr_consent" data-rel="1"><?php echo $this->translate('Accept'); ?></button></div>
  </div>
<?php } ?>
  <?php if($consent){ ?>
  <div class="sesgdpr_consent_tip sesgdpr_clearfix">
    <div class="_message">
    	<span class="_text"><?php echo $this->translate('You provide consent on'); ?> <strong><?php echo date('l dS \o\f M Y H:i:s A',strtotime($consentTime)); ?></strong></span>
    </div>
    <div class="_btn"><button class="sesgdpr_consent" data-rel="0"><?php echo $this->translate('Withdraw Consent'); ?></button></div>
  </div>
  <?php } ?>
 
  <div class="sesgdpr_consent_tip sesgdpr_clearfix">
    <h3 style="margin:0 0 10px;"><?php echo $this->translate('Strictly Necessary Cookie Settings'); ?></h3>
    <p>
      <?php echo $this->translate('When you visit any website, it may store or retrieve information on your browser, mostly in the form of cookies. This information might be about you, your preferences or your device and is mostly used to make the site work as you expect it to. The information does not usually directly identify you, but it can give you a more personalized web experience.<br>There are some Cookies that are necessary for the site to function properly which you can not disallow.'); ?>
    </p>
    
    <?php $cookieuser = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesconsent_bypass_cookie'); ?>
    <?php if($cookieuser){ ?>
  	<div class="sesgdpr_cookies_used_box">
      <div class="_head">
        <div class="_title"><?php echo $this->translate('Cookies Used'); ?></div>
        <div><button><?php echo $this->translate("Always Active"); ?></button></div>
      </div>
      <div class="_content">
        <code><?php echo str_replace(',',', ',$cookieuser); ?></code>	
      </div>
    </div>
  <?php } ?>
	</div>  
</div>  



<script type="application/javascript">

sesJqueryObject(document).on('click','.sesgdpr_consent',function(){
  var value = sesJqueryObject(this).attr('data-rel');  
  if(value == 1){
    if(!sesJqueryObject('#consent_checknox:checked').length) 
      return; 
  }
  sesJqueryObject('#sesconsent_consent').val(value);
  sesJqueryObject('#sesconsent_consent').closest('form').submit();
})
</script>