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

<?php if($this->gdpr_bannerstyle == 'left' || $this->gdpr_bannerstyle == 'right') { ?>
<div class="sesgdpr_sb_cookie_banner <?php if($this->gdpr_bannerstyle == 'left') { ?> _pl <?php } else { ?> _pr <?php } ?> sesgdpr_bxs">
  <div class="_des"><?php echo $this->translate($this->gdpr_bannertext)?></div>
	<div class="_links">
  	<div><a href="sesgdpr/index/consent/type/1"><?php echo $this->translate("Accept");?></a></div>
    <div><a href="<?php echo $this->url(array('action' => 'index'), 'sesgdpr_view', true); ?>"><?php echo $this->translate("Change Settings");?></a></div>
    <div><a href="<?php echo $this->gdpr_privacyurl; ?>"><?php echo $this->translate("Read More");?></a></div>
  </div>
</div>
<style type="text/css">
.sesgdpr_sb_cookie_banner{
	background-color:#<?php echo $this->gdpr_bannerbackgroundcolor ?>;
}
.sesgdpr_sb_cookie_banner ._des{
	color:#<?php echo $this->gdpr_bannertextcolor ?>;
}
.sesgdpr_sb_cookie_banner ._links a{
	color:#<?php echo $this->gdpr_bannerlinkcolor ?>;
}
</style>

<?php } else if($this->gdpr_bannerstyle == 'top_center' || $this->gdpr_bannerstyle == 'bottom_center') { ?>
<div class="sesgdpr_cookie_banner <?php if($this->gdpr_bannerstyle == 'top_center') { ?> _ft <?php } else { ?> _fb <?php } ?> sesgdpr_bxs">
  <div class="_des"><?php echo $this->translate($this->gdpr_bannertext)?></div>
  <?php if(in_array('changeSettings', $this->gdpr_banneroption) || in_array('accept', $this->gdpr_banneroption) || in_array('readMore', $this->gdpr_banneroption)) { ?>
    <div class="_links">
      <?php if(in_array('accept', $this->gdpr_banneroption)) { ?>
        <div><a href="sesgdpr/index/consent/type/1"><?php echo $this->translate("Accept");?></a></div>
      <?php } ?>
      <?php if(in_array('changeSettings', $this->gdpr_banneroption)) { ?>
        <div><a href="<?php echo $this->url(array('action' => 'index'), 'sesgdpr_view', true); ?>"><?php echo $this->translate("Change Settings");?></a></div>
      <?php } ?>
      <?php if(in_array('readMore', $this->gdpr_banneroption)) { ?>
        <div><a href="<?php echo $this->gdpr_privacyurl; ?>"><?php echo $this->translate("Read More");?></a></div>
      <?php } ?>
    </div>
  <?php } ?>
</div>
<style type="text/css">
.sesgdpr_cookie_banner{
	background-color:#<?php echo $this->gdpr_bannerbackgroundcolor ?>;
}
.sesgdpr_cookie_banner ._des{
	color:#<?php echo $this->gdpr_bannertextcolor ?>;
}
.sesgdpr_cookie_banner ._links a{
	color:#<?php echo $this->gdpr_bannerlinkcolor ?>;
}
</style>
<?php } ?>

<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('gdpr.popup',1) && (($this->viewer()->getIdentity() && !Engine_Api::_()->getDbTable('settings', 'user')->getSetting($this->viewer(),'gdpr_popup_consent')) || (!$this->viewer()->getIdentity() && empty($_COOKIE['user_popup_consent'])))){ ?>
<!-- privacy policy popup -->
<div class="sesgdpr_privacy_popup sesgdpr_bxs">
	<div class="sesgdpr_privacy_popup_inner">
  	<h3><?php echo $this->translate('Our Privacy Policy has been Updated'); ?></h3>
    <p><b><?php echo $this->translate("To continue using the site you need to read the revised version and agree to the policies"); ?></b></p>
    <div id="privacy_pop" class="_privacy_data sesbasic_html_block">
    	<?php
        $str = $this->translate('_CORE_PRIVACY_STATEMENT');
        if ($str == strip_tags($str)) {
          // there is no HTML tags in the text
          echo nl2br($str);
        } else {
          echo $str;
        }
    	?>
    </div>
    <div class="sesgdpr_agree_disagree">
      <a href="javascript:;" class="privacy_agree"><?php echo $this->translate('Agree'); ?></a>
      <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('gdpr_madatory_popup',0)){ ?>
      <a href="javascript:;" class="privacy_disagree"><?php echo $this->translate('Disagree');
          }
        ?></a>
  	</div>
	</div>
</div>

<?php $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('gdpr_content', array('cookie','dataProtection','privacySettings','requestArchive','unsubscribe','forgotMe'));
 ?>

<a href="javascript:;" data-url="sesgdpr/index/get-gdpr-data/type/forget" class="sessmoothbox" id="sesconsent_forgot_me" style="display:none;">forgotme</a>
<script type="application/javascript">
sesJqueryObject('.privacy_agree').click(function(e){
  window.location.href = "sesgdpr/index/consent/type/1";
});
</script>
<script type="application/javascript">
sesJqueryObject('.privacy_disagree').click(function(){
  sesJqueryObject('.sesgdpr_privacy_popup').hide();
  sesJqueryObject.post('sesgdpr/index/popup',{},function(response){});
  <?php if(in_array('forgotMe',$settings)){ ?>
  sesJqueryObject('#sesconsent_forgot_me').trigger('click');
  <?php } ?>
})
</script>
<?php } ?>