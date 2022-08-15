<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: edit-features.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php $settings = Engine_Api::_()->getApi('settings', 'core');
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jscolor/jscolor.js');
?>

<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Ememsub/externals/scripts/jquery1.11.js'); ?>

<div class="ememsub_search_reasult">
  <?php echo $this->htmlLink(array('action' => 'index', 'reset' => false), $this->translate('Back to Manage Feature'), array('class' => 'buttonlink  ememsub_icon_back')) ?>
</div>

<h3>
  <?php echo $this->feature->getTitle(); ?>
</h3>
<div class='clear'>
  <div class='settings ememsub_plan_features_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>  

<script>
  function addNew(obj){
    var ctr = sesJqueryObject(obj).attr('data-ctr');
    var str = '<div id="title_'+ctr+'-wrapper" class="form-wrapper"><div id="title_'+ctr+'-label" class="form-label"><label for="title_'+ctr+'" class="required">Set Feature '+ctr+'</label></div><div id="title_'+ctr+'-element" class="form-element"><p class="description">Please enter Feature Name in the box.</p><input type="text" name="title_'+ctr+'" id="title_'+ctr+'" value="" placeholder="Enter Feature Name"></div></div>';
    sesJqueryObject(str).insertAfter(sesJqueryObject('#title_'+(parseInt(ctr)-1)+'-wrapper'));
    sesJqueryObject(obj).attr('data-ctr',parseInt(ctr)+1);
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
  <?php foreach($this->feature as $key => $elementValue):?>
      showMoreOption('<?php echo $key; ?>', '<?php echo str_replace('""','',Zend_Json_Encoder::encode($elementValue))?>', '1');
  <?php endforeach;?>
  
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
    }
    else {
      jqueryObjectOfSes('.text_row').parent().parent().hide();
      jqueryObjectOfSes('.wrap').parent().parent().parent().removeClass('collapse');
      document.getElementById('expand_all-element').innerHTML = '<p class="expand"><a onclick="showAllOption()" href="javascript:void(0);">Expand all Rows</a></p>';
      showAllOptions = 0;
    }
  }
  
  function showMoreOption(id, elementValue, showElement) {

    var removeColumnArray = ['column_name','column_title', 'column_width','column_row_color','column_row_text_color', 'icon_position', 'currency', 'show_currency', 'currency_value', 'currency_duration', 'column_description', 'footer_text','footer_text_color','footer_bg_color','column_text_color', 'text_url', 'column_color', 'show_highlight'];
    var checkColumn = removeColumnArray.indexOf(id);
    if(checkColumn != '-1')
    return;
   
    if(document.getElementById(id +'-wrapper')) {
      var res = id.split("_").pop(-1); 
      if(res == 'tabshowhide')
      id = id.replace("_tabshowhide", "");
      if(showElement == '1') {
	if(elementValue != '')
	document.getElementById(id +'-wrapper').style.display = 'block';
	else
	document.getElementById(id +'-wrapper').style.display = 'none';
      }
      else {		
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
    }
  }
  
  function showIconOption() {
    if(document.getElementById('row1_file_id-wrapper').getStyle('display') == 'block') {
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
    showLabel('<?php echo $this->feature->show_label;?>');
    showIconOption();
  });
  jqueryObjectOfSes('.upload_icon_row').parent().parent().hide();
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
  <?php foreach($tabs_count as $tab):?>
    if ($('row'+'<?php echo $tab; ?>'+'_icon_preview-wrapper')) {
      var checkFileId = '<?php  echo Engine_Api::_()->ememsub()->isFileIdExist("row$tab", $this->feature_id);?>';
      if(checkFileId == '0') {
    $('row'+'<?php echo $tab; ?>'+'_icon_preview-wrapper').style.display = 'none'; 
      }
      if(checkFileId == '1')
      jqueryObjectOfSes('.upload_icon_row').parent().parent().show();
    }
  <?php endforeach;?>
</script>
