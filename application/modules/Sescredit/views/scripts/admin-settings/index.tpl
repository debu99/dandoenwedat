<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
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

<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit.pluginactivated',0)){
 $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
	<script type="application/javascript">
  	sesJqueryObject('.global_form').submit(function(e){
			sesJqueryObject('.sesbasic_waiting_msg_box').show();
		});
  </script>
<?php } ?>

<script type="application/javascript">
  sesJqueryObject(document).on('change','input[type=radio][name=sescredit_endtime]',function(){
    if (this.value == 1) {
      sesJqueryObject('#credit_end_time-wrapper').hide();
    }else{
      sesJqueryObject('#credit_end_time-wrapper').show();
    }
  });
  window.addEvent('domready', function() {
    var valueStyle = sesJqueryObject('input[name=sescredit_endtime]:checked').val();
    if(valueStyle == 1) {
      sesJqueryObject('#credit_end_time-wrapper').hide();
    }
    else {
      sesJqueryObject('#credit_end_time-wrapper').show();
    }
  });
</script>
