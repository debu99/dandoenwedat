<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: edit.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $optionsenableglotion = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('optionsenableglotion','a:6:{i:0;s:7:"country";i:1;s:5:"state";i:2;s:4:"city";i:3;s:3:"zip";i:4;s:3:"lat";i:5;s:3:"lng";}')); ?>
<style>
.tag img{
	float:left;
	height:25px;
	width:25px;
}
</style>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/calendar/calendar.compat.js'); ?>
<?php	$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'externals/calendar/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Observer.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.Local.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'externals/autocompleter/Autocompleter.Request.js'); ?>
<?php if(!$this->is_ajax){ ?>
<?php
echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
		echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
		'event' => $this->event,
		));	
		?>
    <div class="sesbasic_dashboard_form sesevent_create_form">
			<?php echo $this->form->render() ?>
    </div>
		<?php if(!$this->is_ajax){ ?>
	</div>
	</div>
</div>
</div>
<?php  } ?>
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
//auto fill custom url value
jqueryObjectOfSes("#title").keyup(function(){
		var Text = jqueryObjectOfSes(this).val();
		Text = Text.toLowerCase();
		Text = Text.replace(/[^a-zA-Z0-9]+/g,'-');
		Text = removeLastMinus(Text);
		jqueryObjectOfSes("#custom_url").val(Text);        
});
//function ckeck url availability
jqueryObjectOfSes('#check_custom_url_availability').click(function(){
	var custom_url_value = jqueryObjectOfSes('#custom_url').val();
	if(!custom_url_value)
		return;
	jqueryObjectOfSes.post('<?php echo $this->url(array('controller' => 'ajax','module'=>'sesevent', 'action' => 'custom-url-check'), 'default', true) ?>',{value:custom_url_value,event_id:<?php echo $this->event->event_id ?>},function(response){
				response = jqueryObjectOfSes.parseJSON(response);
				if(response.error){
					jqueryObjectOfSes('#custom_url').css('border-color','red');
				}else{
						jqueryObjectOfSes('#custom_url').css('border-color','green');
				}
		});
});
//tags
  en4.core.runonce.add(function()
  {
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
//custom term and condition
function customTermAndCondition(){
	if(jqueryObjectOfSes("#is_custom_term_condition").is(':checked'))
    jqueryObjectOfSes("#custom_term_condition-wrapper").show();  // checked
	else
    jqueryObjectOfSes("#custom_term_condition-wrapper").hide();  // unchecked
}
jqueryObjectOfSes('#is_custom_term_condition').bind('change', function () {
	customTermAndCondition();
});
sesJqueryObject('#is_additional_costs').bind('change', function () {
	additionalCostsToggle();
});
customTermAndCondition();
additionalCostsToggle();

<?php if(Engine_Api::_()->sesevent()->isMultiCurrencyAvailable()){ ?>
	sesJqueryObject('#additional_costs_amount-element').append('<span class="fa fa-retweet sesevent_convert_icon sesbasic_link_btn" id="sesevent_currency_coverter" title="<?php echo $this->translate("Convert to %s",Engine_Api::_()->sesevent()->defaultCurrency());?>"></span>');
	sesJqueryObject('#additional_costs_amount-label').append('<span> (<?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?>)</span>');
<?php }else{ ?>
	sesJqueryObject('#additional_costs_amount-label').append('<span> (<?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?>)</span>');
<?php } ?>

jqueryObjectOfSes(document).ready(function(){
	jqueryObjectOfSes('#subcat_id-wrapper').css('display' , 'none');
	jqueryObjectOfSes('#subsubcat_id-wrapper').css('display' , 'none');
	//map
mapLoad_event = false;
if(jqueryObjectOfSes('#lat-wrapper').length > 0) {
  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
	jqueryObjectOfSes('#lat-wrapper').css('display' , 'none');
	jqueryObjectOfSes('#lng-wrapper').css('display' , 'none');
	<?php } else { ?>
    <?php if(!in_array('lat', $optionsenableglotion)) { ?>
      jqueryObjectOfSes('#lat-wrapper').css('display' , 'none');
    <?php } ?>
    <?php if(!in_array('lng', $optionsenableglotion)) { ?>
      jqueryObjectOfSes('#lng-wrapper').css('display' , 'none');
    <?php } ?>
	<?php } ?>
	initializeSesEventMapList();
}
});
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
	if(isLoad == 'custom'){
		var type = typed;
	}else{
		var categoryId = getProfileType($('category_id').value);
		var subcatId = getProfileType($('subcat_id').value);
		var subsubcatId = getProfileType($('subsubcat_id').value);
		var type = categoryId+','+subcatId+','+subsubcatId;
	}
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
  function showSubCategory(cat_id,selectedId,isLoad) {
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
        if ($('subcat_id') && responseHTML) {
          if ($('subcat_id-wrapper')) {
            $('subcat_id-wrapper').style.display = "block";
          }
          $('subcat_id').innerHTML = responseHTML;
        } else {
          if ($('subcat_id-wrapper')) {
            $('subcat_id-wrapper').style.display = "none";
            $('subcat_id').innerHTML = '<option value="0"></option>';
          }
        }
			  if ($('subsubcat_id-wrapper')) {
					$('subsubcat_id-wrapper').style.display = "none";
					$('subsubcat_id').innerHTML = '<option value="0"></option>';
				}
			if(isLoad != 'yes')
				showFields(cat_id,1);
      }
    }).send(); 
  }
	function showSubSubCategory(cat_id,selectedId,isLoad) {
		var categoryId = getProfileType($('category_id').value);
		if(cat_id == 0){
			if ($('subsubcat_id-wrapper')) {
				$('subsubcat_id-wrapper').style.display = "none";
				$('subsubcat_id').innerHTML = '';
				document.getElementsByName("0_0_1")[0].value=categoryId;				
      }
		if(isLoad != 'yes')
			showFields(cat_id,1,categoryId);
			return false;
		}
	if(isLoad != 'yes')
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
        if ($('subsubcat_id') && responseHTML) {
          if ($('subsubcat_id-wrapper')) {
            $('subsubcat_id-wrapper').style.display = "block";
						 $('subsubcat_id').innerHTML = responseHTML;
          }					
       }else{
					// get category id value 						
					if ($('subsubcat_id-wrapper')) {
						$('subsubcat_id-wrapper').style.display = "none";
						$('subsubcat_id').innerHTML = '<option value="0"></option>';
					} 
				}
			}
    })).send();  
  }
	function showCustom(value,isLoad){
		var categoryId = getProfileType($('category_id').value);
		var subcatId = getProfileType($('subcat_id').value);
		var id = categoryId+','+subcatId;
		if(isLoad != 'yes')
			showFields(value,1,id,isLoad);
		if(value == 0)
			document.getElementsByName("0_0_1")[0].value=subcatId;	
			return false;
	}
	
	function showCustomOnLoad(value,isLoad){
	 <?php if(isset($this->category_id) && $this->category_id != 0){ ?>
		var categoryId = getProfileType(<?php echo $this->category_id; ?>)+',';
		<?php }else{ ?>
		var categoryId = '0';
		<?php } ?>
		<?php if(isset($this->subcat_id) && $this->subcat_id != 0){ ?>
		var subcatId = getProfileType(<?php echo $this->subcat_id; ?>)+',';
		<?php  }else{ ?>
		var subcatId = '0';
		<?php } ?>
		<?php if(isset($this->subsubcat_id) && $this->subsubcat_id != 0){ ?>
		var subsubcat_id = getProfileType(<?php echo $this->subsubcat_id; ?>)+',';
		<?php  }else{ ?>
		var subsubcat_id = '0';
		<?php } ?>
		var id = (categoryId+subcatId+subsubcat_id).replace(/,+$/g,"");;
			showFields(value,1,id,'custom');
		if(value == 0)
			document.getElementsByName("0_0_1")[0].value=subcatId;	
			return false;
		
	}
  window.addEvent('domready', function() {
	jqueryObjectOfSes('#host-element').find('select').val(0);
	var sesdevelopment = 1;
	<?php if(isset($this->category_id) && $this->category_id != 0){ ?>
			<?php if(isset($this->subcat_id)){$catId = $this->subcat_id;}else $catId = ''; ?>
      showSubCategory('<?php echo $this->category_id; ?>','<?php echo $catId; ?>','yes');
   <?php  }else{ ?>
	  $('subcat_id-wrapper').style.display = "none";
	 <?php } ?>
	 <?php if(isset($this->subsubcat_id)){ ?>
    if (<?php echo isset($this->subcat_id) && intval($this->subcat_id)>0 ? $this->subcat_id : 'sesdevelopment' ?> == 0) {
     $('subsubcat_id-wrapper').style.display = "none";
    } else {
			<?php if(isset($this->subsubcat_id)){$subsubcat_id = $this->subsubcat_id;}else $subsubcat_id = ''; ?>
      showSubSubCategory('<?php echo $this->subcat_id; ?>','<?php echo $this->subsubcat_id; ?>','yes');
    }
	 <?php }else{ ?>
	 		 $('subsubcat_id-wrapper').style.display = "none";
	 <?php } ?>
	 		showCustomOnLoad('','no');
  });
//validate form
//Ajax error show before form submit
var error = false;
var objectError ;
var counter = 0;
function validateForm(){
		var errorPresent = false;
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
						)
						|| (checkRegion)
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
								if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
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
								
							}
							if(error)
								errorPresent = true;
							error = false;
						}
				}
			);
				
			return errorPresent ;
}
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
						var lastTwoDigit = sesJqueryObject('#end_time').val().slice('-2');
						var endDate = new Date(sesJqueryObject('#end_date').val()+' '+sesJqueryObject('#end_time').val().replace(lastTwoDigit,'')+':00 '+lastTwoDigit);
						var lastTwoDigitStart = sesJqueryObject('#start_time').val().slice('-2');
						var startDate = new Date(sesJqueryObject('#start_date').val()+' '+sesJqueryObject('#start_time').val().replace(lastTwoDigitStart,'')+':00 '+lastTwoDigitStart);
						var error = checkDateTime(startDate,endDate);
						if(error != ''){
							sesJqueryObject('#event_error_time-wrapper').show();
							sesJqueryObject('#event_error_time-element').text(error);
						 var errorFirstObject = sesJqueryObject('#event_start_time-wrapper').parent().parent();
						 sesJqueryObject('html, body').animate({
							scrollTop: errorFirstObject.offset().top
						 }, 2000);
							return false;
						}else{
							sesJqueryObject('#event_error_time-wrapper').hide();
						}
						sesJqueryObject('#submit').attr('disabled',true);
						sesJqueryObject('#submit').html('<?php echo $this->translate("Saving Form ...") ; ?>');
						return true;
					}			
	});
</script>
