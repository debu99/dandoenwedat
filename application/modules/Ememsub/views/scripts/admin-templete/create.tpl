<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: create.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core');
  $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jscolor/jscolor.js');
  $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery.min.js');
?>
<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>
<div class="settings">
  <div class="ememsub_search_reasult">
     <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'templates','template_id'=>$this->template_id), $this->translate("Back to Manage Template Styles"), array('class' => 'buttonlink ememsub_icon_back')); ?> 
  </div>
  <div class="ememsub_plan_style_form">
  	<?php echo $this->form->render($this) ?>
  </div>
</div>
<script>
  en4.core.runonce.add(function() {
    showLabel(sesJqueryObject('input[name=show_label]:checked').val());
  });
  function showLabel(value){ 
    if(value == 1) {
      $('label_text-wrapper').style.display = "block";
      $('label_color-wrapper').style.display = "block";
      $('label_text_color-wrapper').style.display = "block";
      $('label_position-wrapper').style.display = "block";
    } else {
      $('label_text-wrapper').style.display = "none";
      $('label_color-wrapper').style.display = "none";
      $('label_text_color-wrapper').style.display = "none";
      $('label_position-wrapper').style.display = "none";
    }
  }
</script>
