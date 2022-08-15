<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: background.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
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
<script type="application/javascript">
scriptJquery('#sesandroidapp_image_video').change(function(e){
  var value = scriptJquery(this).val();
  if(value == 1){
    scriptJquery('#sesandroidapp_video_slide-wrapper').show();
    scriptJquery('#sesandroidapp_login_background_image-wrapper').hide();
    scriptJquery('#sesandroidapp_forgot_background_image-wrapper').hide();
  }else{
    scriptJquery('#sesandroidapp_video_slide').val('');
    scriptJquery('#sesandroidapp_video_slide-wrapper').hide();
    scriptJquery('#sesandroidapp_login_background_image-wrapper').show();
    scriptJquery('#sesandroidapp_forgot_background_image-wrapper').show();  
  }  
})
scriptJquery('#sesandroidapp_image_video').trigger('change');
</script>
