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
<ul class="sesbasic_sidebar_block sesevent_clist_listing sesbasic_bxs sesbasic_clearfix">
  <?php $items = $this->paginator;
  foreach($items as $item){
    $item = Engine_Api::_()->getItem('sesevent_list', $item->list_id);;
  }
   ?>
  <li class="sesevent_clist_item sesbasic_clearfix sesbm sesevent_grid_btns_wrap" style="width:<?php echo $this->width ?>px;">
      <div class="sesevent_clist_item_header sesbasic_clearfix">
        <?php if(!empty($this->information) && in_array('postedby', $this->information)): ?>
          <div class="sesevent_clist_item_owner floatL">
            <?php echo $this->htmlLink($item->getOwner()->getHref(), $this->itemPhoto($item->getOwner(), 'thumb.icon', $item->getOwner()->getTitle()), array('title'=>$item->getOwner()->getTitle())) ?>
          </div>
        <?php endif; ?>
        <div class="sesevent_clist_item_header_info">
        <?php if(!empty($this->information) && in_array('title', $this->information)){ ?>
          <div class="sesevent_clist_item_title">
            <?php $title = mb_substr($item->getTitle(),0,($this->titletruncation-3)).'...';
            echo $this->htmlLink($item->getHref(), $title, array('title' => $item->getTitle())) ?>
          </div>
        <?php } ?>
          <?php if(!empty($this->information) && in_array('postedby', $this->information)): ?>
             <div class="sesevent_list_date sesbasic_text_light">
               <?php echo $this->translate('by');?> <?php echo $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle()) ?>     
             </div>
           <?php endif; ?>
          <div class="sesevent_list_date sesevent_list_stats sesbasic_text_light">
            <?php if (!empty($this->information) && in_array('eventcount', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s event', '%s events', $item->countEvents()), $this->locale()->toNumber($item->countEvents()))?>"><i class="far fa-calendar-alt"></i><?php echo $item->countEvents(); ?></span>
            <?php endif; ?>
            <?php if (!empty($this->information) && in_array('favouriteCount', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s favorite', '%s favorites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count)); ?>"><i class="fa fa-heart"></i><?php echo $item->favourite_count; ?></span>
            <?php endif; ?>
            <?php if (!empty($this->information) && in_array('viewCount', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)); ?>"><i class="fa fa-eye"></i><?php echo $item->view_count; ?></span>
            <?php endif; ?>
            <?php if (!empty($this->information) && in_array('likeCount', $this->information)): ?>
              <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up"></i><?php echo $item->like_count; ?></span>
              <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="sesevent_clist_item_thumb floatL" style="height:<?php echo $this->height ?>px;">
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
        <?php $events = $item->getEvents(array('limit'=>3),false); ?>
          <?php if($events && !empty($this->information) && in_array('showEventsList', $this->information)):  ?>
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
        </div>
      </li>
</ul>
