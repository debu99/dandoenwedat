<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->typesmoothbox){ ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Observer.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.Local.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.Request.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/moment.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/moment-timezone.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/moment-timezone-with-data.js'); ?>
<?php }else{ ?>
<script type="application/javascript">
//Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js' ?>");
Sessmoothbox.css.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl .'externals/autocompleter/Observer.js'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.js' ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.Local.js'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.Request.js'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/moment.js'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/moment-timezone.js'; ?>");
Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/moment-timezone-with-data.js'; ?>");
//Sessmoothbox.javascript.push("<?php echo $this->layout()->staticBaseUrl . 'externals/tinymce/tinymce.min.js'; ?>");
</script>
<?php } ?>
<?php if( $this->parent_type == 'group' ) { ?>
  <h2>
    <?php echo $this->group->__toString() ?>
    <?php echo '&#187; '.$this->translate('Events');?>
  </h2>
<?php } ?>

<?php if (($this->current_count >= $this->quota) && !empty($this->quota)):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('You have already uploaded the maximum number of events allowed.');?>
      <?php echo $this->translate('If you would like to upload a new event, please <a href="%1$s">delete</a> an old one first.', $this->url(array('action' => 'manage'), 'sesevent_general'));?>
    </span>
  </div>
  <br/>


<?php else:?>
	<div class="sesevent_create_form">
  	<?php echo $this->form->render();?>
  </div>
<?php endif; ?>

<script type="text/javascript">
//trim last -
function removeLastMinus (myUrl)
{
    if (myUrl.substring(myUrl.length-1) == "-")
    {
        myUrl = myUrl.substring(0, myUrl.length-1);
    }
    return myUrl;
}
var changeTitle = true;
var validUrl = true;
  en4.core.runonce.add(function()
  {
		//auto fill custom url value
sesJqueryObject("#title").keyup(function(){
		var Text = sesJqueryObject(this).val();
	  if(!changeTitle)
			return;
		Text = Text.toLowerCase();
		Text = Text.replace(/[^a-zA-Z0-9]+/g,'-');
		Text = removeLastMinus(Text);
		sesJqueryObject("#custom_url").val(Text);        
});
sesJqueryObject("#title").blur(function(){
		if(sesJqueryObject(this).val()){
				changeTitle = false;
		}
});
sesJqueryObject("#custom_url").blur(function(){
				validUrl = false;
				sesJqueryObject('#check_custom_url_availability').trigger('click');
		});
//function ckeck url availability
sesJqueryObject('#check_custom_url_availability').click(function(){
	var custom_url_value = sesJqueryObject('#custom_url').val();
	if(!custom_url_value)
		return;
	sesJqueryObject('#sesevent_custom_url_wrong').hide();
	sesJqueryObject('#sesevent_custom_url_correct').hide();
	sesJqueryObject('#sesevent_custom_url_loading').css('display','inline-block');
	sesJqueryObject.post('<?php echo $this->url(array('controller' => 'ajax','module'=>'sesevent', 'action' => 'custom-url-check'), 'default', true) ?>',{value:custom_url_value},function(response){
				sesJqueryObject('#sesevent_custom_url_loading').hide();
				response = sesJqueryObject.parseJSON(response);
				if(response.error){
					validUrl = false;
					sesJqueryObject('#sesevent_custom_url_correct').hide();
					sesJqueryObject('#sesevent_custom_url_wrong').css('display','inline-block');
				}else{
						validUrl = true;
						sesJqueryObject('#custom_url').val(response.value);
						sesJqueryObject('#sesevent_custom_url_wrong').hide();
						sesJqueryObject('#sesevent_custom_url_correct').css('display','inline-block');
				}
		});
});
		//tags
    new Autocompleter.Request.JSON('tags', '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>', {
      'postVar' : 'text',
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'className': 'tag-autosuggest',
      'filterSubset' : true,
      'multiple' : true,
      'injectChoice': function(token){
        var choice = new Element('li', {'class': 'autocompleter-choices', 'value':token.label, 'id':token.id});
        new Element('div', {'html': this.markQueryValue(token.label),'class': 'autocompleter-choice'}).inject(choice);
        choice.inputValue = token;
        this.addChoiceEvents(choice).inject(this.choices);
        choice.store('autocompleteChoice', token);
      }
    });
  });

function additionalCostsToggle(){
	if(sesJqueryObject("#is_additional_costs").is(':checked'))
    sesJqueryObject("#additional_costs_amount-wrapper, #additional_costs_description-wrapper").show();  // checked
	else
    sesJqueryObject("#additional_costs_amount-wrapper, #additional_costs_description-wrapper").hide();  // unchecked
}

function customTermAndCondition(){
	if(sesJqueryObject("#is_custom_term_condition, #is_additional_costs-element").is(':checked'))
    sesJqueryObject("#custom_term_condition-wrapper").show();  // checked
	else
    sesJqueryObject("#custom_term_condition-wrapper").hide();  // unchecked
}

sesJqueryObject('#is_custom_term_condition').bind('change', function () {
	customTermAndCondition();
});

customTermAndCondition();

<?php if(Engine_Api::_()->sesevent()->isMultiCurrencyAvailable()){ ?>
	sesJqueryObject('#additional_costs_amount-element').append('<span class="fa fa-retweet sesevent_convert_icon sesbasic_link_btn" id="sesevent_currency_coverter" title="<?php echo $this->translate("Convert to %s",Engine_Api::_()->sesevent()->defaultCurrency());?>"></span>');
	sesJqueryObject('#additional_costs_amount-label').append('<span> (<?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?>)</span>');
<?php }else{ ?>
	sesJqueryObject('#additional_costs_amount-label').append('<span> (<?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?>)</span>');
<?php } ?>
</script>
<?php 
$defaultProfileFieldId = "0_0_$this->defaultProfileId";
$profile_type = 2;
?>
<?php echo $this->partial('_customFields.tpl', 'sesbasic', array()); ?>
<script type="text/javascript">
  var defaultProfileFieldId = '<?php echo $defaultProfileFieldId ?>';
  var profile_type = '<?php echo $profile_type ?>';
  var previous_mapped_level = 0;
	
  function showFields(cat_value, cat_level,typed,isLoad) {
		var categoryId = getProfileType(formObj.find('#category_id-wrapper').find('#category_id-element').find('#category_id').val());
		var subcatId = getProfileType(formObj.find('#subcat_id-wrapper').find('#subcat_id-element').find('#subcat_id').val());
		var subsubcatId = getProfileType(formObj.find('#subsubcat_id-wrapper').find('#subsubcat_id-element').find('#subsubcat_id').val());
		var type = categoryId+','+subcatId+','+subsubcatId;
    if (cat_level == 1 || (previous_mapped_level >= cat_level && previous_mapped_level != 1) || (profile_type == null || profile_type == '' || profile_type == 0)) {
      profile_type = getProfileType(cat_value);
      if (profile_type == 0) {
        profile_type = '';
      } else {
        previous_mapped_level = cat_level;
      }
      $(defaultProfileFieldId).value = profile_type;
      changeFields($(defaultProfileFieldId),null,isLoad,type);
    }
  }
  var getProfileType = function(category_id) {
    var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sesevent')->getMapping(array('category_id', 'profile_type'))); ?>;
		  for (i = 0; i < mapping.length; i++) {	
      	if (mapping[i].category_id == category_id)
        return mapping[i].profile_type;
    	}
    return 0;
  }
  en4.core.runonce.add(function() {
    var defaultProfileId = '<?php echo '0_0_' . $this->defaultProfileId ?>' + '-wrapper';
     if ($type($(defaultProfileId)) && typeof $(defaultProfileId) != 'undefined') {
      $(defaultProfileId).setStyle('display', 'none');
    }
  });
  function showSubCategory(cat_id,selectedId) {
		var selected;
		if(selectedId != ''){
			var selected = selectedId;
		}
    var url = en4.core.baseUrl + 'sesevent/ajax/subcategory/category_id/' + cat_id;
    new Request.HTML({
      url: url,
      data: {
				'selected':selected
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if (formObj.find('#subcat_id-wrapper').length && responseHTML) {
          formObj.find('#subcat_id-wrapper').show();
          formObj.find('#subcat_id-wrapper').find('#subcat_id-element').find('#subcat_id').html(responseHTML);
        } else {
          if (formObj.find('#subcat_id-wrapper').length) {
            formObj.find('#subcat_id-wrapper').hide();
            formObj.find('#subcat_id-wrapper').find('#subcat_id-element').find('#subcat_id').html( '<option value="0"></option>');
          }
        }
			  if (formObj.find('#subsubcat_id-wrapper').length) {
            formObj.find('#subsubcat_id-wrapper').hide();
            formObj.find('#subsubcat_id-wrapper').find('#subsubcat_id-element').find('#subsubcat_id').html( '<option value="0"></option>');
          }
				//showFields(cat_id,1);
      }
    }).send(); 
  }
	function showSubSubCategory(cat_id,selectedId,isLoad) {
		var categoryId = getProfileType($('category_id').value);
		if(cat_id == 0){
			if (formObj.find('#subsubcat_id-wrapper').length) {
            formObj.find('#subsubcat_id-wrapper').hide();
            formObj.find('#subsubcat_id-wrapper').find('#subsubcat_id-element').find('#subsubcat_id').html( '<option value="0"></option>');
						document.getElementsByName("0_0_1")[0].value=categoryId;		
      }
			showFields(cat_id,1,categoryId);
			return false;
		}
		showFields(cat_id,1,categoryId);
		var selected;
		if(selectedId != ''){
			var selected = selectedId;
		}
    var url = en4.core.baseUrl + 'sesevent/ajax/subsubcategory/subcategory_id/' + cat_id;
    (new Request.HTML({
      url: url,
      data: {
				'selected':selected
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if (formObj.find('#subsubcat_id-wrapper').length && responseHTML) {
          formObj.find('#subsubcat_id-wrapper').show();
          formObj.find('#subsubcat_id-wrapper').find('#subsubcat_id-element').find('#subsubcat_id').html(responseHTML);
        } else {
          if (formObj.find('#subsubcat_id-wrapper').length) {
            formObj.find('#subsubcat_id-wrapper').hide();
            formObj.find('#subsubcat_id-wrapper').find('#subsubcat_id-element').find('#subsubcat_id').html( '<option value="0"></option>');
          }
        }				
			}
    })).send();  
  }
	function showCustom(value,isLoad){
		var categoryId = getProfileType(formObj.find('#category_id-wrapper').find('#category_id-element').find('#category_id').val());
		var subcatId = getProfileType(formObj.find('#subcat_id-wrapper').find('#subcat_id-element').find('#subcat_id').val());
		var id = categoryId+','+subcatId;
			showFields(value,1,id,isLoad);
		if(value == 0)
			document.getElementsByName("0_0_1")[0].value=subcatId;	
			return false;
	}
	function showCustomOnLoad(value,isLoad){
	 <?php if(isset($this->category_id) && $this->category_id != 0){ ?>
		var categoryId = getProfileType(<?php echo $this->category_id; ?>)+',';
		<?php if(isset($this->subcat_id) && $this->subcat_id != 0){ ?>
		var subcatId = getProfileType(<?php echo $this->subcat_id; ?>)+',';
		<?php  }else{ ?>
		var subcatId = '';
		<?php } ?>
		<?php if(isset($this->subsubcat_id) && $this->subsubcat_id != 0){ ?>
		var subsubcat_id = getProfileType(<?php echo $this->subsubcat_id; ?>)+',';
		<?php  }else{ ?>
		var subsubcat_id = '';
		<?php } ?>
		var id = (categoryId+subcatId+subsubcat_id).replace(/,+$/g,"");;
			showFields(value,1,id,isLoad);
		if(value == 0)
			document.getElementsByName("0_0_1")[0].value=subcatId;	
			return false;
		<?php }else{ ?>
			showFields(value,1,'',isLoad);
		<?php } ?>
	}
   en4.core.runonce.add(function(){
		 	formObj = sesJqueryObject('#sesevent_create_form').find('div').find('div').find('div');
			var sesdevelopment = 1;
			<?php if(isset($this->category_id) && $this->category_id != 0){ ?>
					<?php if(isset($this->subcat_id)){$catId = $this->subcat_id;}else $catId = ''; ?>
					showSubCategory('<?php echo $this->category_id; ?>','<?php echo $catId; ?>','yes');
			 <?php  }else{ ?>
				formObj.find('#subcat_id-wrapper').hide();
			 <?php } ?>
			 <?php if(isset($this->subsubcat_id) && $this->subsubcat_id != 0){ ?>
				if (<?php echo isset($this->subcat_id) && intval($this->subcat_id) > 0 ? $this->subcat_id : 'sesdevelopment' ?> == 0) {
				 formObj.find('#subsubcat_id-wrapper').hide();
				} else {
					<?php if(isset($this->subsubcat_id)){$subsubcat_id = $this->subsubcat_id;}else $subsubcat_id = ''; ?>
					showSubSubCategory('<?php echo $this->subcat_id; ?>','<?php echo $this->subsubcat_id; ?>','yes');
				}
			 <?php }else{ ?>
					 formObj.find('#subsubcat_id-wrapper').hide();
			 <?php } ?>
	 		showCustomOnLoad('','no');
  });

//drag drop photo upload
 en4.core.runonce.add(function()
  {
	if(sesJqueryObject('#dragandrophandlerbackground').hasClass('requiredClass')){
		sesJqueryObject('#dragandrophandlerbackground').parent().parent().find('#photouploader-label').find('label').addClass('required').removeClass('optional');	
	}
	$('photouploader-wrapper').style.display = 'block';
	$('event_main_photo_preview-wrapper').style.display = 'none';
	$('photo-wrapper').style.display = 'none';

var obj = sesJqueryObject('#dragandrophandlerbackground');
obj.click(function(e){
	sesJqueryObject('#photo').val('');
	sesJqueryObject('#event_main_photo_preview').attr('src','');
  sesJqueryObject('#photo').trigger('click');
});
obj.on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
    sesJqueryObject (this).addClass("sesbd");
});
obj.on('dragover', function (e) 
{
     e.stopPropagation();
     e.preventDefault();
});
obj.on('drop', function (e) 
{
		 sesJqueryObject (this).removeClass("sesbd");
		 sesJqueryObject (this).addClass("sesbm");
     e.preventDefault();
     var files = e.originalEvent.dataTransfer;
     handleFileBackgroundUpload(files,'event_main_photo_preview');
});
sesJqueryObject (document).on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});
sesJqueryObject (document).on('dragover', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
});
	sesJqueryObject (document).on('drop', function (e) 
	{
			e.stopPropagation();
			e.preventDefault();
	});
});
function handleFileBackgroundUpload(input,id) {
  var url = input.value; 
  if(typeof url == 'undefined')
    url = input.files[0]['name'];
  var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
  if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
    var reader = new FileReader();
    reader.onload = function (e) {
     // $(id+'-wrapper').style.display = 'block';
      $(id).setAttribute('src', e.target.result);
    }
    $('photouploader-element').style.display = 'none';
    $('removeimage-wrapper').style.display = 'block';
    $('removeimage1').style.display = 'inline-block';
    $('event_main_photo_preview').style.display = 'block';
    $('event_main_photo_preview-wrapper').style.display = 'block';
    reader.readAsDataURL(input.files[0]);
  }
}
function removeImage() {
	$('photouploader-element').style.display = 'block';
	$('removeimage-wrapper').style.display = 'none';
	$('removeimage1').style.display = 'none';
	$('event_main_photo_preview').style.display = 'none';
	$('event_main_photo_preview-wrapper').style.display = 'none';
	$('event_main_photo_preview').src = '';
	$('MAX_FILE_SIZE').value = '';
	$('removeimage2').value = '';
	$('photo').value = '';
}
//validate form
//Ajax error show before form submit
var error = false;
var objectError ;
var counter = 0;
function validateForm(){
		var errorPresent = false;
		counter = 0;
		sesJqueryObject('#sesevent_create_form input, #sesevent_create_form select,#sesevent_create_form checkbox,#sesevent_create_form textarea,#sesevent_create_form radio').each(
				function(index){
						var input = sesJqueryObject(this);
                  var checkRegion = (
                          sesJqueryObject(this).hasClass('required') &&
                          sesJqueryObject(this).hasClass('region') &&
                          sesJqueryObject(this).css('display') !== 'none'
                  );
                  if (
                        (
                              sesJqueryObject(this).closest('div').parent().not('fieldset').css('display') != 'none' &&
                              sesJqueryObject(this).closest('div').parent().not('fieldset').find('.form-label').find('label').first().hasClass('required') &&
                              sesJqueryObject(this).prop('type') != 'hidden' &&
                              sesJqueryObject(this).closest('div').parent().not('fieldset').attr('class') != 'form-elements'
                        ) || checkRegion
                  ) {
						  if(sesJqueryObject(this).prop('type') == 'checkbox'){
								value = '';
								if(sesJqueryObject('input[name="'+sesJqueryObject(this).attr('name')+'"]:checked').length > 0) { 
										value = 1;
								};
								if(value == '')
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'select-multiple'){
								if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'select-one' || sesJqueryObject(this).prop('type') == 'select' ){
								if(sesJqueryObject(this).val() === '')
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'radio'){
								if(sesJqueryObject("input[name='"+sesJqueryObject(this).attr('name').replace('[]','')+"']:checked").val() === '')
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'textarea'){
								if(sesJqueryObject(this).css('display') == 'none'){
								 var	content = tinymce.get(sesJqueryObject(this).attr('id')).getContent();
								 if(!content)
								 	error= true;
								 else
								 	error = false;
								}else	if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
									error = true;
								else
									error = false;
							}else{
								if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
									error = true;
								else
									error = false;
							}
							if(error){
							 if(counter == 0){
							 	objectError = this;
							 }
								counter++
							}else{
									if(sesJqueryObject('#photo').length && sesJqueryObject('#photo').val() === '' && sesJqueryObject('#photouploader-label').find('label').hasClass('required')){
											objectError = sesJqueryObject('#dragandrophandlerbackground');
											error = true;
									}
							}
							if(error)
								errorPresent = true;
							error = false;
						}
				}
			);
			return errorPresent ;
}
en4.core.runonce.add(function()
  {
sesJqueryObject('#sesevent_create_form').submit(function(e){
					var validationFm = validateForm();
					if(validationFm)
					{
						alert('<?php echo $this->translate("Please fill the red mark fields"); ?>');
						if(typeof objectError != 'undefined'){
						 var errorFirstObject = sesJqueryObject(objectError).parent().parent();
						 sesJqueryObject('html, body').animate({
							scrollTop: errorFirstObject.offset().top
						 }, 2000);
						}
						return false;
					}else{
                                            if (sesJqueryObject('#sesevent_start_time').length && sesJqueryObject('#sesevent_end_time').length) {
						var lastTwoDigit = sesJqueryObject('#sesevent_end_time').val().slice('-2');
						var endDate = new Date(sesJqueryObject('#sesevent_end_date').val()+' '+sesJqueryObject('#sesevent_end_time').val().replace(lastTwoDigit,'')+':00 '+lastTwoDigit);
						var lastTwoDigitStart = sesJqueryObject('#sesevent_start_time').val().slice('-2');
						var startDate = new Date(sesJqueryObject('#sesevent_start_date').val()+' '+sesJqueryObject('#sesevent_start_time').val().replace(lastTwoDigitStart,'')+':00 '+lastTwoDigitStart);
						var error = checkDateTime(startDate,endDate);
						if(error != ''){
							sesJqueryObject('#event_error_time-wrapper').show();
							sesJqueryObject('#event_error_time-element').text(error);
						  var errorFirstObject = sesJqueryObject('#starteventid').parent().parent();
						  sesJqueryObject('html, body').animate({
							 scrollTop: errorFirstObject.offset().top
						  }, 2000);
						  return false;
						}else{
							sesJqueryObject('#event_error_time-wrapper').hide();
						}
                                            }
                                            if(!validUrl){
                                                    objectError = sesJqueryObject('#custom_url');
                                                    alert('<?php echo $this->translate("Invalid Custom Url"); ?>');
                                                    if(typeof objectError != 'undefined'){
                                                     var errorFirstObject = sesJqueryObject(objectError).parent().parent();
                                                     sesJqueryObject('html, body').animate({
                                                            scrollTop: errorFirstObject.offset().top
                                                     }, 2000);
                                                    }
                                                return false;	
                                            }else{
                                                    sesJqueryObject('#submit').attr('disabled',true);
                                                    sesJqueryObject('#submit').html('<?php echo $this->translate("Submitting Form ...") ; ?>');
                                                    return true;
                                            }
					}
	});
});

</script>

<?php if($this->typesmoothbox) { ?>
	<script type="application/javascript">
	executetimesmoothboxTimeinterval = 200;
	executetimesmoothbox = true;
	function showHideOptions(display){
		var elem = sesJqueryObject('.sesevent_hideelement_smoothbox');
		for(var i = 0 ; i < elem.length ; i++){
				sesJqueryObject(elem[i]).parent().parent().css('display',display);
		}
	}
	function checkSetting(first){
		var hideShowOption = sesJqueryObject('#advanced_options').hasClass('active');
			if(hideShowOption){
					showHideOptions('none');
					if(typeof first == 'undefined'){
						sesJqueryObject('#advanced_options').html("<i class='fa fa-plus-circle'></i><?php echo $this->translate('Show Advanced Settings') ?>");
					}
					sesJqueryObject('#advanced_options').removeClass('active');
			}else{
					showHideOptions('block');
					sesJqueryObject('#advanced_options').html("<i class='fa fa-minus-circle'></i><?php echo $this->translate('Hide Advanced Settings') ?>");
						sesJqueryObject('#advanced_options').addClass('active');
			}	
	}
	en4.core.runonce.add(function()
  {
		sesJqueryObject('#advanced_options').click(function(e){
			checkSetting();
		});
		sesJqueryObject('#advanced_options').html("<i class='fa fa-plus-circle'></i><?php echo $this->translate('Show Advanced Settings') ?>");
		checkSetting('true');
		tinymce.init({
			mode: "specific_textareas",
			plugins: "table,fullscreen,media,preview,paste,code,image,textcolor,jbimages,link",
			theme: "modern",
			menubar: false,
			statusbar: false,
			toolbar1:  "undo,redo,removeformat,pastetext,|,code,media,image,jbimages,link,fullscreen,preview",
			toolbar2: "fontselect,fontsizeselect,bold,italic,underline,strikethrough,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify,|,bullist,numlist,|,outdent,indent,blockquote",
			toolbar3: "",
			element_format: "html",
			height: "225px",
      content_css: "bbcode.css",
      entity_encoding: "raw",
      add_unload_trigger: "0",
      remove_linebreaks: false,
			convert_urls: false,
			language: "<?php echo $this->language; ?>",
			directionality: "<?php echo $this->direction; ?>",
			upload_url: "<?php echo $this->url(array('module' => 'sesbasic', 'controller' => 'index', 'action' => 'upload-image'), 'default', true); ?>",
			editor_selector: "tinymce"
		});
	});
  </script>	
<?php	die; 	} ?>