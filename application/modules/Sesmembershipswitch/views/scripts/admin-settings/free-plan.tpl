<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesmembershipswitch
 * @package    Sesmembershipswitch
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: free-plan.tpl  2018-10-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php include APPLICATION_PATH .  '/application/modules/Sesmembershipswitch/views/scripts/dismiss_message.tpl';
?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
<div class="clear">
  <div class='settings sesbasic_admin_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="application/javascript">

function changePlan(value){
  window.location.href = 'admin/sesmembershipswitch/settings/free-plan/plan_id/'+value;  
}
sesJqueryObject('#notification_switch').on('change',function(){
    sesJqueryObject('#notification').val(sesJqueryObject(this).val());
});

sesJqueryObject('#type_switch').on('change',function(){
    sesJqueryObject('#type').val(sesJqueryObject(this).val());
});
sesJqueryObject('#number_switch').on('keydown',function(e){
   // Allow: backspace, delete, tab, escape, enter and .
    if (sesJqueryObject.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
         // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
         // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
             // let it happen, don't do anything
             return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});
sesJqueryObject('#number_switch').on('keyup',function(){
    sesJqueryObject('#number').val(sesJqueryObject(this).val());
});

sesJqueryObject(document).ready(function(e){
    sesJqueryObject('#number_switch').val(sesJqueryObject('#number').val());
    sesJqueryObject('#type_switch').val(sesJqueryObject('#type').val());
    sesJqueryObject('#notification_switch').val(sesJqueryObject('#notification').val());
})

</script>