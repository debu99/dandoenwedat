<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: review-rating.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="form-wrapper seseventreview_form_rating_star">
  <div class="form-label"><label><?php echo $this->translate("Overall Rating"); ?></label></div>
  <div id="sesevent_review_rating" class="sesbasic_rating_star seseventreview_rating_star_element" onmouseout="rating_out();">
    <span id="rate_1" class="fa fa-star star-disable" onclick="rate(1);" onmouseover="rating_over(1);"></span>
    <span id="rate_2" class="fa fa-star star-disable" onclick="rate(2);" onmouseover="rating_over(2);"></span>
    <span id="rate_3" class="fa fa-star star-disable" onclick="rate(3);" onmouseover="rating_over(3);"></span>
    <span id="rate_4" class="fa fa-star star-disable" onclick="rate(4);" onmouseover="rating_over(4);"></span>
    <span id="rate_5" class="fa fa-star star-disable" onclick="rate(5);" onmouseover="rating_over(5);"></span>
    <span id="rating_text" class="sesbasic_rating_text"><?php echo $this->translate('click to rate');?></span>
  </div>
</div>

<script type="text/javascript">
 function ratingText(rating){
    var text = '';
    if(rating == 1)
    text = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_rating_stars_one',$this->translate('terrible')); ?>";
    else if(rating == 2)
    text = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_rating_stars_two',$this->translate('poor')); ?>";
    else if(rating == 3)
    text = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_rating_stars_three',$this->translate('average')); ?>";
    else if(rating == 4)
    text = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_rating_stars_four',$this->translate('very good')); ?>";
    else if(rating == 5)
    text = "<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_rating_stars_five',$this->translate('excellent')); ?>";
    else 
    text = "<?php echo $this->translate('click to rate');?>";
    return text;
  }
  var rating_over = window.rating_over = function(rating) {
    $('rating_text').innerHTML = ratingText(rating);
    for(var x=1; x<=5; x++) {
      if(x <= rating) {
		$('rate_'+x).set('class', 'fa fa-star');
				} else {
		$('rate_'+x).set('class', 'fa fa-star star-disable');
      }
    }
  }
  
  var rating_out = window.rating_out = function() {
    var star_value = document.getElementById('rate_value').value;
		$('rating_text').innerHTML = ratingText(star_value);
    if(star_value != '') {
      set_rating(star_value);
    }
    else {
      for(var x=1; x<=5; x++) {	
	$('rate_'+x).set('class', 'fa fa-star star-disable');
      }
    }
  }
    
  var rate = window.rate = function(rating) {
    document.getElementById('rate_value').value = rating;
		$('rating_text').innerHTML = ratingText(rating);
    set_rating(rating);
  }
    
  var set_rating = window.set_rating = function(rating) {
    for(var x=1; x<=parseInt(rating); x++) {
      $('rate_'+x).set('class', 'fa fa-star');
    }
    for(var x=parseInt(rating)+1; x<=5; x++) {
      $('rate_'+x).set('class', 'fa fa-star star-disable');
    }
		$('rating_text').innerHTML = ratingText(rating);
  }
  
  window.addEvent('domready', function() {
		var ratingCount = $('rate_value').value;
		if(ratingCount > 0)
			var val = ratingCount;
		else
			var val = 0;
    set_rating(ratingCount);
  });

//Ajax error show before form submit
var error = false;
var objectError ;
var counter = 0;
function validateForm(){
		var errorPresent = false;
		counter = 0;
		sesJqueryObject('#sesevent_review_form input, #sesevent_review_form select,#sesevent_review_form checkbox,#sesevent_review_form textarea,#sesevent_review_form radio').each(
				function(index){
						var input = sesJqueryObject(this);
						if(sesJqueryObject(this).closest('div').parent().not('fieldset').css('display') != 'none' && sesJqueryObject(this).closest('div').parent().not('fieldset').find('.form-label').find('label').first().hasClass('required') && sesJqueryObject(this).prop('type') != 'hidden' && sesJqueryObject(this).closest('div').parent().not('fieldset').attr('class') != 'form-elements'){	
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
			sesJqueryObject('#sesevent_review_form').submit(function(e){
					var validationFm = validateForm();
					if(!sesJqueryObject('#rate_value').val()){
						alert('<?php echo $this->translate("Please fill the red mark fields"); ?>');
						 var errorFirstObject = sesJqueryObject('#sesevent_review_rating').parent();
						 sesJqueryObject('html, body').animate({
							scrollTop: errorFirstObject.offset().top
						 }, 2000);
						 return false;
					}else	if(validationFm)
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
						sesJqueryObject('#submit').attr('disabled',true);
						sesJqueryObject('#submit').html('<?php echo $this->translate("Submitting Form ...") ; ?>');
						return true;
					}			
	});
});

</script>