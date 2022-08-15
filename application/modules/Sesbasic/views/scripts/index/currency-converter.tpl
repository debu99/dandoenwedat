<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: currency-converter.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
<div class="sesbasic_currency_converter_popup">
	<?php echo $this->form->render() ?>
</div>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>

<script type="application/javascript">
sesJqueryObject ('#converter_price-wrapper').hide();

sesJqueryObject (document).on('submit','#sesbasic_currency_converter',function(e){

		e.preventDefault();
		
		if(sesJqueryObject('#main_price').val() == ''){
				sesJqueryObject('#main_price').css('border','1px solid red');
				return false;
		}else{
				sesJqueryObject('#main_price').css('border','');
		}
		sesJqueryObject('#sesbasic_loading_cont_overlay_con').show();
		new Request.JSON({
      method: 'post',
      url : sesJqueryObject(this).attr('action'),
      data : {
        format : 'json',
				curr:sesJqueryObject('#currency').val(),
				val:sesJqueryObject('#main_price').val(),
				is_ajax:true,
      },
      onComplete: function(response) {
				sesJqueryObject('#sesbasic_loading_cont_overlay_con').hide();
				sesJqueryObject('#converter_price-wrapper').show();
				sesJqueryObject('#converter_price').val(response);
			}
    }).send();
});
</script>