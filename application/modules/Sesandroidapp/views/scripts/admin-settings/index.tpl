<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesandroidapp/views/scripts/dismiss_message.tpl';?>

<?php if( count($this->navigation) ): ?>
  <div class='sesandroidapp-admin-navgation'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<div class="settings sesandroidapp_admin_form">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<div class="sesandroidapp_waiting_msg_box" style="display:none;">
	<div class="sesandroidapp_waiting_msg_box_cont">
    <?php echo $this->translate("Please wait.. It might take some time to activate plugin."); ?>
    <i></i>
  </div>
</div>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesandroidapp.pluginactivated',0)){  ?>
	<script type="application/javascript">
  	scriptJquery('.global_form').submit(function(e){
			scriptJquery('.sesandroidapp_waiting_msg_box').show();
		});
  </script>
<?php } ?>
