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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<script type="text/javascript">
var searchparamshost = '';
<?php if($this->paginationType != 'pagging'){ ?>
var pagenumberhost = "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>";
  function loadMoreHosts() {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';    
    if(document.getElementById('underloading_image'))
     document.getElementById('underloading_image').style.display = '';
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/browse-hosts',
      'data': {
        format: 'html',
        page: pagenumberhost,
        viewmore: 1,
        params: '<?php echo json_encode($this->all_params); ?>',
				searchparams:searchparamshost,
        
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				if(document.getElementById('load_more'))
          document.getElementById('load_more').destroy();
        if(document.getElementById('underloading_image'))
         document.getElementById('underloading_image').destroy();
        if(document.getElementById('loadmore_list'))
         document.getElementById('loadmore_list').destroy();
				if(sesJqueryObject('#loading_image_hosts').length)
						sesJqueryObject('#loading_image_hosts').remove();
				sesJqueryObject('#loadingimgseseventhost-wrapper').hide();
        document.getElementById('results_data').innerHTML = document.getElementById('results_data').innerHTML + responseHTML;
				if(sesJqueryObject('.sesevent_search_result').length)
					sesJqueryObject('.sesevent_search_result').html(sesJqueryObject('#count_host_dara').html());
        
      }
    })).send();
    return false;
  }
<?php }else{ ?>
	function paggingNumbermydatalisthost(page) {
    if ($('load_more'))
      $('load_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";
    if(document.getElementById('load_more'))
      document.getElementById('load_more').style.display = 'none';
		sesJqueryObject('#sesbasic_loading_cont_overlay_mydatalisthost').css('display', 'block')
    if(document.getElementById('underloading_image'))
      document.getElementById('underloading_image').style.display = '';
    (new Request.HTML({
      method: 'post',              
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/browse-hosts',
      'data': {
        format: 'html',
        page: page,
        viewmore: 1,
        params: '<?php echo json_encode($this->all_params); ?>',  
				searchparams:searchparamshost,      
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById("results_data").innerHTML = responseHTML;
				sesJqueryObject('#loadingimgseseventhost-wrapper').hide();
				if(sesJqueryObject('.sesevent_search_result').length)
					sesJqueryObject('.sesevent_search_result').html(sesJqueryObject('#count_host_dara').html());
					sesJqueryObject('#sesbasic_loading_cont_overlay_mydatalisthost').css('display', 'none')
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
<?php } ?>
</script>
<?php if($this->viewmore){ ?>
<span id="count_host_dara" style="display:none"><?php echo $this->translate(array('%s host found.', '%s hosts found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())); ?></span>
<?php } ?>
<?php if(count($this->paginator) > 0): ?>
  <?php if (empty($this->viewmore)): ?>
  	 <?php if (($this->list_count)): ?>
     <div class="sesevent_search_result"><?php echo $this->translate(array('%s host found.', '%s hosts found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())); ?></div>
      <?php endif; ?>
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
	  <li class="sesevent_host_list sesevent_grid_btns_wrap sesbasic_clearfix <?php if($this->contentInsideOutside == 'in'): ?> sesevent_host_list_in <?php else: ?> sesevent_host_list_out <?php endif; ?> <?php if($this->mouseOver): ?> sesae-i-over <?php endif; ?>" style="width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">
	    <div class="sesevent_host_list_thumb" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;">
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
       	 <div class="sesevent_verified_label" title="<?php echo $this->translate("Verified Host"); ?>"><i class="fa fa-check"></i></div>
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
	        </div>
	      <?php } ?>
	    </div>
      <div class="sesevent_host_list_in_show_title sesevent_animation">
        <?php if(strlen($item->getTitle()) > $this->title_truncation_grid) {
          $title = mb_substr($item->getTitle(),0,($this->title_truncation_grid - 3)).'...';
          echo $this->htmlLink($href,$title ) ?>
        <?php } else { ?>
          <?php echo $this->htmlLink($href,$item->getTitle(),array('title'=>$item->getTitle()) ) ?>
        <?php } ?>
      </div>
	    <div class="sesevent_host_list_info sesbasic_clearfix">
	      <div class="sesevent_host_list_name">
	        <?php if(strlen($item->getTitle()) > $this->title_truncation_grid) {
		        $title = mb_substr($item->getTitle(),0,($this->title_truncation_grid - 3)).'...';
		        echo $this->htmlLink($href,$title ) ?>
	        <?php } else { ?>
		        <?php echo $this->htmlLink($href,$item->getTitle(),array('title'=>$item->getTitle()) ) ?>
	        <?php } ?>
	      </div>
	      <div class="sesevent_host_list_stats sesevent_list_stats">
	        <?php if(isset($this->hostEventCountActive) && isset($hostEventCount)) { ?>
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
	<?php if($this->paginationType == 'pagging'): ?>
    	<?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'mydatalisthost')); ?>
    <?php endif; ?>
  <?php if (!empty($this->paginator) && $this->paginator->count() > 1 && $this->paginationType != 'pagging'): ?>
    <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>    
      <div class="clear" id="loadmore_list"></div>      
      <div class="sesbasic_load_btn" id="load_more" onclick="loadMoreHosts();" > 
      	<?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading" id="underloading_image" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>
    <?php endif; ?>
  <?php endif; ?>
<?php if (empty($this->viewmore)): ?>
</ul>
<?php endif; ?>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('There are currently no hosts.') ?>
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
        if($('loadmore_list') && sesJqueryObject('#load_more').css('display') != 'none') {
          if (scrollTop > 40)
            loadMoreHosts();
        }
      }
      window.addEvent('scroll', function() { 
        ScrollLoader(); 
      });
    });    
  </script>
<?php endif; ?>