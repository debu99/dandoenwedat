<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _sidebarWidgetData.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); 
?>
<?php 
  $defaultParams['starttime'] = true;
  $defaultParams['endtime'] = true;
  $defaultParams['timezone'] = true;
  $defaultParams['isBreak'] = true;
?>
<?php foreach( $this->results as $item ): ?>
<?php if(isset($item['joinedmember']))
					$joinedmemberOld = $item['joinedmember'];
 ?>
<?php if(isset($this->getitem)){ 
	$item = Engine_Api::_()->getItem('sesevent_event', $item['event_id']);
 } ?>
<?php $host = Engine_Api::_()->getItem('sesevent_host', $item->host); ?>
<?php if($this->view_type == 'list'){ ?>
  <li class="sesbasic_sidebar_list sesbasic_clearfix">
    <?php echo $this->htmlLink($item, $this->itemPhoto($item, 'thumb.icon')); ?>
    <div class="sesbasic_sidebar_list_info">
      <?php  if(isset($this->titleActive)){ ?>
        <div class="sesbasic_sidebar_list_title">
          <?php if(strlen($item->getTitle()) > $this->title_truncation_list){
          $title = mb_substr($item->getTitle(),0,($this->title_truncation_list-3)).'...';
          echo $this->htmlLink($item->getHref(),$title, array('class' => 'ses_tooltip', 'data-src' => $item->getGuid()));
          } else { ?>
          <?php echo $this->htmlLink($item->getHref(),$item->getTitle(), array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
          <?php } ?>
        </div>
      <?php } ?>
      <?php if(isset($this->byActive)){ ?>
        <div class="sesevent_list_stats">
          <?php $owner = $item->getOwner(); ?>
            <span>
            <i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate('By'); ?>"></i>
            <?php echo $this->htmlLink($owner->getHref(),$owner->getTitle()) ?></span>
        </div>
      <?php } ?>
      <?php if(isset($this->hostActive)){ ?>
	      <div class="sesevent_list_stats">
	        <span><i class="fa fa-male sesbasic_text_light" title="<?php echo $this->translate('Hosted By'); ?>"></i><?php echo $this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author')) ?></span>
        </div>
      <?php } ?>
      <?php  if(isset($this->locationActive) && $item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)){ ?>
      <div class="sesevent_list_stats sesevent_list_location">
        <span class="widthfull">
          <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->translate('Location');?>"></i>
          <span title="<?php echo $item->location; ?>">
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
            <a href="<?php echo $this->url(array('resource_id' => $item->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $item->location ?></a>
          <?php } else { ?>
            <?php echo $item->location;?>
          <?php } ?>
          </span>
        </span>
       </div>
      <?php } ?>
      <?php  if(isset($this->startenddateActive)){ ?>
      <div class='sesevent_list_stats sesevent_list_time'>
        <span class='widthfull'>
          <i class='far fa-calendar-alt sesbasic_text_light' title="<?php echo $this->translate('Start & End Time');?>"></i>
           <span><span><?php echo $this->eventStartEndDates($item,$defaultParams) ?></span></span>
        </span>
      </div>
      <?php  } 
      if(isset($this->joinedcountActive)){
      	 $guestCountStats = isset($joinedmemberOld) ? $joinedmemberOld : ($item->joinedmember ? $item->joinedmember : 0);
     		 $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
      	 echo	"<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
      }
      ?>
      <?php if(isset($this->categoryActive)){ ?>
        <?php if($item->category_id != '' && intval($item->category_id) && !is_null($item->category_id)){ 
          $categoryItem = Engine_Api::_()->getItem('sesevent_category', $item->category_id);
        ?>
        <div class="sesevent_list_stats">
          <span class="widthfull">
            <i class="fa fa-folder-open sesbasic_text_light" title="<?php echo $this->translate('Category:'); ?>"></i>
            <span>
              <a href="<?php echo $categoryItem->getHref(); ?>">
                <?php echo $categoryItem->category_name; ?></a>
              <?php $subcategory = Engine_Api::_()->getItem('sesevent_category',$item->subcat_id); ?>
              <?php if($subcategory && $item->subcat_id){ ?>
              &nbsp;&raquo;&nbsp;<a href="<?php echo $subcategory->getHref(); ?>"><?php echo $subcategory->category_name; ?></a>
              <?php } ?>
              <?php $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$item->subsubcat_id); ?>
              <?php if($subsubcategory && $item->subsubcat_id){ ?>
              &nbsp;&raquo;&nbsp;<a href="<?php echo $subsubcategory->getHref(); ?>"><?php echo $subsubcategory->category_name; ?></a>
              <?php } ?>
            </span>
          </span>
        </div>
        <?php } ?>
      <?php } ?>
      <div class="sesevent_list_stats">
        <?php if(isset($this->likeActive) && isset($item->like_count)) { ?>
        <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up sesbasic_text_light"></i><?php echo $item->like_count; ?></span>
        <?php } ?>
        <?php if(isset($this->commentActive) && isset($item->comment_count)) { ?>
        <span title="<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count))?>"><i class="fa fa-comment sesbasic_text_light"></i><?php echo $item->comment_count;?></span>
        <?php } ?>
        <?php if(isset($this->viewActive) && isset($item->view_count)) { ?>
        <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye sesbasic_text_light"></i><?php echo $item->view_count; ?></span>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($item->favourite_count)) { ?>
        <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count))?>"><i class="fa fa-heart sesbasic_text_light"></i><?php echo $item->favourite_count; ?></span>
        <?php } ?>
        <?php
        if(Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating() && $this->ratingActive){
				echo '<span title="'.$this->translate(array('%s rating', '%s ratings', $item->rating), $this->locale()->toNumber($item->rating)).'"><i class="fa fa-star sesbasic_text_light"></i>'.round($item->rating,1).'/5'. '</span>';
    		}
        ?>
      </div>
    </div>
  </li>
<?php }else if($this->view_type == 'gridInside'){ ?>
  <li class="sesevent_grid_<?php echo $this->gridInsideOutside ; ?> sesbasic_clearfix sesbasic_bxs sesevent_grid_btns_wrap sesae-i-<?php echo $this->mouseOver; ?>" style="width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">
    <div class="sesevent_list_thumb" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;">
      <?php
      $href = $item->getHref();
      $imageURL = $item->getPhotoUrl('thumb.profile');
      ?>
      <a href="<?php echo $href; ?>" class="sesevent_list_thumb_img">
        <span style="background-image:url(<?php echo $imageURL; ?>);"></span>
      </a>
      <?php if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive)){ ?>
      <p class="sesevent_labels">
        <?php if(isset($this->featuredLabelActive) && $item->featured){ ?>
        <span class="sesevent_label_featured">FEATURED</span>
        <?php } ?>
        <?php if(isset($this->sponsoredLabelActive) && $item->sponsored){ ?>
        <span class="sesevent_label_sponsored">SPONSORED</span>
        <?php } ?>
      </p>
      <?php } ?>
      <?php if(isset($this->verifiedLabelActive) && $item->verified == 1){ ?>
        <div class="sesevent_verified_label" title="<?php echo $this->translate("VERIFIED"); ?>"><i class="fa fa-check"></i></div>
      <?php } ?>
      <?php if(isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive)) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); ?>
        <div class="sesevent_grid_btns"> 
          <?php if(isset($this->socialSharingActive)){ ?>
          
          <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $item, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

          <?php } 
          $itemtype = 'sesevent_event';
          $getId = 'event_id';
    
          $canComment =  $item->authorization()->isAllowed($this->viewer, 'comment');
          if(isset($this->likeButtonActive) && $canComment){
          ?>
          <!--Like Button-->
          <?php $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($item->$getId, $item->getType()); ?>
          <a href="javascript:;" data-url="<?php echo $item->$getId ; ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_<?php echo $itemtype; ?> <?php echo ($LikeStatus) ? 'button_active' : '' ; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $item->like_count; ?></span></a>
          <?php } ?>
          <?php
						if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && isset($item->favourite_count) && $this->viewer_id) {
							$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$item->event_id));
							$favClass = ($favStatus)  ? 'button_active' : '';
							$shareOptions = "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $item->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$item->event_id\"><i class='fa fa-heart'></i><span>$item->favourite_count</span></a>";
							echo $shareOptions;
						}
          ?>
					<?php if(isset($this->listButtonActive) && $this->viewer_id) { ?>
						<a href="javascript:;" onclick="opensmoothboxurl('<?php echo $this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$item->event_id),'default',true); ?>')" class="sesbasic_icon_btn  sesevent_add_list"  title="<?php echo  $this->translate('Add To List'); ?>" data-url="<?php echo $item->event_id ; ?>"><i class="fa fa-plus"></i></a>
					<?php } ?>
        </div>
      <?php } ?>
    </div>
    <?php if(isset($this->titleActive) ){ ?>
      <div class="sesevent_grid_in_title_show sesevent_animation">
        <?php if(strlen($item->getTitle()) > $this->title_truncation_grid){ 
        $title = mb_substr($item->getTitle(),0,($this->title_truncation_grid-3)).'...';
        echo $this->htmlLink($item->getHref(),$title, array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
        <?php }else{ ?>
        <?php echo $this->htmlLink($item->getHref(),$item->getTitle(), array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
        <?php } ?>
      </div>
    <?php } ?>
    <div class="sesevent_list_info sesbasic_clearfix">
      <?php if(isset($this->titleActive) ){ ?>
      <div class="sesevent_list_title">
        <?php if(strlen($item->getTitle()) > $this->title_truncation_grid){ 
        $title = mb_substr($item->getTitle(),0,($this->title_truncation_grid - 3)).'...';
        echo $this->htmlLink($item->getHref(),$title, array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
        <?php }else{ ?>
        <?php echo $this->htmlLink($item->getHref(),$item->getTitle(), array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
        <?php } ?>
      </div>
      <?php } ?>
      <?php if(isset($this->byActive)){ ?>
        <div class="sesevent_list_stats">
          <?php $owner = $item->getOwner(); ?>
            <span>
            <i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate('Created By:'); ?>"></i>
            <?php echo $this->htmlLink($owner->getHref(),$owner->getTitle()) ?></span>
        </div>
      <?php } ?>
      <?php if(isset($this->hostActive)){ ?>
	      <div class="sesevent_list_stats">
	        <span><i class="fa fa-male sesbasic_text_light" title="<?php echo $this->translate('Hosted By'); ?>"></i><?php echo $this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author')) ?></span>
        </div>
      <?php } ?>
      <?php if(isset($this->locationActive) && $item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)){ ?>
		      <div class="sesevent_list_stats sesevent_list_location">
		        <span class="widthfull">
		          <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->translate('Location');?>"></i>
		          <span title="<?php echo $item->location; ?>">
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                  <a href="<?php echo $this->url(array('resource_id' => $item->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $item->location ?></a>
                <?php } else { ?>
                  <?php echo $item->location;?>
                <?php } ?>
		          </span>
		        </span>
		      </div>
      <?php } ?>
      <?php if(isset($this->startenddateActive)) { ?>
      <div class='sesevent_list_stats sesevent_list_time'>
        <span class='widthfull'>
          <i class='far fa-calendar-alt sesbasic_text_light' title="<?php echo $this->translate('Start & End Time');?>"></i>
           <span><span><?php echo $this->eventStartEndDates($item,$defaultParams) ?></span></span>
        </span>
      </div>
      <?php } 
      if(isset($this->joinedcountActive)){
      	  $guestCountStats = isset($joinedmemberOld) ? $joinedmemberOld : ($item->joinedmember ? $item->joinedmember : 0);
     		 $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
      	 echo	"<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
      }
      ?>
      <?php if(isset($this->categoryActive)){ ?>
      <?php if($item->category_id != '' && intval($item->category_id) && !is_null($item->category_id)){ 
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $item->category_id);
      ?>
      <div class="sesevent_list_stats">
        <span class="widthfull">
          <i class="fa fa-folder-open sesbasic_text_light"  title="<?php echo $this->translate('Category:'); ?>"></i>
          <span>
            <a href="<?php echo $categoryItem->getHref(); ?>"><?php echo $categoryItem->category_name; ?></a>
            <?php $subcategory = Engine_Api::_()->getItem('sesevent_category',$item->subcat_id); ?>
            <?php if($subcategory && $item->subcat_id) { ?>
            &nbsp;&raquo;&nbsp;<a href="<?php echo $subcategory->getHref(); ?>"><?php echo $subcategory->category_name; ?></a>
            <?php } ?>
            <?php $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$item->subsubcat_id); ?>
            <?php if($subsubcategory && $item->subsubcat_id) { ?>
            &nbsp;&raquo;&nbsp;<a href="<?php echo $subsubcategory->getHref(); ?>"><?php echo $subsubcategory->category_name; ?></a>
            <?php } ?>
          </span>
        </span>
      </div>
      <?php } ?>
      <?php } ?>
      <div class="sesevent_list_stats">
        <?php if(isset($this->likeActive) && isset($item->like_count)) { ?>
        <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up sesbasic_text_light"></i><?php echo $item->like_count; ?></span>
        <?php } ?>
        <?php if(isset($this->commentActive) && isset($item->comment_count)) { ?>
        <span title="<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count))?>"><i class="fa fa-comment sesbasic_text_light"></i><?php echo $item->comment_count;?></span>
        <?php } ?>
        <?php if(isset($this->viewActive) && isset($item->view_count)) { ?>
        <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye sesbasic_text_light"></i><?php echo $item->view_count; ?></span>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($item->favourite_count)) { ?>
        <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count))?>"><i class="fa fa-heart sesbasic_text_light"></i><?php echo $item->favourite_count; ?></span>
        <?php } ?>
      </div>
    </div>
  </li>
<?php }else{ ?>
  <?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
  <li class="sesbasic_item_grid sesbasic_clearfix sesbasic_bxs sesbasic_item_grid_btns_wrap sesbm" style="width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;">
    <div class="sesbasic_item_grid_thumb floatL">
      <?php
      $href = $item->getHref();
      $imageURL = $item->getPhotoUrl('thumb.profile');
      ?>
      <a href="<?php echo $href; ?>" class="sesbasic_item_grid_thumb_img floatL">
        <span class="floatL" style="background-image:url(<?php echo $imageURL; ?>);"></span>
        <div class="sesbasic_item_grid_thumb_overlay"></div>
      </a>
      <?php if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive)){ ?>
        <p class="sesbasic_item_grid_labels">
          <?php if(isset($this->featuredLabelActive) && $item->featured){ ?>
          <span class="sesevent_label_featured">FEATURED</span>
          <?php } ?>
          <?php if(isset($this->sponsoredLabelActive) && $item->sponsored){ ?>
          <span class="sesevent_label_sponsored">SPONSORED</span>
          <?php } ?>
        </p>
      <?php } ?>
      <?php if(isset($this->verifiedLabelActive) && $item->verified == 1){ ?>
        <div class="sesevent_verified_label" title="<?php echo $this->translate("VERIFIED"); ?>" style="display:none;"><i class="fa fa-check"></i></div>
      <?php } ?>
      <?php if(isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive)) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); ?>
        <div class="sesbasic_item_grid_btns"> 
          <?php if(isset($this->socialSharingActive)){ ?>
          
          <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $item, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

          <?php } 
          $itemtype = 'sesevent_event';
          $getId = 'event_id';
    
          $canComment =  $item->authorization()->isAllowed($this->viewer, 'comment');
          if(isset($this->likeButtonActive) && $canComment){
          ?>
          <!--Like Button-->
          <?php $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($item->$getId, $item->getType()); ?>
          <a href="javascript:;" data-url="<?php echo $item->$getId ; ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_<?php echo $itemtype; ?> <?php echo ($LikeStatus) ? 'button_active' : '' ; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $item->like_count; ?></span></a>
          <?php } ?>
          <?php
						if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && isset($item->favourite_count) && $this->viewer_id) {
							$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$item->event_id));
							$favClass = ($favStatus)  ? 'button_active' : '';
							$shareOptions = "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $item->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$item->event_id\"><i class='fa fa-heart'></i><span>$item->favourite_count</span></a>";
							echo $shareOptions;
						}
          ?>
					<?php if(isset($this->listButtonActive) && $this->viewer_id) { ?>
						<a href="javascript:;" onclick="opensmoothboxurl('<?php echo $this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$item->event_id),'default',true); ?>')" class="sesbasic_icon_btn  sesevent_add_list"  title="<?php echo  $this->translate('Add To List'); ?>" data-url="<?php echo $item->event_id ; ?>"><i class="fa fa-plus"></i></a>
					<?php } ?>
        </div>
      <?php } ?>
      
      <?php if(isset($this->titleActive) ){ ?>
        <div class="sesbasic_item_grid_title">
          <?php if(strlen($item->getTitle()) > $this->title_truncation_grid){ 
          $title = mb_substr($item->getTitle(),0,($this->title_truncation_grid - 3)).'...';
          echo $this->htmlLink($item->getHref(),$title, array('class' => 'ses_tooltip', 'data-src' => $item->getGuid()) ) ?>
          <?php }else{ ?>
          <?php echo $this->htmlLink($item->getHref(),$item->getTitle(), array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
          <?php } ?>
          <?php if(isset($this->verifiedLabelActive) && $item->verified == 1){ ?>
          <i class="sesevent_verified_sign fa fa-check-circle"></i>
          <?php } ?>
        </div>
      <?php } ?>
      <div class="sesbasic_item_grid_date">
        <span class="day"><?php echo date('d',strtotime($item->starttime)); ?></span>
        <span class="month"><?php echo date('M',strtotime($item->starttime)); ?></span>
        <span class="year"><?php echo date('Y',strtotime($item->starttime)); ?></span>
      </div>
      <div class="sesbasic_item_grid_owner">
        <a href="<?php echo $host->getHref(); ?>" title="<?php echo $host-> host_name; ?>">
          <img src="<?php echo $host->getPhotoUrl('thumb.icon'); ?>"  class="thumb_icon item_photo_user thumb_icon">
        </a>
      </div>
    </div>
    <div class="sesbasic_item_grid_info  sesbasic_clearfix">
      <?php if(isset($this->byActive)){ ?>
        <div class="sesevent_list_stats">
          <?php $owner = $item->getOwner(); ?>
            <span>
            <i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate('Created By:'); ?>"></i>
            <?php echo $this->htmlLink($owner->getHref(),$owner->getTitle()) ?></span>
        </div>
      <?php } ?>
      <?php if(isset($this->hostActive)){ ?>
	      <div class="sesevent_list_stats">
	        <span><i class="fa fa-male sesbasic_text_light" title="<?php echo $this->translate('Hosted By'); ?>"></i><?php echo $this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author')) ?></span>
        </div>
      <?php } ?>
      <?php if(isset($this->locationActive) && $item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)){ ?>
		      <div class="sesevent_list_stats sesevent_list_location">
		        <span class="widthfull">
		          <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->translate('Location');?>"></i>
		          <span title="<?php echo $item->location; ?>">
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                  <a href="<?php echo $this->url(array('resource_id' => $item->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $item->location ?></a>
                <?php } else { ?>
                  <?php echo $item->location;?>
                <?php } ?>
		          </span>
		        </span>
		      </div>
      <?php } ?>
      <?php if(isset($this->startenddateActive)) { ?>
      <div class='sesevent_list_stats sesevent_list_time'>
        <span class='widthfull'>
          <i class='far fa-calendar-alt sesbasic_text_light' title="<?php echo $this->translate('Start & End Time');?>"></i>
           <span><span><?php echo $this->eventStartEndDates($item,$defaultParams) ?></span></span>
        </span>
      </div>
      <?php } 
       if(isset($this->joinedcountActive)){
      	 $guestCountStats = isset($joinedmemberOld) ? $joinedmemberOld : ($item->joinedmember ? $item->joinedmember : 0);
     		 $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
      	 echo "<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
      }
      ?>
      <?php if(isset($this->categoryActive)){ ?>
      <?php if($item->category_id != '' && intval($item->category_id) && !is_null($item->category_id)){ 
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $item->category_id);
      ?>
      <div class="sesevent_list_stats">
        <span class="widthfull">
          <i class="fa fa-folder-open sesbasic_text_light" title="<?php echo $this->translate('Category:'); ?>"></i>
          <span>
            <a href="<?php echo $categoryItem->getHref(); ?>"><?php echo $categoryItem->category_name; ?></a>
            <?php $subcategory = Engine_Api::_()->getItem('sesevent_category',$item->subcat_id); ?>
            <?php if($subcategory && $item->subcat_id) { ?>
            &nbsp;&raquo;&nbsp;<a href="<?php echo $subcategory->getHref(); ?>"><?php echo $subcategory->category_name; ?></a>
            <?php } ?>
            <?php $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$item->subsubcat_id); ?>
            <?php if($subsubcategory && $item->subsubcat_id) { ?>
            &nbsp;&raquo;&nbsp;<a href="<?php echo $subsubcategory->getHref(); ?>"><?php echo $subsubcategory->category_name; ?></a>
            <?php } ?>
          </span>
        </span>
      </div>
      <?php } ?>
      <?php } ?>
      <div class="sesevent_list_stats">
        <?php if(isset($this->likeActive) && isset($item->like_count)) { ?>
        <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up sesbasic_text_light"></i><?php echo $item->like_count; ?></span>
        <?php } ?>
        <?php if(isset($this->commentActive) && isset($item->comment_count)) { ?>
        <span title="<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count))?>"><i class="fa fa-comment sesbasic_text_light"></i><?php echo $item->comment_count;?></span>
        <?php } ?>
        <?php if(isset($this->viewActive) && isset($item->view_count)) { ?>
        <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count))?>"><i class="fa fa-eye sesbasic_text_light"></i><?php echo $item->view_count; ?></span>
        <?php } ?>
        <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($item->favourite_count)) { ?>
        <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count))?>"><i class="fa fa-heart sesbasic_text_light"></i><?php echo $item->favourite_count; ?></span>
        <?php } ?>
      </div>
      
       <?php
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
			if(isset($this->buyActive)){
			$params['event_id'] = $item->event_id;
			$params['checkEndDateTime'] = date('Y-m-d H:i:s');
			$ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
			if(count($ticket))
				$buyTicket = '<a href="'.$this->url(array('event_id' => $item->custom_url), 'sesevent_ticket', true).'" class="sesbasic_link_btn">'.$this->translate("Book Now").'</a>';
			 else
				$buyTicket = '';
			}else
				$buyTicket = '';
		}
		?>
      <?php if($buyTicket != ''){ ?>
        <div class="sesbasic_item_grid_info_btns clear">
          <?php echo $buyTicket; ?>
        </div>
      <?php } ?>
    </div>
  </li>
<?php } ?>
<?php endforeach; ?>
