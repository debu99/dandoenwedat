<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if( $this->form ): ?>
  <div class="sesbasic_browse_search">
    <?php echo $this->form->render($this) ?>
  </div>
<?php endif; ?>
<script type="application/javascript">
sesJqueryObject('#loadingimgseseventhost-wrapper').hide();
</script>
<?php $request = Zend_Controller_Front::getInstance()->getRequest();?>
<?php $controllerName = $request->getControllerName();?>
<?php $actionName = $request->getActionName();?>
<?php if($controllerName == 'index' && ($actionName == 'browse-host')){ ?>
  <?php $pageName = 'sesevent_index-browse-host'; ?>
    <script type="application/javascript">
      sesJqueryObject(document).ready(function(){
	sesJqueryObject('#filter_form').submit(function(e){		
	if(sesJqueryObject('#results_data').length > 0){
		e.preventDefault();
	  sesJqueryObject('#loadingimgseseventhost-wrapper').show();
	  if(typeof paggingNumbermydatalisthost == 'function'){
			sesJqueryObject('#sesbasic_loading_cont_overlay_mydatalisthost').css('display', 'block');
			sesJqueryObject('#results_data').html('<div class="sesbasic_load_btn sesbasic_view_more_loading" id="loading_image_hosts" style="display: block;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>');
	    e.preventDefault();
	    searchparamshost = sesJqueryObject(this).serialize();
	    paggingNumbermydatalisthost(1);
	  }else if(typeof loadMoreHosts == 'function'){
			sesJqueryObject('#results_data').html('<div class="sesbasic_load_btn sesbasic_view_more_loading" id="loading_image_hosts" style="display: block;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>');
	 	  sesJqueryObject('#load_more').show();
	    e.preventDefault();
	    searchparamshost = sesJqueryObject(this).serialize();
	    pagenumberhost = 1;
	    loadMoreHosts();
	  }
	}
	return true;
	});	
      });
    </script>
<?php } ?>