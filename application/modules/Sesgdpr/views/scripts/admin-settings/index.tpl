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

<?php include APPLICATION_PATH .  '/application/modules/Sesgdpr/views/scripts/dismiss_message.tpl';?>

<div class='clear'>
  <div class='settings sesbasic_admin_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<div class="sesbasic_waiting_msg_box" style="display:none;">
	<div class="sesbasic_waiting_msg_box_cont">
    <?php echo $this->translate("Please wait.. It might take some time to activate plugin."); ?>
    <i></i>
  </div>
</div>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesgdpr.pluginactivated',0)){ 
 $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
	<script type="application/javascript">
  	sesJqueryObject('.global_form').submit(function(e){
			sesJqueryObject('.sesbasic_waiting_msg_box').show();
		});
  </script>
<?php } ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
     
<script type="application/javascript">
/*sesJqueryObject(document).on('change','input[name=gdpr_popup]',function(){
  if(sesJqueryObject(this).val() != 0){
    sesJqueryObject('#gdpr_madatory_popup-wrapper').show();
  }else{
    sesJqueryObject('#gdpr_madatory_popup-wrapper').hide();  
  }  
});

sesJqueryObject('input[name=gdpr_popup]:checked').trigger('change');*/

</script>