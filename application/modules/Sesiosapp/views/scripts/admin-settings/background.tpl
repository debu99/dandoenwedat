<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: background.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>
<?php if( count($this->navigation) ): ?>
  <div class='sesiosapp-admin-navgation'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<div class="settings sesiosapp_admin_form">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="application/javascript">
scriptJquery('#sesiosapp_image_video').change(function(e){
  var value = scriptJquery(this).val();
  if(value == 1){
    scriptJquery('#sesiosapp_video_slide-wrapper').show();
    scriptJquery('#sesiosapp_login_background_image-wrapper').hide();
    scriptJquery('#sesiosapp_forgot_background_image-wrapper').hide();
  }else{
    scriptJquery('#sesiosapp_video_slide').val('');
    scriptJquery('#sesiosapp_video_slide-wrapper').hide();
    scriptJquery('#sesiosapp_login_background_image-wrapper').show();
    scriptJquery('#sesiosapp_forgot_background_image-wrapper').show();  
  }  
})
scriptJquery('#sesiosapp_image_video').trigger('change');
</script>
