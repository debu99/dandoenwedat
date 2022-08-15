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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<style>
.hideOptn{display:none !important;}
</style>
<?php if(isset($this->identityForWidget) && !empty($this->identityForWidget)):?>
  <?php $randonNumber = $this->identityForWidget;?>
<?php else:?>
  <?php $randonNumber = $this->identity;?> 
<?php endif;?>
<?php if(!$this->is_ajax){ ?>
   <!--Default Tabs-->
<?php if($this->tab_option == 'default'){ ?>
<div class="layout_core_container_tabs">
<div class="tabs_alt tabs_parent" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
<!--Advance Tabs-->
<?php if($this->tab_option == 'advance'){ ?>
<div class="sesbasic_tabs_container sesbasic_clearfix sesbasic_bxs">
  <div class="sesbasic_tabs sesbasic_clearfix" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
 <?php } ?>
<!--Filter Tabs-->
<?php if($this->tab_option == 'filter'){ ?>
<div class="sesbasic_filter_tabs_container sesbasic_clearfix sesbasic_bxs">
  <div class="sesbasic_filter_tabs sesbasic_clearfix" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
<!--Vertical Tabs-->
<?php if($this->tab_option == 'vertical'){ ?>
<div class="sesbasic_v_tabs_container sesbasic_clearfix sesbasic_bxs">
  <div class="sesbasic_v_tabs sesbasic_clearfix" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
  <ul>
 <?php 
 $defaultOptionArray = array();
 foreach($this->defaultOptions as $key=>$valueOptions){ 
 $defaultOptionArray[] = $key;
 ?>
 <li <?php if($this->defaultOpenTab == $key){ ?> class="active"<?php } ?> id="sesTabContainer_<?php echo $randonNumber; ?>_<?php echo $key; ?>">
   <a href="javascript:;" onclick="changeTabSes_<?php echo $randonNumber; ?>('<?php echo $key; ?>')"><?php echo $this->translate(($valueOptions)); ?></a>
 </li>
 <?php } ?>
      </ul>
   <?php echo $this->content()->renderWidget('sesevent.browse-menu-quick',array()); ?>
  <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){ ?>
  <div>
     <ul class="sesbasic_dashboard_links sesbasic_bxs">
      <li><a href="<?php echo $this->url(array(), 'sesevent_my_ticket', true); ?>" class="sesbasic_link_btn"><i class="fa fa-ticket"></i><?php echo $this->translate('My Tickets'); ?></a></li>
     </ul></div>
   <?php } ?>
     </div>
  <div class="sesbasic_tabs_content sesbasic_clearfix">
<?php } ?>
<?php if($this->manageorder == 'lists'){ ?>
<script type="text/javascript">
  function showPopUp(url) {
    Smoothbox.open(url);
    parent.Smoothbox.close;
  }
	
	function paggingNumbermydatalist(page) {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';
    if(document.getElementById('underloading_image'))
      document.getElementById('underloading_image').style.display = '';
    (new Request.HTML({
      method: 'post',              
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/manage-events',
      'data': {
        format: 'html',
        page: page,
        is_ajax: 1,
				identity : '<?php echo $randonNumber; ?>',
				height:'<?php echo $this->height;?>',
				type:activeType_<?php echo $randonNumber ?>,
        params: '<?php echo json_encode($this->params); ?>',        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject("#view_more_<?php echo $randonNumber; ?>").hide();
				sesJqueryObject("#loading_image_<?php echo $randonNumber; ?>").hide();
        document.getElementById("results_data").innerHTML = responseHTML;
        if(document.getElementById('load_more'))
          document.getElementById('load_more').destroy();
        if(document.getElementById('underloading_image'))
         document.getElementById('underloading_image').destroy();
        if(document.getElementById('loadmore_list'))
         document.getElementById('loadmore_list').destroy();
      }
    })).send();
    return false;
  }
	
  function loadMoreContent() {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';
    if(document.getElementById('underloading_image'))
      document.getElementById('underloading_image').style.display = '';
    (new Request.HTML({
      method: 'post',              
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/manage-events',
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        is_ajax: 1,
				identity : '<?php echo $randonNumber; ?>',
				height:'<?php echo $this->height;?>',
				type:activeType_<?php echo $randonNumber ?>,
        params: '<?php echo json_encode($this->params); ?>',        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject("#view_more_<?php echo $randonNumber; ?>").hide();
				sesJqueryObject("#loading_image_<?php echo $randonNumber; ?>").hide();
        document.getElementById("results_data").innerHTML = document.getElementById("results_data").innerHTML + responseHTML;
        if(document.getElementById('load_more'))
          document.getElementById('load_more').destroy();
        if(document.getElementById('underloading_image'))
         document.getElementById('underloading_image').destroy();
        if(document.getElementById('loadmore_list'))
         document.getElementById('loadmore_list').destroy();
      }
    })).send();
    return false;
  }
</script>
<?php if(count($this->paginator) > 0): ?>
  <?php if (empty($this->is_ajax) || $this->first_content): ?>
    <ul class="sesevent_clist_listing sesbasic_bxs sesbasic_clearfix" id="results_data">
  <?php endif; ?>
  <?php foreach ($this->paginator as $item):  ?>
    <li class="sesevent_clist_item sesbasic_clearfix sesbm sesevent_grid_btns_wrap" style="width:<?php echo str_replace('px','',$this->width_lists); ?>px;">
      <div class="sesevent_clist_item_header sesbasic_clearfix">
        <?php if(!empty($this->information) && in_array('by', $this->information)): ?>
          <div class="sesevent_clist_item_owner floatL">
            <?php echo $this->htmlLink($item->getOwner()->getHref(), $this->itemPhoto($item->getOwner(), 'thumb.profile', $item->getOwner()->getTitle()), array('title'=>$item->getOwner()->getTitle())) ?>
          </div>
        <?php endif; ?>
        <div class="sesevent_clist_item_header_info">
          <?php if(!empty($this->information) && in_array('title', $this->information)): ?>
            <div class="sesevent_clist_item_title">
              <?php 
              	if(strlen($item->getTitle()) > $this->grid_title_truncation){
              		$title = mb_substr($item->getTitle(),0,($this->grid_title_truncation - 3)).'...';
              	}else {
                	$title = $item->getTitle();
                }
           		  echo $this->htmlLink($item->getHref(), $title, array('title' => $item->getTitle())) ?>
            </div>
          <?php endif; ?>
          <?php if(!empty($this->information) && in_array('by', $this->information)): ?>
            <div class="sesevent_list_date sesbasic_text_light">
              <?php echo $this->translate('By %s', $this->htmlLink($item->getOwner(), $item->getOwner()->getTitle())) ?>
            </div>
          <?php endif; ?>
          <div class="sesevent_list_date sesevent_list_stats sesbasic_text_light">
          <?php if(!empty($this->information) && in_array('eventcount', $this->information)){ ?>
            <span title="<?php echo $this->translate(array('%s event', '%s events', $item->countEvents()), $this->locale()->toNumber($item->countEvents()))?>"><i class="far fa-calendar-alt"></i><?php echo $item->countEvents(); ?></span>
          <?php } ?>
            <?php if(!empty($this->information) && in_array('view', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye"></i><?php echo $item->view_count; ?></span>
            <?php endif; ?>
            <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && !empty($this->information) && in_array('favourite', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count))?>"><i class="fa fa-heart"></i><?php echo $item->favourite_count;?></span>
            <?php endif; ?>
            <?php if(!empty($this->information) && in_array('like', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up"></i><?php echo $item->like_count; ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="sesevent_clist_item_thumb floatL" style="height:<?php echo $this->height; ?>px;">
        <a href="<?php echo $item->getHref(); ?>" class="sesevent_clist_item_thumb_img floatL">
          <span style="background-image:url(<?php echo $item->getPhotoUrl(); ?>);"></span>
        </a>
     <?php if(!empty($this->information) && in_array('featuredLabel', $this->information) || in_array('sponsoredLabel', $this->information)){ ?>
      <p class="sesevent_labels">
      <?php if(in_array('featuredLabel', $this->information) && $item->is_featured ){ ?>
        <span class="sesevent_label_featured"><?php echo $this->translate('FEATURED'); ?></span>
      <?php } ?>
      <?php if(in_array('sponsoredLabel', $this->information) && $item->is_sponsored ){ ?>
        <span class="sesevent_label_sponsored"><?php echo $this->translate("SPONSORED"); ?></span>
      <?php } ?>
      </p>
     <?php } ?>
     	<?php $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); ?>
     	<div class="sesevent_grid_btns"> 
      	<?php if(!empty($this->information) && in_array('socialSharing', $this->information)){ ?>
      	
          <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $item, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

				<?php } ?>  
        <?php if($this->viewer_id): ?>
          <?php if($this->viewer_id && !empty($this->information) && in_array('share', $this->information)): ?>
          	<a  class="sesbasic_icon_btn" title='<?php echo $this->translate("Share") ?>' href="javascript:void(0);" onclick="showPopUp('<?php echo $this->escape($this->url(array('module'=>'activity', 'controller'=>'index', 'action'=>'share', 'route'=>'default', 'type'=>'sesevent_list', 'id' => $item->list_id, 'format' => 'smoothbox'), 'default' , true)); ?>'); return false;" >
          		<i class="fa fa-share"></i>
          	</a>
        	<?php endif; ?>
        <?php endif; ?>
        <?php 
        if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
            $itemtype = 'sesevent_list';
            $getId = 'list_id';
            $canComment =  true;
            if(!empty($this->information) && in_array('likeButton', $this->information) && $canComment){
          ?>
          <!--Like Button-->
          <?php $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($item->$getId,$item->getType()); ?>
            <a href="javascript:;" data-url="<?php echo $item->$getId ; ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_<?php echo $itemtype; ?> <?php echo ($LikeStatus) ? 'button_active' : '' ; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $item->like_count; ?></span></a>
            <?php } ?>
            <?php if(!empty($this->information) && in_array('favouriteButton', $this->information) && isset($item->favourite_count)){ ?>
            <?php $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>$itemtype,'resource_id'=>$item->$getId)); ?>
            <a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn sesevent_favourite_<?php echo $itemtype; ?> <?php echo ($favStatus)  ? 'button_active' : '' ?>"  data-url="<?php echo $item->$getId ; ?>"><i class="fa fa-heart"></i><span><?php echo $item->favourite_count; ?></span></a>
          <?php } ?>
        <?php  } ?>
         <?php 
         	$editList = false;
         $viewer = Engine_Api::_()->user()->getViewer(); ?>
          <?php if($this->viewer_id): ?>
           <?php if($viewer->getIdentity() == $item->owner_id || $viewer->level_id == 1 ): ?>
           <?php 	$editList = true; ?>
           <?php endif; ?>
         <?php endif; ?>
        <?php if($editList): ?>
        <a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a>
        <div class="sesbasic_option_box">
          <a href="<?php echo $this->url(array('action'=>'edit', 'list_id'=>$item->getIdentity(),'slug'=>$item->getSlug()),'sesevent_list_view',true) ?>" title="<?php echo $this->translate("Edit List") ?>"><i class="fa fa-edit"></i><?php echo $this->translate("Edit List") ?></a>
          <a onclick="openSmoothBoxInUrl(this.href);return false;" href="<?php echo $this->url(array('action'=>'delete', 'list_id'=>$item->getIdentity(),'slug'=>$item->getSlug(),  'format' => 'smoothbox'),'sesevent_list_view',true) ?>" title="<?php echo $this->translate("Delete List") ?>"><i class="fa fa-trash"></i><?php echo $this->translate("Delete List") ?></a>
        </div>
        <?php endif; ?>
      </div>
      <?php if(!empty($this->information) && in_array('showEventsList', $this->information)): ?>
      <?php $list = $item;
      			$events = $item->getEvents(array('limit'=>3),false);
      ?>
      <?php if(count($events) > 0): ?>
      <div class="sesevent_clist_item_events">
        <ul>
          <?php foreach( $events as $eventItems ): ?>
          <?php $event = Engine_Api::_()->getItem('sesevent_event', $eventItems->file_id); ?>
          <?php if( !empty($event) ): ?>
            <li class="floatL">
              <div>
                <a class="sesevent_clist_item_event_img ses_tooltip"  title="<?php echo $event->title; ?>" data-src="<?php echo $event->getGuid(); ?>" href="<?php echo $event->getHref(); ?>">
                  <span style="background-image:url(<?php echo $event->getPhotoUrl() ?>);"></span>
                </a>
               <?php if(count($events) > 3){ ?>
               	<?php $moreNumber = count($events) - 3; ?>
                <a href="<?php echo $event->getHref(); ?>" class="sesevent_clist_item_event_more centerT ses_tooltip" data-src="<?php echo $event->getGuid(); ?>">
                  <b>+ <?php $moreNumber; ?></b>
                  <span><?php echo $this->translate(array('%s Event', '%s Events', $moreNumber), $this->locale()->toNumber($moreNumber)); ?></span>
                </a>
               <?php } ?>
              </div>
            </li>
          <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
    </li>
  <?php endforeach; ?>
  	<?php if($this->loadOptionData == 'pagging'){ ?>
      <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'mydatalist')); ?>
    <?php } ?>
    <?php if (!empty($this->paginator) && $this->paginator->count() > 1 && $this->loadOptionData != 'pagging'): ?>
      <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
        <div class="clr" id="loadmore_list"></div>
         <div class="sesbasic_load_btn" id="load_more" onclick="loadMoreContent();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn" id="underloading_image" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
      <?php endif; ?>
     <?php endif; ?>
<?php if (empty($this->is_ajax)  || $this->first_content): ?>
</ul>
<?php endif; ?>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('There are currently no list created yet.') ?>
    </span>
  </div>
<?php endif; ?>
<?php if($this->loadOptionData == 'auto_load'): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      var paginatorCount = '<?php echo $this->paginator->count(); ?>';
      var paginatorCurrentPageNumber = '<?php echo $this->paginator->getCurrentPageNumber(); ?>';
      function ScrollLoader() { 
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        if($('loadmore_list')) {
          if (scrollTop > 40)
            loadMoreContent();
        }
      }
      window.addEvent('scroll', function() { 
        ScrollLoader(); 
      });
    });    
  </script>
<?php endif; ?>
<?php }else if($this->manageorder == 'hosts'){ ?> 
	<script type="text/javascript">
	function paggingNumbermydatalist(page) {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';
    if(document.getElementById('underloading_image'))
      document.getElementById('underloading_image').style.display = '';
    (new Request.HTML({
      method: 'post',              
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/manage-events',
      'data': {
        format: 'html',
        page: page,
        is_ajax: 1,
				identity : '<?php echo $randonNumber; ?>',
				height:'<?php echo $this->height;?>',
				type:activeType_<?php echo $randonNumber ?>,
        params: '<?php echo json_encode($this->params); ?>',        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject("#view_more_<?php echo $randonNumber; ?>").hide();
				sesJqueryObject("#loading_image_<?php echo $randonNumber; ?>").hide();
				document.getElementById("results_data").innerHTML = responseHTML;
				if(document.getElementById('load_more'))
					document.getElementById('load_more').destroy();
				if(document.getElementById('underloading_image'))
				 document.getElementById('underloading_image').destroy();
				if(document.getElementById('loadmore_list'))
				 document.getElementById('loadmore_list').destroy();
      }
    })).send();
    return false;
  }
  function loadMore() {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';    
    if(document.getElementById('underloading_image'))
     document.getElementById('underloading_image').style.display = '';
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/manage-events',
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        is_ajax: 1,
				identity : '<?php echo $randonNumber; ?>',
				height:'<?php echo $this->height;?>',
				type:activeType_<?php echo $randonNumber ?>,
        params: '<?php echo json_encode($this->params); ?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject("#view_more_<?php echo $randonNumber; ?>").hide();
				sesJqueryObject("#loading_image_<?php echo $randonNumber; ?>").hide();
        document.getElementById('results_data').innerHTML = document.getElementById('results_data').innerHTML + responseHTML;
        if(document.getElementById('load_more'))
          document.getElementById('load_more').destroy();
        if(document.getElementById('underloading_image'))
         document.getElementById('underloading_image').destroy();
        if(document.getElementById('loadmore_list'))
         document.getElementById('loadmore_list').destroy();
      }
    })).send();
    return false;
  }
</script>
<?php if(count($this->paginator) > 0): ?>
  <?php if (empty($this->is_ajax) || $this->first_content): ?>
     <ul class="sesbasic_bxs sesbasic_clearfix sesevent_host_list_container" id="results_data">
  <?php endif; ?>
  <?php foreach ($this->paginator as $item): ?>
    <?php
      $followCount = Engine_Api::_()->getDbtable('follows', 'sesevent')->getFollowCount(array('host_id' => $item->host_id, 'type' => $item->type));
      $hostEventCount = Engine_Api::_()->getDbtable('events', 'sesevent')->getHostEventCounts(array('host_id' => $item->host_id, 'type' => $item->type));
	    $sitehostredirect = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1); 
			if($sitehostredirect && $item->user_id) {
			  $user = Engine_Api::_()->getItem('user', $item->user_id);
			  $href = $user->getHref();
			} else {
			  $href = $item->getHref();
			}
		?>
	  <li class="sesevent_host_list sesevent_grid_btns_wrap sesbasic_clearfix <?php if($this->contentInsideOutside == 'in'): ?> sesevent_host_list_in <?php else: ?> sesevent_host_list_out <?php endif; ?> <?php if($this->mouseOver): ?> sesae-i-over <?php endif; ?>" style="width:<?php echo is_numeric($this->width_hosts) ? $this->width_hosts.'px' : $this->width_hosts ?>;">
	    <div class="sesevent_host_list_thumb" style="height:<?php echo is_numeric($this->height_hosts) ? $this->height_hosts.'px' : $this->height_hosts ?>;">
	      <?php
	      $href = $href;
	      $imageURL = $item->getPhotoUrl('thumb.main');
	      ?>
	      <a href="<?php echo $href; ?>" class="sesevent_host_list_thumb_img">
	        <span style="background-image:url(<?php echo $imageURL; ?>);"></span>
	      </a>
       <a href="<?php echo $href; ?>" class="sesevent_host_list_overlay"></a>
	      <?php if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive)){ ?>
	      <p class="sesevent_labels">
	        <?php if(isset($this->featuredLabelActive) && $item->featured){ ?>
	        <span class="sesevent_label_featured"><?php echo $this->translate("FEATURED"); ?></span>
	        <?php } ?>
	        <?php if(isset($this->sponsoredLabelActive) && $item->sponsored){ ?>
	        <span class="sesevent_label_sponsored"><?php echo $this->translate("SPONSORED"); ?></span>
	        <?php } ?>
	      </p>
        <?php if(isset($this->verifiedLabelActive) && $item->verified){ ?>
       	 <div class="sesevent_verified_label" title="<?php echo $this->translate("VERIFIED"); ?>"><i class="fa fa-check"></i></div>
        <?php } ?>
	      <?php } ?>
	      <?php if(isset($this->socialSharingActive) || isset($this->favouriteButtonActive)) {
	      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $href); ?>
	        <div class="sesevent_grid_btns"> 
	          <?php if(isset($this->socialSharingActive)){ ?>
	          
	          <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $item, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

	          <?php } 
	          $itemtype = 'sesevent_host';
	          $getId = 'host_id';
	          ?>
	          <?php
							if(isset($this->favouriteButtonActive) && isset($item->favourite_count)) {
								$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_host','resource_id'=>$item->host_id));
								$favClass = ($favStatus)  ? 'button_active' : '';
								$shareOptions = "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_host_". $item->host_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_host ".$favClass ."' data-url=\"$item->host_id\"><i class='fa fa-heart'></i><span>$item->favourite_count</span></a>";
								echo $shareOptions;
							}
	          ?>
            <?php 
               $isEditHost = false;
               $isDeleteHost = false;
               $viewer = Engine_Api::_()->user()->getViewer(); ?>
             <?php if($viewer->getIdentity() == $item->owner_id || $viewer->level_id == 1 ): ?>
               <?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1) || $item->type == 'offsite'): ?>
             	 <?php  $isEditHost = true; ?>
               <?php endif; ?>
               <?php if($item->type == 'offsite'): ?>
               <?php   $isDeleteHost = true; ?>
               <?php endif; ?>
             <?php endif; ?> 
             
           <?php if($isDeleteHost || $isEditHost): ?>           
            <a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_more"><i class="fa fa-ellipsis-v"></i></a>
            <div class="sesbasic_option_box">
            <?php if($isEditHost): ?>
              <a href="<?php echo $this->url(array('action'=>'edit', 'host_id'=>$item->getIdentity()),'sesevent_host',true) ?>" title="<?php echo $this->translate("Edit Host") ?>"><i class="fa fa-edit"></i><?php echo $this->translate("Edit Host") ?></a>
            <?php endif; ?>
           	<?php if($isDeleteHost): ?>
              <a href="<?php echo $this->url(array('action'=>'delete', 'host_id'=>$item->getIdentity(),'format' => 'smoothbox'),'sesevent_host',true) ?>" title="<?php echo $this->translate("Delete Host") ?>" onclick="openSmoothBoxInUrl(this.href);return false;"><i class="fa fa-trash"></i><?php echo $this->translate("Delete Host") ?></a>
            <?php endif; ?>
            </div>
            <?php endif; ?>
            
	        </div>
	      <?php } ?>
	    </div>
      <div class="sesevent_host_list_in_show_title sesevent_animation">
        <?php if(strlen($item->getTitle()) > $this->grid_title_truncation) {
          $title = mb_substr($item->getTitle(),0,($this->grid_title_truncation - 3)).'...';
          echo $this->htmlLink($href,$title ) ?>
        <?php } else { ?>
          <?php echo $this->htmlLink($href,$item->getTitle() ) ?>
        <?php } ?>
      </div>
	    <div class="sesevent_host_list_info sesbasic_clearfix">
	      <div class="sesevent_host_list_name">
	        <?php if(strlen($item->getTitle()) > $this->grid_title_truncation) {
		        $title = mb_substr($item->getTitle(),0,($this->grid_title_truncation - 3)).'...';
		        echo $this->htmlLink($href,$title ) ?>
	        <?php } else { ?>
		        <?php echo $this->htmlLink($href,$item->getTitle() ) ?>
	        <?php } ?>
	      </div>
	      <div class="sesevent_host_list_stats sesevent_list_stats">
	        <?php if(isset($this->showEventsListActive) && isset($hostEventCount)) { ?>
		        <span title="<?php echo $this->translate(array('%s event host', '%s event hosted', $hostEventCount), $this->locale()->toNumber($hostEventCount))?>"><i class="far fa-calendar-alt sesbasic_text_light"></i><?php echo $hostEventCount; ?></span>
	        <?php } ?>
	        <?php if(isset($this->followActive) && isset($followCount)) { ?>
		        <span title="<?php echo $this->translate(array('%s follow', '%s followed', $followCount), $this->locale()->toNumber($followCount))?>"><i class="fas fa-users sesbasic_text_light"></i><?php echo $followCount; ?></span>
	        <?php } ?>
	        <?php if(isset($this->viewActive) && isset($item->view_count)) { ?>
		        <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye sesbasic_text_light"></i><?php echo $item->view_count; ?></span>
	        <?php } ?>
	        <?php if(isset($this->favouriteActive) && isset($item->favourite_count)) { ?>
		        <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count))?>"><i class="fa fa-heart sesbasic_text_light"></i><?php echo $item->favourite_count; ?></span>
	        <?php } ?>
	      </div>
	    </div>
	  </li>
  <?php endforeach; ?>
	<?php if($this->loadOptionData == 'pagging'){ ?>
      <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'mydatalist')); ?>
    <?php } ?>
  <?php if (!empty($this->paginator) && $this->paginator->count() > 1 && $this->loadOptionData != 'pagging'): ?>
    <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
      <div class="clear" id="loadmore_list"></div>      
       <div class="sesbasic_load_btn" id="load_more" onclick="loadMore();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn" id="underloading_image" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
    <?php endif; ?>
  <?php endif; ?>
<?php if (empty($this->is_ajax)  || $this->first_content): ?>
</ul>
<?php endif; ?>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('There are currently no hosts.') ?>
    </span>
  </div>
<?php endif; ?>
<?php if($this->loadOptionData == 'auto_load'): ?>
  <script type="text/javascript">    
    en4.core.runonce.add(function() {
      var paginatorCount = '<?php echo $this->paginator->count(); ?>';
      var paginatorCurrentPageNumber = '<?php echo $this->paginator->getCurrentPageNumber(); ?>';
      function ScrollLoader() { 
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        if($('loadmore_list')) {
          if (scrollTop > 40)
            loadMore();
        }
      }
      window.addEvent('scroll', function() { 
        ScrollLoader(); 
      });
    });    
  </script>
<?php endif; ?>	
<?php }else{ 
	$managepage = true;
?>
<?php include APPLICATION_PATH . '/application/modules/Sesevent/views/scripts/_eventBrowseWidget.tpl'; ?>
<?php } ?>
  <?php if(!$this->is_ajax):?>
  </div>
  </div>
  </div>
<script type="application/javascript">
	sesJqueryObject(document).on('click','.changeoptn',function(e){
		if(sesJqueryObject(this).hasClass('active'))
			return;
			document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = '<div class="sesbasic_view_more_loading" id="loading_image_<?php echo $randonNumber; ?>"> <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sesbasic/externals/images/loading.gif" /> </div>';
			sesJqueryObject('#sesevent_manage_event_optn').find('.active').removeClass('active');
			sesJqueryObject(this).addClass('active');
			searchCriteriaSesevent = sesJqueryObject(this).attr('data-url');
      sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
      sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
			var searchCriteriaSesevent = '';
			if(sesJqueryObject('#sesevent_manage_event_optn').length){
				searchCriteriaSesevent = sesJqueryObject('#sesevent_manage_event_optn').find('.active').attr('data-url');
			}else{
				searchCriteriaSesevent = '';	
			}
			requestViewMorea_<?php echo $randonNumber; ?> = new Request.HTML({
					method: 'post',
					'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
					'data': {
						format: 'html',
						page: 1,    
						params : params<?php echo $randonNumber; ?>, 
						is_ajax : 1,
						searchCtr : searchCriteriaSesevent,
						identity : '<?php echo $randonNumber; ?>',
						height:'<?php echo $this->masonry_height;?>',
						type:activeType_<?php echo $randonNumber ?>,
						identityObject:'<?php echo isset($this->identityObject) ? $this->identityObject : "" ?>'
					},
							onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
								sesJqueryObject('#map-data_<?php echo $randonNumber;?>').removeClass('checked');
								sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').html(responseHTML);
								if(sesJqueryObject('#error-message_<?php echo $randonNumber;?>').length > 0) {
									var optionEnable = sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel');
									var optionEnableList = sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?> > a');
									for(i=0;i<optionEnableList.length;i++)
										sesJqueryObject('#sesevent_'+optionEnable+'_view_<?php echo $randonNumber; ?>').hide();
									sesJqueryObject('#tabbed-widget_<?php echo $randonNumber;?>').append('<div id="error-message_<?php echo $randonNumber;?>">'+sesJqueryObject('#error-message_<?php echo $randonNumber;?>').html()+'</div>')
								}
								if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
										document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').html() ;	
								oldMapData_<?php echo $randonNumber; ?> = [];								
								if(document.getElementById('map-data_<?php echo $randonNumber;?>') && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel') == 'map') {
								if(document.getElementById('sesevent_map_view_<?php echo $randonNumber;?>'))	
									document.getElementById('sesevent_map_view_<?php echo $randonNumber;?>').style.display = 'block';
									var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
								if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
										newMapData_<?php echo $randonNumber ?> = mapData;
										sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
										mapFunction_<?php echo $randonNumber?>();
								}else{
									if(typeof  map_<?php echo $randonNumber;?> == 'undefined')	{
														sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
														initialize_<?php echo $randonNumber?>();	
											}	
									}
								}else if(document.getElementById('map-data_<?php echo $randonNumber;?>')){
									var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
									sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, mapData);
									sesJqueryObject('#map-data_<?php echo $randonNumber;?>').addClass('read');
								}
								if(sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>'))
										document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>').style.display = 'block';
									pinboardLayout_<?php echo $randonNumber ?>('force','true');
								}
								else if(sesJqueryObject('.masonry_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
								}
								if(document.getElementById('temporary-data-<?php echo $randonNumber?>'))
									document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML = '';
								sesJqueryObject('.sesbasic_view_more_loading_<?php echo $randonNumber;?>').hide();
								sesJqueryObject('#loadingimgsesevent-wrapper').hide();
							}
				});
      requestViewMorea_<?php echo $randonNumber; ?>.send();
      return false;
	});
   var availableTabs_<?php echo $randonNumber; ?>;
  var requestTab_<?php echo $randonNumber; ?>;
  <?php if(isset($defaultOptionArray)){ ?>
    availableTabs_<?php echo $randonNumber; ?> = <?php echo json_encode($defaultOptionArray); ?>;
  <?php  } ?>
  var defaultOpenTab ;
  function changeTabSes_<?php echo $randonNumber; ?>(valueTab){
		if(valueTab == 'lists' || valueTab == 'hosts')
			sesJqueryObject('.sesbasic_view_type ').addClass('hideOptn');
		else
			sesJqueryObject('.sesbasic_view_type ').removeClass('hideOptn');
    if(sesJqueryObject("#sesTabContainer_<?php echo $randonNumber ?>_"+valueTab).hasClass('active'))
    return;
    var id = '_<?php echo $randonNumber; ?>';
    var length = availableTabs_<?php echo $randonNumber; ?>.length;
    for (var i = 0; i < length; i++){
      if(availableTabs_<?php echo $randonNumber; ?>[i] == valueTab){
					document.getElementById('sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).addClass('active');
					sesJqueryObject('#sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).addClass('sesbasic_tab_selected');
      }else{
				sesJqueryObject('#sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).removeClass('sesbasic_tab_selected');
				document.getElementById('sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).removeClass('active');
      }
    }
    if(valueTab){
      if(document.getElementById("error-message_<?php echo $randonNumber;?>")) {
        document.getElementById("error-message_<?php echo $randonNumber;?>").style.display = 'none';
      }
			if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
					document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML ='';
      sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
     // sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
		 document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = '<div class="sesbasic_view_more_loading" id="loading_image_<?php echo $randonNumber; ?>"> <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sesbasic/externals/images/loading.gif" /> </div>';
      if(typeof(requestTab_<?php echo $randonNumber; ?>) != 'undefined') {
				requestTab_<?php echo $randonNumber; ?>.cancel();
      }
      if(typeof(requestViewMore_<?php echo $randonNumber; ?>) != 'undefined') {
				requestViewMore_<?php echo $randonNumber; ?>.cancel();
      }
			var searchCriteriaSesevent = '';
			if(sesJqueryObject('#sesevent_manage_event_optn').length){
				searchCriteriaSesevent = sesJqueryObject('#sesevent_manage_event_optn').find('.active').attr('data-url');
			}else{
				searchCriteriaSesevent = '';	
			}
      defaultOpenTab = valueTab;
      requestTab_<?php echo $randonNumber; ?> = new Request.HTML({
				method: 'post',
				'url': en4.core.baseUrl+"widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>/openTab/"+valueTab,
				'data': {
					format: 'html',  
					params : params<?php echo $randonNumber; ?>, 
					is_ajax : 1,
					searchCtr : searchCriteriaSesevent,
					first_content : 1,
					searchParams:searchParams<?php echo $randonNumber; ?> ,
					identity : '<?php echo $randonNumber; ?>',
					height:'<?php echo $this->height;?>',
					type:activeType_<?php echo $randonNumber ?>
				},
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
								sesJqueryObject('#map-data_<?php echo $randonNumber;?>').removeClass('checked');
								sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').hide(); 
								sesJqueryObject('#error-message_<?php echo $randonNumber;?>').remove();
								sesJqueryObject('.sesbasic_view_more').hide();
								sesJqueryObject('.sesbasic_view_more_loading').hide();
								sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').html(responseHTML);
								var check = true;
								if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
										document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML ;
									oldMapData_<?php echo $randonNumber; ?> = [];
								if(document.getElementById('map-data_<?php echo $randonNumber;?>') && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber;?>').find('.active').attr('rel') == 'map'){
									var mapData = sesJqueryObject.parseJSON(document.getElementById('temporary-data-<?php echo $randonNumber?>').getElementById('map-data_<?php echo $randonNumber;?>').innerHTML);
									if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
										oldMapData_<?php echo $randonNumber; ?> = [];
										newMapData_<?php echo $randonNumber ?> = mapData;
										loadMap_<?php echo $randonNumber ?> = true;
										sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
										initialize_<?php echo $randonNumber?>();	
										mapFunction_<?php echo $randonNumber?>();
									}else{
										sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
										initialize_<?php echo $randonNumber?>();	
									}
							 }
								if(sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>'))
										document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>').style.display = 'block';
									pinboardLayout_<?php echo $randonNumber ?>('force','true');
								}
								else if(sesJqueryObject('.masonry_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
								}
								if(document.getElementById('temporary-data-<?php echo $randonNumber?>'))
									document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML = '';
								sesJqueryObject('.sesbasic_view_more_loading_<?php echo $randonNumber;?>').hide();
								if(typeof viewMoreHide_<?php echo $randonNumber; ?> == 'function' && (valueTab != 'lists' && valueTab != 'hosts'))
									viewMoreHide_<?php echo $randonNumber; ?>();
								else{
									sesJqueryObject("#view_more_<?php echo $randonNumber; ?>").hide();
									sesJqueryObject("#loading_image_<?php echo $randonNumber; ?>").hide();	
								}
									
							}
      });
      requestTab_<?php echo $randonNumber; ?>.send();
      return false;			
    }
  }
</script> 
  <?php endif;?>
