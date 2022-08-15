<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: review-parameters.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
if($this->item->category_id){
	$reviewParameters = Engine_Api::_()->getDbtable('parameters', 'seseventreview')->getParameterResult(array('category_id'=>$this->item->category_id));
  if(count($reviewParameters)){
  foreach($reviewParameters as $value){
?>
<div class="form-wrapper seseventreview_form_review_star">
	<div class="form-label"><label><?php echo $this->translate($value['title']); ?></label></div>
  <div id="sesevent_review_rating" class="sesbasic_rating_parameter seseventreview_rating_star_element" onmouseout="rating_out_review(<?php echo $value['parameter_id'] ?>);">
    <span id="rate_1_<?php echo $value['parameter_id'] ?>" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable" <?php  if (1):?> onclick="rate_review(1,<?php echo $value['parameter_id'] ?>);"<?php  endif; ?> onmouseover="rating_over_review(1,<?php echo $value['parameter_id'] ?>);"></span>
    <span id="rate_2_<?php echo $value['parameter_id'] ?>" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable" <?php if ( 1):?> onclick="rate_review(2,<?php echo $value['parameter_id'] ?>);"<?php endif; ?> onmouseover="rating_over_review(2,<?php echo $value['parameter_id'] ?>);"></span>
    <span id="rate_3_<?php echo $value['parameter_id'] ?>" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable" <?php if ( 1):?> onclick="rate_review(3,<?php echo $value['parameter_id'] ?>);"<?php endif; ?> onmouseover="rating_over_review(3,<?php echo $value['parameter_id'] ?>);"></span>
    <span id="rate_4_<?php echo $value['parameter_id'] ?>" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable" <?php if (1):?> onclick="rate_review(4,<?php echo $value['parameter_id'] ?>);"<?php endif; ?> onmouseover="rating_over_review(4,<?php echo $value['parameter_id'] ?>);"></span>
    <span id="rate_5_<?php echo $value['parameter_id'] ?>" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable" <?php if (1):?> onclick="rate_review(5,<?php echo $value['parameter_id'] ?>);"<?php endif; ?> onmouseover="rating_over_review(5,<?php echo $value['parameter_id'] ?>);"></span>
    <span id="rating_text_<?php echo $value['parameter_id'] ?>" class="sesbasic_rating_text"><?php echo $this->translate('click to rate');?></span>
  </div>
</div>
<input type="hidden" name="review_parameter_<?php echo $value['parameter_id'] ?>" id="review_parameter_<?php echo $value['parameter_id'] ?>" />
<?php } 
	}
 }
?>
<script type="text/javascript">
function ratingTextReview(rating){
	var text = '';
	if(rating == 1){
			text = "<?php echo $this->translate('terrible');?>";
	}else if(rating == 2){
			text = "<?php echo $this->translate('poor');?>";
	}else if(rating == 3){
			text = "<?php echo $this->translate('average');?>";
	}else if(rating == 4){
			text = "<?php echo $this->translate('very good');?>";
	}else if(rating == 5){
			text = "<?php echo $this->translate('excellent');?>";
	}else {
		text = "<?php echo $this->translate('click to rate');?>";
	}
	return text;
}
  var rating_over_review = window.rating_over_review = function(rating,id) {
    $('rating_text_'+id).innerHTML = ratingTextReview(rating);
    for(var x=1; x<=5; x++) {
      if(x <= rating) {
		$('rate_'+x+'_'+id).set('class', 'sesbasic-rating-parameter-unit');
				} else {
		$('rate_'+x+'_'+id).set('class', 'sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable');
      }
    }
  }
  
  var rating_out_review = window.rating_out_review = function(id) {
    var star_value = document.getElementById('review_parameter_'+id).value;
		$('rating_text_'+id).innerHTML = ratingTextReview(star_value);
    if(star_value != '') {
      set_rating_review(star_value,id);
    }
    else {
      for(var x=1; x<=5; x++) {	
	$('rate_'+x+'_'+id).set('class', 'sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable');
      }
    }
  }
    
  var rate_review = window.rate_review = function(rating,id) {
    document.getElementById('review_parameter_'+id).value = rating;
		$('rating_text_'+id).innerHTML = ratingTextReview(rating);
    set_rating_review(rating,id);
  }
    
  var set_rating_review = window.set_rating_review = function(rating,id) {
    for(var x=1; x<=parseInt(rating); x++) {
      $('rate_'+x+'_'+id).set('class', 'sesbasic-rating-parameter-unit');
    }
    for(var x=parseInt(rating)+1; x<=5; x++) {
      $('rate_'+x+'_'+id).set('class', 'sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable');
    }
		$('rating_text_'+id).innerHTML = ratingTextReview(rating);
  }
  
  window.addEvent('domready', function() {
		var countExistsParam = sesJqueryObject('.sesvent_review_values');
		for(var i=0;i<countExistsParam.length;i++){
			var valueEx = sesJqueryObject(countExistsParam[i]).val();	
			var id = sesJqueryObject(countExistsParam[i]).attr('id');	
			id = id.replace('review_parameter_value_','');
			sesJqueryObject('#review_parameter_'+id).val(valueEx);
			set_rating_review(valueEx,id);
		}
  });
</script>