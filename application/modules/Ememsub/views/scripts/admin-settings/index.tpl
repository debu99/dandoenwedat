<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/jquery.min.js'); ?>
<?php 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jscolor/jscolor.js');
?>

<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>
<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php $enablefooter = Engine_Api::_()->getApi('settings', 'core')->getSetting('ememsub.footer.enable', 1);?>
<script type="text/javascript">
  showFooterNote('<?php echo $enablefooter;?>');
  function showFooterNote(value) {
    if(value == 1)
      scriptJquery('#ememsub_footer_note-wrapper').show();
    else
      scriptJquery('#ememsub_footer_note-wrapper').hide();
  }
</script>
<div class="ememsub_waiting_msg_box" style="display:none;">
	<div class="ememsub_waiting_msg_box_cont">
    <?php echo $this->translate("Please wait.. It might take some time to activate plugin."); ?>
    <i></i>
  </div>
</div>

<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('ememsub.pluginactivated',0)) { ?>
	<script type="application/javascript">
  	scriptJquery('.global_form').submit(function(e){
			scriptJquery('.ememsub_waiting_msg_box').show();
		});
  </script>
<?php } ?>
