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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php if(isset($this->identityForWidget) && !empty($this->identityForWidget)):?>
  <?php $randonNumber = $this->identityForWidget;?>
<?php else:?>
  <?php $randonNumber = $this->identity;?> 
<?php endif;?>
<?php if(!$this->is_ajax){ ?>
<div class="sesevent_view_sponsorship sesbasic_clearfix sesbasic_bxs">
	<div class="sesevent_view_sponsorship_photo sesbm">
  	<span class="sesevent_view_sponsorship_photo_thumb" style="background-image:url(<?php echo $this->subject->getPhotoUrl(); ?>);"></span>
  </div>
  <div class="sesevent_view_sponsorship_details">  
    <div class="sesevent_view_sponsorship_title"><?php echo $this->subject->title; ?></div>
    <div class="sesevent_view_sponsorship_des"><?php echo $this->subject->description; ?></div>
    <?php if ($this->can_buy):?>
     	<div class="sesevent_view_sponsorship_buy_btn">
        <a class="sesbasic_link_btn" href="<?php echo $this->url(array('action' => 'details','event_id'=>$this->event->custom_url,'id'=>$this->subject->getIdentity()), "sesevent_sponsorship"); ?>"><?php echo $this->translate('Buy Now'); ?></a>
      </div>
  	<?php endif; ?>
  </div>
</div>
<ul id="sesevent_sponsorship_view_<?php echo $randonNumber;?>" class="sesevent_sponsored_list_wrapper clear sesbasic_clearfix sesbasic_bxs">
<?php } ?>
<?php if($this->paginator->getTotalItemCount() >0){ ?>
<?php foreach($this->paginator as $item){ ?>
	<li class="sesevent_sponsored_list sesbasic_clearfix sesbm">
  	<?php  
      if($item->website){
        if (!preg_match("~^(?:f|ht)tps?://~i", $item->website))
           $url = "http://" . $item->website;
         else
            $url = $item->website;
      }else
          $url = '';
    ?>   
    <?php if(is_array($this->details) && in_array('logo',$this->details)){ ?>
    	<div class="sesevent_sponsored_list_photo">   
        <a href="<?php echo $url ? $url  : 'javascript:;' ?>" target="_blank">
          <img src="<?php echo $item->getLogoUrl(); ?>" alt="<?php echo $item->title; ?>" />
        </a>
     	</div>
   	<?php } ?>
    <div class="sesevent_sponsored_list_info">
    	<?php if(is_array($this->details) && in_array('title',$this->details)){ ?>
        <div class="sesevent_sponsored_list_name">
         <a href="<?php echo $url ? $url  : 'javascript:;' ?>" target="_blank">
          	<?php echo $item->title; ?>
        	</a>
      	</div>
    	<?php } ?>
    	<?php if(is_array($this->details) && in_array('description',$this->details)){ ?>
      	<div class="sesevent_sponsored_list_des">
      		<?php echo $item->description; ?>
      	</div>
    	<?php } ?>
     </div>
	</li>
<?php } ?>
<?php }else{ ?>
		<div class="tip">
    <span>
      <?php echo $this->translate('No sponsors found for this sponsorship.');?>
      <?php if ($this->can_buy):?>
        <?php echo $this->translate('Be the first to %1$sbuy%2$s one!', '<a href="'.$this->url(array('action' => 'details','event_id'=>$this->event->custom_url,'id'=>$this->subject->getIdentity()), "sesevent_sponsorship").'">', '</a>'); ?>
      <?php endif; ?>
    </span>
  </div>
  <br/>
<?php } ?>
<?php if($this->loadOptionData == 'pagging'){ ?>
      <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>$randonNumber)); ?>
    <?php } ?>
<?php if(!$this->is_ajax){ ?>
</ul>
<?php } ?>
<?php if($this->loadOptionData != 'pagging' && !$this->is_ajax):?>
   <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
  
<?php endif;?>

<script type="application/javascript">

  <?php if($this->loadOptionData == 'auto_load' && !$this->is_ajax){ ?>
    window.addEvent('load', function() {
      sesJqueryObject(window).scroll( function() {
				var containerId = '#sesevent_sponsorship_view_<?php echo $randonNumber;?>';
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
  <?php } ?>
	var identity<?php echo $randonNumber; ?>  = '<?php echo $randonNumber; ?>';
		var params<?php echo $randonNumber; ?> = '<?php echo json_encode($this->params); ?>';
		var page<?php echo $randonNumber; ?> = '<?php echo $this->page + 1; ?>';
  <?php if($this->loadOptionData != 'pagging') { ?>
    viewMoreHide_<?php echo $randonNumber; ?>();	
    function viewMoreHide_<?php echo $randonNumber; ?>() {
      if ($('view_more_<?php echo $randonNumber; ?>'))
	$('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
    }
		 
    function viewMore_<?php echo $randonNumber; ?> () {
      document.getElementById('view_more_<?php echo $randonNumber; ?>').style.display = 'none';
      document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = '';    
      requestViewMore_<?php echo $randonNumber; ?> = new Request.HTML({
	method: 'post',
	'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/event-sponsorship",
	'data': {
	  format: 'html',
	  page: page<?php echo $randonNumber; ?>,    
	  params : params<?php echo $randonNumber; ?>, 
	  is_ajax : 1,
	  identity : '<?php echo $randonNumber; ?>',
	},
		onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
			
        document.getElementById('sesevent_sponsorship_view_<?php echo $randonNumber;?>').innerHTML = document.getElementById('sesevent_sponsorship_view_<?php echo $randonNumber;?>').innerHTML + responseHTML;
				document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = 'none';
				
      }
			
		
      });
      requestViewMore_<?php echo $randonNumber; ?>.send();
      return false;
    }
    <?php }else{ ?>
    function paggingNumber<?php echo $randonNumber; ?>(pageNum){
      sesJqueryObject('.sesbasic_loading_cont_overlay').css('display','block');
      requestViewMore_<?php echo $randonNumber; ?> = (new Request.HTML({
	method: 'post',
	'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/event-sponsorship",
	'data': {
	  format: 'html',
	  page: pageNum,    
	  params :params<?php echo $randonNumber; ?> , 
	  is_ajax : 1,
	  identity : <?php echo $randonNumber; ?>,
	},
	onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
		sesJqueryObject('.sesbasic_loading_cont_overlay').css('display','none');
						document.getElementById('sesevent_sponsorship_view_<?php echo $randonNumber;?>').innerHTML =  responseHTML;
	}
      }));
      requestViewMore_<?php echo $randonNumber; ?>.send();
      return false;
    }
  <?php } ?>
</script>