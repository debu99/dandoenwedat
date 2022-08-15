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
<?php if((!$this->is_ajax) && ($this->paginator->count() > 1 || $this->canUpload )): ?> 
  <?php if( $this->canUpload ): ?>
    <div class="sesbasic_profile_tabs_top sesbasic_clearfix">
      <?php echo $this->htmlLink(array(
        'route' => 'sesevent_extended',
        'controller' => 'album',
        'action' => 'create',
        'event_id' => $this->event_id,
        ), $this->translate('Add New Album'), array(
        'class' => 'sesbasic_button sesbasic_icon_add'
      )) ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_album.css'); ?> 
<?php if(isset($this->identityForWidget) && !empty($this->identityForWidget)):?>
  <?php $randonNumber = $this->identityForWidget;?>
<?php else:?>
  <?php $randonNumber = $this->identity;?>
<?php endif;?>

 <div class="sesevent_search_result sesbasic_clearfix sesbm" id="<?php echo !$this->is_ajax ? 'paginator_count_sesevent' : 'paginator_count_ajax_sesevent' ?>"><span id="total_item_count_sesevent" style="display:inline-block;"> 
 <?php echo $this->translate(array('%s album found', '%s albums found', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount()))?>
 </div>
 
  <?php if(!$this->is_ajax): ?>
  <script type="application/javascript">
  var tabId_pPhoto = <?php echo $this->identity; ?>;
	window.addEvent('domready', function() {
		tabContainerHrefSesbasic(tabId_pPhoto);	
	});
  </script>
    <div id="scrollHeightDivSes_<?php echo $randonNumber; ?>">
      <ul class="sesevent_album_listings sesevent_browse_album_listings sesbasic_bxs sesbasic_clearfix" id="tabbed-widget_<?php echo $randonNumber; ?>">
  <?php endif;?>			 
  <?php foreach( $this->paginator as $album ): ?>
    <?php if($this->view_type == 1){ ?>
      <li id="thumbs-photo-<?php echo $album->photo_id ?>" class="sesevent_album_list_grid_thumb sesevent_album_list_grid sesea-i-<?php echo (isset($this->insideOutside) && $this->insideOutside == 'outside') ? 'outside' : 'inside'; ?> sesea-i-<?php echo (isset($this->fixHover) && $this->fixHover == 'fix') ? 'fix' : 'over'; ?> sesbm" style="width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">  
	<a class="sesevent_album_list_grid_img" href="<?php echo Engine_Api::_()->sesevent()->getHref($album->getIdentity(),$album->album_id); ?>" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;">
	  <span class="main_image_container" style="background-image: url(<?php echo $album->getPhotoUrl('thumb.normalmain'); ?>);"></span>
	  <div class="ses_image_container" style="display:none;">
	    <?php $image = Engine_Api::_()->sesevent()->getAlbumPhoto($album->getIdentity(),$album->photo_id);
	    foreach($image as $key=>$valuePhoto){?>
	      <div class="child_image_container"><?php echo $valuePhoto->getPhotoUrl('thumb.normalmain');  ?></div>
	    <?php  }  ?>  
	    <div class="child_image_container"><?php echo $album->getPhotoUrl('thumb.normalmain'); ?></div>          
	  </div>
	</a>
	<?php  if(isset($this->socialSharing) || isset($this->likeButton)){  ?>
	  <span class="sesevent_album_list_grid_btns">
	    <?php if(isset($this->socialSharing)){ 
	      //album viewpage link for sharing
	      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $album->getHref());
	      ?>
	      
	      <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $album, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

	    <?php }
	    $canComment = $this->event->authorization()->isAllowed($this->viewer, 'comment');;
	    if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 && isset($this->likeButton) && $canComment){  ?>
	      <!--Album Like Button-->
	      <?php $albumLikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($album->album_id,'sesevent_album'); ?>
	      <a href="javascript:;" data-url='<?php echo $album->album_id; ?>' class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_albumlike <?php echo ($albumLikeStatus) ? 'button_active' : '' ; ?>">
		<i class="fa fa-thumbs-up"></i>
		<span><?php echo $album->like_count; ?></span>
	      </a>
	    <?php }  ?>
	  </span>
	<?php } ?>
	<?php if(isset($this->like) || isset($this->comment) || isset($this->view) || isset($this->title) || isset($this->photoCount) ||  isset($this->by)){ ?>
	  <p class="sesevent_album_list_grid_info sesbasic_clearfix<?php if(!isset($this->photoCount)) { ?> nophotoscount<?php } ?>">
	    <?php if(isset($this->title)) { ?>
	      <span class="sesevent_album_list_grid_title">
		<?php echo $this->htmlLink($album, $this->string()->truncate($album->getTitle(), $this->title_truncation),array('title'=>$album->getTitle())) ; ?>
	      </span>
	    <?php } ?>
	    <span class="sesevent_album_list_grid_stats">
	      <?php if(isset($this->by)) { ?>
		<span class="sesevent_album_list_grid_owner">
		  <?php echo $this->translate('By');
      	$albumOwner  = Engine_Api::_()->getItem('user',$album->owner_id);
      ?>
		  <?php echo $this->htmlLink($albumOwner->getHref(), $albumOwner->getTitle(), array('class' => 'thumbs_author')) ?>
		</span>
	      <?php }?>
	    </span>
	    <span class="sesevent_album_list_grid_stats sesbasic_text_light">
	      <?php if(isset($this->like) && isset($album->like_count)) { ?>
		<span class="sesevent_album_list_grid_likes" title="<?php echo $this->translate(array('%s like', '%s likes', $album->like_count), $this->locale()->toNumber($album->like_count))?>">
		  <i class="fa fa-thumbs-up"></i>
		  <?php echo $album->like_count;?>
	      </span>
	      <?php } ?>
	      <?php if(isset($this->comment)) { ?>
		<span class="sesevent_album_list_grid_comment" title="<?php echo $this->translate(array('%s comment', '%s comments', $album->comment_count), $this->locale()->toNumber($album->comment_count))?>">
		  <i class="fa fa-comment"></i>
		  <?php echo $album->comment_count;?>
		</span>
	      <?php } ?>
	      <?php if(isset($this->view)) { ?>
		<span class="sesevent_album_list_grid_views" title="<?php echo $this->translate(array('%s view', '%s views', $album->view_count), $this->locale()->toNumber($album->view_count))?>">
		  <i class="fa fa-eye"></i>
		  <?php echo $album->view_count;?>
		</span>
	      <?php } ?>
	      <?php if(isset($this->photoCount)) { ?>
		<span class="sesevent_album_list_grid_count" title="<?php echo $this->translate(array('%s photo', '%s photos', $album->count()), $this->locale()->toNumber($album->count()))?>" >
		  <i class="fa fa-image"></i> 
		  <?php echo $album->count();?>                
		</span>
	      <?php } ?>
	    </span>
	  </p>
	<?php } ?>
	<?php if(isset($this->photoCount)) { ?>
	  <p class="sesevent_album_list_grid_count">
	    <?php echo $this->translate(array('%s <span>photo</span>', '%s <span>photos</span>', $album->count()),$this->locale()->toNumber($album->count())); ?>
	  </p>
	<?php } ?>
      </li>
    <?php }?>
  <?php endforeach;?>
  <?php if($this->load_content == 'pagging'){ ?>
    <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>$randonNumber)); ?>
  <?php } ?>
  <?php if(!$this->is_ajax){ ?>
     </ul>
    </div>  
    <?php if($this->load_content != 'pagging'){ ?>      
       <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
      
    <?php } ?>
  <?php } ?>
  
<?php if($this->load_content == 'auto_load'){ ?>
  <script type="text/javascript">
  window.addEvent('load', function() {
    sesJqueryObject(window).scroll( function() {
			var containerId = '#scrollHeightDivSes_<?php echo $randonNumber;?>';
			if(typeof sesJqueryObject(containerId).offset() != 'undefined') {
				var hT = sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').offset().top,
				hH = sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').outerHeight(),
				wH = sesJqueryObject(window).height(),
				wS = sesJqueryObject(this).scrollTop();
				if ((wS + 30) > (hT + hH - wH) && sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').css('display') == 'block') {
					document.getElementById('feed_viewmore_link_<?php echo $randonNumber; ?>').click();
				}
			}	
    });
  });
  </script>
<?php } ?>
<script type="text/javascript">
  var params<?php echo $randonNumber; ?> = '<?php echo json_encode($this->params); ?>';
  var identity<?php echo $randonNumber; ?>  = '<?php echo $randonNumber; ?>';
  var searchParams<?php echo $randonNumber; ?>;
  function paggingNumber<?php echo $randonNumber; ?>(pageNum){
    sesJqueryObject ('.overlay_<?php echo $randonNumber ?>').css('display','block');
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/profile-photos",
      'data': {
      format: 'html',
      page: pageNum,    
      params :params<?php echo $randonNumber; ?>, 
      is_ajax : 1,
      searchParams : searchParams<?php echo $randonNumber; ?>,
      identity : identity<?php echo $randonNumber; ?>,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
      if($('loadingimgsesevent-wrapper'))
	sesJqueryObject('#loadingimgsesevent-wrapper').hide();
	sesJqueryObject ('.overlay_<?php echo $randonNumber ?>').css('display','none');
	document.getElementById('tabbed-widget_<?php echo $randonNumber; ?>').innerHTML =  responseHTML;	sesJqueryObject('#paginator_count_sesevent').find('#total_item_count_sesevent').html(sesJqueryObject('#paginator_count_ajax_sesevent').find('#total_item_count_sesevent').html());
	sesJqueryObject('#paginator_count_ajax_sesevent').remove();
      }
    })).send();
    return false;
  }
  var page<?php echo $randonNumber; ?> = '<?php echo $this->page + 1; ?>';
  viewMoreHide_<?php echo $randonNumber; ?>();
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    if ($('view_more_<?php echo $randonNumber; ?>'))
      $('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
  }
  function viewMore_<?php echo $randonNumber; ?> (){
    document.getElementById('view_more_<?php echo $randonNumber; ?>').style.display = 'none';
    document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = '';    
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/profile-photos/index/',
      'data': {
	format: 'html',
	page: page<?php echo $randonNumber; ?>,    
	params :params<?php echo $randonNumber; ?>, 
	is_ajax : 1,
	searchParams : searchParams<?php echo $randonNumber; ?>,
	identity : identity<?php echo $randonNumber; ?>,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
	if($('loadingimgsesevent-wrapper'))
	sesJqueryObject('#loadingimgsesevent-wrapper').hide();
	document.getElementById('tabbed-widget_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('tabbed-widget_<?php echo $randonNumber; ?>').innerHTML + responseHTML;
	sesJqueryObject('#paginator_count_sesevent').find('#total_item_count_sesevent').html(sesJqueryObject('#paginator_count_ajax_sesevent').find('#total_item_count_sesevent'));
	sesJqueryObject('#paginator_count_ajax_sesevent').remove();
	//document.getElementById('view_more_<?php echo $randonNumber; ?>').style.display = 'block';
	document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = 'none';
      }
    })).send();
    return false;
  };
</script>