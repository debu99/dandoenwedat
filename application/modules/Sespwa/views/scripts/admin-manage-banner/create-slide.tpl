<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create-slide.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jscolor/jscolor.js');
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
?>
<?php include APPLICATION_PATH .  '/application/modules/Sespwa/views/scripts/dismiss_message.tpl';?>
<div class="sesbasic_search_reasult">
	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sespwa', 'controller' => 'manage-banner', 'action' => 'manage','id'=>$this->banner_id), $this->translate("Back to Manage Photo Slides") , array('class'=>'sesbasic_icon_back buttonlink')); ?>
</div>
<div class='clear'>
  <div class='settings sesbasic_admin_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script type="application/javascript">

function extra_buton(value){
	if(value == 1)
		jqueryObjectOfSes('div[id^="extra_button_"]').show();
	else
		jqueryObjectOfSes('div[id^="extra_button_"]').hide();
	
	jqueryObjectOfSes('#extra_button_-wrapper').show();
}
extra_buton(jqueryObjectOfSes('#extra_button').val());
</script>
<style type="text/css">
.settings div.form-label label.required:after{
	content:" *";
	color:#f00;
}
</style>
