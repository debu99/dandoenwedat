<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $base_url = $this->layout()->staticBaseUrl;
$this->headScript()
->appendFile($base_url . 'externals/autocompleter/Observer.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.Local.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.Request.js');
?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php if(!$this->widgetIdentity) { ?>
	<div class="sesevent_browse_reviews_search sesbasic_bxs sesbasic_clearfix <?php echo $this->view_type == 'horizontal' ? 'sesevent_browse_review_search_horizontal' : 'sesevent_browse_review_search_vertical'; ?>">
<?php } ?>
<?php echo $this->form->render($this) ?>
<?php if(!$this->widgetIdentity) { ?>
	</div>
<?php } ?>
<script type="application/javascript">
  sesJqueryObject('#loadingimgseseventreview-wrapper').hide();
</script>
<?php $request = Zend_Controller_Front::getInstance()->getRequest();?>
<?php $controllerName = $request->getControllerName();?>
<?php $actionName = $request->getActionName();?>
<?php if($controllerName == 'index' && $actionName == 'browse'){ ?>
  <?php $identity = Engine_Api::_()->sesbasic()->getIdentityWidget('sesevent.browse-reviews','widget','seseventreview_index_browse'); ?>
	<?php if($identity):?>
		<script type="application/javascript">
			sesJqueryObject(document).ready(function(){
				sesJqueryObject('#filter_form_review').submit(function(e){	
					if(sesJqueryObject('.sesevent_review_listing').length > 0){
						e.preventDefault();
						sesJqueryObject('#loadingimgseseventreview-wrapper').show();
						is_search_<?php echo $identity; ?> = 1;
						if(typeof paggingNumber<?php echo $identity; ?> == 'function'){
							sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $identity?>').css('display', 'block');
							isSearch = true;
							e.preventDefault();
							searchParams<?php echo $identity; ?> = sesJqueryObject(this).serialize();
							sesJqueryObject('#loadingimgseseventreview-wrapper').show();
							paggingNumber<?php echo $identity; ?>(1);
						}else if(typeof viewMore_<?php echo $identity; ?> == 'function'){
							sesJqueryObject('#sesevent_review_listing').html('');
							sesJqueryObject('#loading_image_<?php echo $identity; ?>').show();
							isSearch = true;
							e.preventDefault();
							searchParams<?php echo $identity; ?> = sesJqueryObject(this).serialize();
							page<?php echo $identity; ?> = 1;
							sesJqueryObject('#loadingimgseseventreview-wrapper').show();
							viewMore_<?php echo $identity; ?>();
						}
					}
					return true;
				});	
			});
		</script>
	<?php endif;?>
<?php } ?>
<script type="text/javascript">
	sesJqueryObject('#loadingimgseseventreview-wrapper').hide();
</script>