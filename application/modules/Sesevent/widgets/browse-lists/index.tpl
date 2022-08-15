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
<script type="text/javascript">
  function showPopUp(url) {
    Smoothbox.open(url);
    parent.Smoothbox.close;
  }
  function loadMoreContent() {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';
    if(document.getElementById('underloading_image'))
      document.getElementById('underloading_image').style.display = '';
    en4.core.request.send(new Request.HTML({
      method: 'post',              
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/browse-lists',
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        viewmore: 1,
        params: '<?php echo json_encode($this->all_params); ?>',        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById('results_data').innerHTML = document.getElementById('results_data').innerHTML + responseHTML;
        if(document.getElementById('load_more'))
          document.getElementById('load_more').destroy();
        if(document.getElementById('underloading_image'))
         document.getElementById('underloading_image').destroy();
        if(document.getElementById('loadmore_list'))
         document.getElementById('loadmore_list').destroy();
      }
    }));
    return false;
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
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/browse-lists',
      'data': {
        format: 'html',
        page: page,
        viewmore: 1,
        params: '<?php echo json_encode($this->all_params); ?>',        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
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
</script>
<?php if(count($this->paginator) > 0): ?>
  <?php if (empty($this->viewmore)): ?>
  <?php if($this->listCount): ?>
  <div class="sesevent_search_result"><?php echo $this->translate(array('%s list found.', '%s lists found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())); ?></div>
  <?php endif; ?>
    <ul class="sesevent_clist_listing sesbasic_bxs sesbasic_clearfix" id="results_data">
  <?php endif; ?>
  <?php foreach ($this->paginator as $item):  ?>
    <li class="sesevent_clist_item sesbasic_clearfix sesbm sesevent_grid_btns_wrap" style="width:<?php echo $this->width; ?>px;">
      <div class="sesevent_clist_item_header sesbasic_clearfix">
        <?php if(!empty($this->information) && in_array('postedby', $this->information)): ?>
          <div class="sesevent_clist_item_owner floatL">
            <?php echo $this->htmlLink($item->getOwner()->getHref(), $this->itemPhoto($item->getOwner(), 'thumb.icon', $item->getOwner()->getTitle()), array('title'=>$item->getOwner()->getTitle())) ?>
          </div>
        <?php endif; ?>
        <div class="sesevent_clist_item_header_info">
          <?php if(!empty($this->information) && in_array('title', $this->information)): ?>
            <div class="sesevent_clist_item_title">
              <?php 
              	if(strlen($item->getTitle()) > $this->titletruncation){
              		$title = mb_substr($item->getTitle(),0,($this->titletruncation - 3)).'...';
              	}else {
                	$title = $item->getTitle();
                }
            echo $this->htmlLink($item->getHref(), $title, array('title' => $item->getTitle())) ?>
            </div>
          <?php endif; ?>
          <?php if(!empty($this->information) && in_array('postedby', $this->information)): ?>
            <div class="sesevent_list_date sesbasic_text_light">
              <?php echo $this->translate('By %s', $this->htmlLink($item->getOwner(), $item->getOwner()->getTitle())) ?>
            </div>
          <?php endif; ?>
          <div class="sesevent_list_date sesevent_list_stats sesbasic_text_light">
          <?php if(!empty($this->information) && in_array('eventcount', $this->information)){ ?>
            <span title="<?php echo $this->translate(array('%s event', '%s events', $item->countEvents()), $this->locale()->toNumber($item->countEvents()))?>"><i class="far fa-calendar-alt"></i><?php echo $item->countEvents(); ?></span>
          <?php } ?>
            <?php if(!empty($this->information) && in_array('viewCount', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye"></i><?php echo $item->view_count; ?></span>
            <?php endif; ?>
            <?php if(!empty($this->information) && in_array('favouriteCount', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count))?>"><i class="fa fa-heart"></i><?php echo $item->favourite_count;?></span>
            <?php endif; ?>
            <?php if(!empty($this->information) && in_array('likeCount', $this->information)): ?>
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
  
  	<?php if($this->paginationType == 'pagging'): ?>
    	<?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'mydatalist')); ?>
    <?php endif; ?>
  
    <?php if (!empty($this->paginator) && $this->paginator->count() > 1 && $this->paginationType != 'pagging'): ?>
      <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
        <div class="clr" id="loadmore_list"></div>        
         <div class="sesbasic_load_btn" id="load_more" onclick="loadMoreContent();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn" id="underloading_image" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
        
      <?php endif; ?>
     <?php endif; ?>
<?php if (empty($this->viewmore)): ?>
</ul>
<?php endif; ?>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('There are currently no list created yet.') ?>
    </span>
  </div>
<?php endif; ?>
<?php if($this->paginationType == 1): ?>
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