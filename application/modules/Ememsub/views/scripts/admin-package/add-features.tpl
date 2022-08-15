<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: add-features.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jscolor/jscolor.js');
?>
<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery1.11.js'); ?>
<div class="ememsub_search_reasult">
  <?php echo $this->htmlLink(array('action' => 'index', 'reset' => false), $this->translate('Back to Manage Features'), array('class' => 'buttonlink  ememsub_icon_back')) ?>
</div>

<h3><?php echo $this->package->getTitle(); ?></h3>
<div class='clear'>
  <div class='settings ememsub_plan_features_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>


<script>
  function addNew(obj){
    var ctr = jqueryObjectOfSes(obj).attr('data-ctr');
    var str = '<div id="title_'+ctr+'-wrapper" class="form-wrapper"><div id="title_'+ctr+'-label" class="form-label"><label for="title_'+ctr+'" class="required">Set Feature '+ctr+'</label></div><div id="title_'+ctr+'-element" class="form-element"><p class="description">Please enter Feature Name in the box.</p><input type="text" name="title_'+ctr+'" id="title_'+ctr+'" value="" placeholder="Enter Feature Name"></div></div>';
    jqueryObjectOfSes(str).insertAfter(jqueryObjectOfSes('#title_'+(parseInt(ctr)-1)+'-wrapper'));
    jqueryObjectOfSes(obj).attr('data-ctr',parseInt(ctr)+1);
  }
</script>

<script type="text/javascript">
  <?php 
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $rowCount = $settings->getSetting('ememsub.table.row',4);
    $tabs_count = array();
    for ($i = 1; $i <= $rowCount; $i++) {
      $tabs_count[] =  $i;
    }
  ?>
  <?php foreach($tabs_count as $tab):?>
    if ($('row'+'<?php echo $tab;?>'+'_icon_preview-wrapper'))
      $('row'+'<?php echo $tab;?>'+'_icon_preview-wrapper').style.display = 'none';
    <?php    
      $localeObject = Zend_Registry::get('Locale');
      $languages = Zend_Locale::getTranslationList('language', $localeObject);
      $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
      $translate = Zend_Registry::get('Zend_Translate');
      $languageList = $translate->getList();
    ?>
    <?php foreach ($languageList as $key => $language):?>
      <?php $key = explode('_', $key);?>
      <?php $key = $key[0];?>
      <?php if ($language == 'en'):?>
	<?php $id = "row$tab";?>
      <?php else:?>
	<?php $id = $language."_row$tab";?>
      <?php endif;?>
      document.getElementById('<?php echo $id ?>'+'_text-wrapper').style.display = 'none';
      document.getElementById('<?php echo $id ?>'+'_description-wrapper').style.display = 'none';
    <?php endforeach;?>
  <?php endforeach;?>
  showIconOption();//jqueryObjectOfSes('.upload_icon_row').parent().parent().hide();
	jqueryObjectOfSes(document).ready(function(e){
		jqueryObjectOfSes('.wrap').parent().parent().parent().addClass('showhide_element');
	});
  var showAllOptions = 0;
  function showAllOption() {
    if(showAllOptions == '0') {
      jqueryObjectOfSes('.text_row').parent().parent().show();
			jqueryObjectOfSes('.wrap').parent().parent().parent().addClass('collapse');
      document.getElementById('expand_all-element').innerHTML = '<p class="collapse"><a onclick="showAllOption()" href="javascript:void(0);">Collapse all Rows</a></p>';
      showAllOptions = 1;
    }else {
      jqueryObjectOfSes('.text_row').parent().parent().hide();
			jqueryObjectOfSes('.wrap').parent().parent().parent().removeClass('collapse');
      document.getElementById('expand_all-element').innerHTML = '<p class="expand"><a onclick="showAllOption()" href="javascript:void(0);">Expand all Rows</a></p>';
      showAllOptions = 0;
    }
  }
  function showMoreOption(id) {
    var res = id.split("_").pop(-1); 
    if(res == 'tabshowhide')
    id = id.replace("_tabshowhide", "");
    if(document.getElementById(id + '_text-wrapper').getStyle('display') == 'block') {
			document.getElementById(id + '_tabshowhide-wrapper').removeClass('collapse');
      document.getElementById(id + '_text-wrapper').style.display = 'none';
      document.getElementById(id + '_description-wrapper').style.display = 'none';
    }
    else {
      document.getElementById(id + '_tabshowhide-wrapper').addClass('collapse');	
		  document.getElementById(id + '_text-wrapper').style.display = 'block';
      document.getElementById(id + '_description-wrapper').style.display = 'block';
    }
  }
  
  function showIconOption() {
    if(document.getElementById('row1_file_id-wrapper').getStyle('display') == 'block' || document.getElementById('row1_file_id-wrapper').getStyle('display') == 'flex') {
     jqueryObjectOfSes('.upload_icon_row').parent().parent().hide();
     jqueryObjectOfSes('.preview_icon_row').parent().parent().hide();
     jqueryObjectOfSes('.remove_icon_row').parent().parent().hide();
		 jqueryObjectOfSes('.file-wrap').parent().parent().parent().removeClass('collapse');
    }
    else { 
      jqueryObjectOfSes('.upload_icon_row').parent().parent().show();
      jqueryObjectOfSes('.preview_icon_row').parent().parent().show();
      jqueryObjectOfSes('.remove_icon_row').parent().parent().show();
			jqueryObjectOfSes('.file-wrap').parent().parent().parent().addClass('collapse');
    }
  }
  //Show choose image 
  function showReadImage(input, id) {
    var url = input.value;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $(id + '-wrapper').style.display = 'block';
        $(id).setAttribute('src', e.target.result);
      }
      $(id + '-wrapper').style.display = 'block';
      reader.readAsDataURL(input.files[0]);
    }
  }
  window.addEvent('domready', function() {
    showLabel(0);
  });
  function showLabel(value) {
    if(value == '1') {
      if(document.getElementById('label_text-wrapper'))
      document.getElementById('label_text-wrapper').style.display = 'block';
      if(document.getElementById('label_text_color-wrapper'))
      document.getElementById('label_text_color-wrapper').style.display = 'block';
      if(document.getElementById('label_color-wrapper'))
      document.getElementById('label_color-wrapper').style.display = 'block';
      if(document.getElementById('label_position-wrapper'))
      document.getElementById('label_position-wrapper').style.display = 'block';
    }
    else {
      if(document.getElementById('label_text-wrapper'))
      document.getElementById('label_text-wrapper').style.display = 'none';
      if(document.getElementById('label_text_color-wrapper'))
      document.getElementById('label_text_color-wrapper').style.display = 'none';
      if(document.getElementById('label_color-wrapper'))
      document.getElementById('label_color-wrapper').style.display = 'none';
      if(document.getElementById('label_position-wrapper'))
      document.getElementById('label_position-wrapper').style.display = 'none';
    }
  }
</script>
