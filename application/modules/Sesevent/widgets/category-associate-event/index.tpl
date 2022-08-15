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
<?php if(isset($this->identityForWidget) && !empty($this->identityForWidget)){
				$randonNumber = $this->identityForWidget;
      }else{
      	$randonNumber = $this->identity; 
      }
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?> 
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?> 
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php if(!$this->is_ajax){ ?>
 <div id="scrollHeightDivSes_<?php echo $randonNumber; ?>" class="sesbasic_clearfix sesbasic_bxs clear sesevent_categories_events_listing_container">
<?php } ?>
  <?php foreach( $this->paginatorCategory as $item ): ?>
  	<div class="sesevent_categories_events_listing clear sesbasic_clearfix">
    	<div class="sesevent_categories_events_listing_title clear sesbasic_clearfix">
      	<a class="sesbasic_linkinherit" href="<?php echo $item->getBrowseCategoryHref(); ?>?category_id=<?php echo $item->category_id ?>" title="<?php echo $this->translate($item->category_name); ?>"><?php echo $this->translate($item->category_name); ?><?php if(isset($this->count_event) && $this->count_event == 1){ ?><?php echo "(".$item->total_event_categories.")"; ?><?php } ?></a>
       <?php if(isset($this->seemore_text) && $this->seemore_text != ''){ ?>
          <span <?php echo $this->allignment_seeall == 'right' ?  'class="floatR"' : ''; ?> >
          	<a href="<?php echo $item->getBrowseCategoryHref(); ?>?category_id=<?php echo $item->category_id ?>" title="<?php echo $this->translate($item->category_name); ?>">
            <?php $seemoreTranslate = $this->translate($this->seemore_text); ?>
            <?php echo str_replace('[category_name]',$item->category_name,$seemoreTranslate); ?>
          </a>
         </span>
       <?php } ?>
      </div>
	     <?php if($this->view_type == 1){ ?> 
       <?php if(isset($this->resultArray['event_data'][$item->category_id])){ ?>
       <div class="sesevent_categories_events_listing_thumbnails clear sesbasic_clearfix">
       <?php
            $counter = 1;
            $itemEvents = $this->resultArray['event_data'][$item->category_id];
            foreach($itemEvents as $itemEvent){ 
            if($counter == 1)
              $eventData = $itemEvent;
            ?>
          <div class="<?php echo $counter == 1 ? 'thumbnail_active' : '' ?>" <?php if(empty($this->photoThumbnailActive)) { ?> style="display:none;" <?php } ?>>
          <a href="<?php echo $itemEvent->getHref(); ?>" title="<?php echo $itemEvent->getTitle(); ?>" data-url="<?php echo $itemEvent->event_id ?>" class="slideshow_event_data">
            <img src="<?php echo $itemEvent->getPhotoUrl('thumb.normalmain'); ?>" alt="<?php echo $itemEvent->title ?>" class="thumb_icon item_photo_user  thumb_icon"></a>
          </div>
          <?php 
            $counter++;
          } ?>
        </div>      
        <?php } ?>
      <?php if(isset($eventData) && $eventData != '') { ?>
      <div class="sesevent_categories_events_conatiner clear sesbasic_clearfix sesbm">
        <div class="sesevent_categories_events_item sesbasic_clearfix clear">
        <?php if(isset($this->eventPhotoActive)) { ?>
          <div class="sesevent_categories_events_items_photo floatL sesevent_grid_btns_wrap">
          	<a class="sesevent_thumb_img" href="<?php echo $eventData->getHref(); ?>">
            	<span style="background-image:url(<?php echo $eventData->getPhotoUrl('thumb.main'); ?>);"></span>
            </a>
          	<?php 
              if(isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) {
                $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $itemEvent->getHref());
                $shareOptions .= "<div class='sesevent_grid_btns'>";
                if(isset($this->socialSharingActive)) {

                  $shareOptions .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $itemEvent, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit));
                }
                $canComment =  $itemEvent->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
                if(isset($this->likeButtonActive) && $canComment){
                  $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($itemEvent->event_id,$itemEvent->getType());
                  $likeClass = ($LikeStatus) ? ' button_active' : '' ;
                  $shareOptions .= "<a href='javascript:;' data-url=\"$itemEvent->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $itemEvent->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$itemEvent->like_count</span></a>";
                }
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && isset($itemEvent->favourite_count) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
                  $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$itemEvent->event_id));
                  $favClass = ($favStatus)  ? 'button_active' : '';
                  $shareOptions .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $itemEvent->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$itemEvent->event_id\"><i class='fa fa-heart'></i><span>$itemEvent->favourite_count</span></a>";
                }
                if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
                $shareOptions .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$itemEvent->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$itemEvent->event_id.'"><i class="fa fa-plus"></i></a>';
                }
                $shareOptions .= "</div>";
                echo $shareOptions;
              } ?>
            <?php if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive)){ ?>
              <span class="sesevent_labels">
                <?php if(isset($this->featuredLabelActive) && $eventData->featured == 1){ ?>
                  <span class="sesevent_label_featured"><?php echo $this->translate("Featured"); ?></span>
                <?php } ?>
                <?php if(isset($this->sponsoredLabelActive)  && $eventData->sponsored == 1){ ?>
                  <span class="sesevent_label_sponsored"><?php echo $this->translate("Sponsored"); ?></span>
                <?php } ?>
              </span>
            <?php } ?>
          </div>
        <?php } ?>
          <div class="sesevent_categories_events_items_cont">
            <div class="sesevent_categories_events_items_title">
            <?php if(isset($this->titleActive)){ ?>
            	<?php 
              		if(strlen($eventData->title)>$this->title_truncation)
                  	$eventTitle = mb_substr($eventData->title,0,($this->title_truncation - 3)).'...';
                  else
              			$eventTitle = $eventData->title; 
              ?>
            	<a href="<?php echo $eventData->getHref(); ?>" class="ses_tooltip" data-src="<?php echo $eventData->getGuid(); ?>"><?php echo $eventTitle ?></a>
             <?php } 
             if(isset($this->verifiedLabelActive) && $eventData->verified == 1) { ?>
            	  <i class="sesevent_verified_sign fa fa-check-circle" title="<?php echo $this->translate('Verified'); ?>"></i>
          	<?php
             }
             ?>
            </div>
            <div class="sesevent_categories_events_item_cont_btm">
              <?php if(isset($this->byActive)){ ?>
                <div class="sesevent_categories_events_item_stat sesevent_list_stats">
                  <span>
                   <?php $owner = $eventData->getOwner(); ?>
                    <i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate('By');?>"></i>	
                    <?php echo $this->htmlLink($owner->getHref(), $owner->getTitle());?>
                  </span>
                </div>
              <?php } ?>
              
              <?php
              if(isset($this->hostActive)){
              	$host = Engine_Api::_()->getItem('sesevent_host', $eventData->host);
                $host ='<div class="sesevent_list_stats" title="'.$this->translate("Hosted By ").'"><span><i class="fa fa-male sesbasic_text_light"></i>'.$this->htmlLink($host->getHref(),$host->getTitle()).'</span></div>';
              }else
                $host = '';
                echo $host;
              ?>
              
              <?php if($eventData->location && isset($this->locationActive) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)): ?>
                <div class="sesevent_categories_events_item_stat sesevent_list_stats sesevent_list_location">
                  <span class="widthfull">
                    <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->translate('Location'); ?>"></i>
                    <span title="<?php echo $eventData->location; ?>">
                      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                        <a href="<?php echo $this->url(array('resource_id' => $eventData->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $eventData->location ?></a>
                      <?php } else { ?>
                        <?php echo $eventData->location;?>
                      <?php } ?>
                    </span>
                  </span>
                </div>
               <?php endif; ?>
              <?php if(isset($this->startenddateActive)) { ?>
              <div class="sesevent_categories_events_item_stat sesevent_list_stats sesevent_list_time">
                <span class="widthfull">
                  <i class="far fa-calendar-alt sesbasic_text_light" title="<?php echo $this->translate('Start & End Time'); ?>"></i>
                  <?php echo $this->eventStartEndDates($eventData); ?>
                </span>
              </div>
              <?php } ?>
              <?php
                if(isset($this->joinedcountActive)){
                   $guestCountStats = $eventData->joinedmember ? $eventData->joinedmember : 0;
                   $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
                   echo	"<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
                }
                // Show Category
                $showCategory = '';
                if(isset($this->categoryActive)){
                  if($eventData->category_id != '' && intval($eventData->category_id) && !is_null($eventData->category_id)){
                    $categoryItem = Engine_Api::_()->getItem('sesevent_category', $eventData->category_id);
                    $categoryUrl = $categoryItem->getHref();
                    $categoryName = $categoryItem->category_name;
                    if($categoryItem){
                      $showCategory .= "<div class=\"sesevent_list_stats\">
                        <span class=\"widthfull\">
                          <i class=\"fa fa-folder-open sesbasic_text_light\"></i> 
                          <span><a href=\"$categoryUrl\">$categoryName</a>";
                          $subcategory = Engine_Api::_()->getItem('sesevent_category',$eventData->subcat_id);
                          if($subcategory && $eventData->subcat_id != 0){
                            $subCategoryUrl = $subcategory->getHref();
                            $subCategoryName = $subcategory->category_name;
                            $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
                          }
                          $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$eventData->subsubcat_id);
                          if($subsubcategory && $eventData->subsubcat_id != 0){
                            $subsubCategoryUrl = $subsubcategory->getHref();
                            $subsubCategoryName = $subsubcategory->category_name;
                             $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
                          }
                       echo   $showCategory .= "<span></span></div>";
                    }
                  }
                }
                
             ?>
              <div class="sesevent_categories_events_item_stat sesevent_list_stats">
                <?php if(isset($this->likeActive)) { ?>
                  <span title="<?php echo $this->translate(array('%s like', '%s likes', $eventData->like_count), $this->locale()->toNumber($eventData->like_count)); ?>">
                    <i class="fa fa-thumbs-up sesbasic_text_light"></i>
                    <?php echo $eventData->like_count;?>
                  </span>
                <?php } ?>
                <?php if(isset($this->commentActive)) { ?>
                  <span title="<?php echo $this->translate(array('%s comment', '%s comments', $eventData->comment_count), $this->locale()->toNumber($eventData->comment_count)); ?>">
                    <i class="fa fa-comment sesbasic_text_light"></i>
                    <?php echo $eventData->comment_count;?>
                  </span>
               <?php } ?>
               <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive)) { ?>
                  <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $eventData->favourite_count), $this->locale()->toNumber($eventData->favourite_count)); ?>">
                    <i class="fa fa-heart sesbasic_text_light"></i>
                    <?php echo $eventData->favourite_count;?>
                  </span>
               <?php } ?>
               <?php if(isset($this->viewActive)) { ?>
                  <span title="<?php echo $this->translate(array('%s view', '%s views', $eventData->view_count), $this->locale()->toNumber($eventData->view_count)); ?>">
                    <i class="fa fa-eye sesbasic_text_light"></i>
                    <?php echo $eventData->view_count;?>
                  </span>
                <?php } ?>
                <?php if(isset($this->ratingActive)  && isset($eventData->rating) && $eventData->rating > 0 ): ?>
                  <span title="<?php echo $this->translate(array('%s rating', '%s ratings', $eventData->rating), $this->locale()->toNumber($eventData->rating))?>"><i class="fa fa-star"></i><?php echo round($eventData->rating,1).'/5';?></span>
                <?php endif; ?>
              </div>
              <?php if(isset($this->descriptionActive)){ ?>
                <div class="sesevent_list_des clear">
                <?php if(strlen(strip_tags($eventData->description)) > $this->description_truncation){
                        $description = strip_tags(mb_substr($eventData->description,0,($this->description_truncation - 3))).'...';
                      }else{ ?>
                <?php $description = strip_tags($eventData->description); ?>
                <?php } ?>
                <?php echo $description; ?>
                </div>
              <?php } ?>
						</div>
          </div>
        </div>
      <?php for($i = 2;$i <= $counter; $i++){ ?>
      		<div class="sesevent_categories_events_item sesbasic_clearfix clear nodata" style="display:none;"></div>
      <?php } ?>
      	<?php if($counter>2) { ?>
        <div class="sesevent_categories_events_btns">
        	<a href="javascript:;" class="prevbtn sesevent_slideshow_prev"><i class="fa fa-angle-left sesbasic_text_light"></i></a>
          <a href="javascript:;" class="nxtbtn sesevent_slideshow_next"><i class="fa fa-angle-right sesbasic_text_light"></i></a>
        </div>
        <?php } ?>
      </div>
			<?php } ?>
    <?php } else if($this->view_type == 0) { ?>
    <?php if(isset($this->resultArray['event_data'][$item->category_id])){
	    $changeClass = 0;
    ?>
    <?php foreach($this->resultArray['event_data'][$item->category_id] as $itemEvent){ 
      $href = $itemEvent->getHref();
	    $imageURL = $itemEvent->getPhotoUrl('thumb.normalmain');
    ?>
		  <div class="sesevent_eventlist_column_<?php echo $changeClass == 0 ? 'big' : 'small'; ?> floatL">
		    <div class="sesevent_cat_event_list">
		      <div class="sesevent_thumb">
		        <a href="<?php echo $href; ?>">
		          <span class="sesevent_animation" style="background-image:url(<?php echo $imageURL; ?>);"></span>
		         <?php if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive)){ ?>
		          <p class="sesevent_labels">
		          <?php if(isset($this->featuredLabelActive) && $itemEvent->featured == 1){ ?>
		            <span class="sesevent_label_featured"><?php echo $this->translate("Featured"); ?></span>
		          <?php } ?>
		          <?php if(isset($this->sponsoredLabelActive) && $itemEvent->sponsored == 1){ ?>
		            <span class="sesevent_label_sponsored"><?php echo $this->translate("Sponsored"); ?></span>
		          <?php } ?>
		          </p>
		          <?php } ?>
		          <div class="sesevent_cat_event_list_info sesevent_animation">
		            <div>
		              <div class="sesevent_cat_event_list_content">
		              <?php if(isset($this->titleActive)){ ?>
		                <div class="sesevent_cat_event_list_title">
                      <?php 
                          if(strlen($itemEvent->title) > $this->title_truncation)
                            $eventTitle = mb_substr($itemEvent->title,0,($this->title_truncation - 3)).'...';
                          else
                            $eventTitle = $itemEvent->title; 
                      ?>                      
			                <?php echo $this->htmlLink($itemEvent->getHref(),$eventTitle ) ;
                      	if(isset($this->verifiedLabelActive) && $itemEvent->verified == 1) { ?>
                        	<i class="sesevent_verified_sign fa fa-check-circle" title="<?php echo $this->translate('Verified'); ?>"></i>
                      <?php } ?>
		                </div>
		                <?php } ?>
		                <?php if(isset($this->byActive)){ ?>
		                	<div class="sesevent_cat_event_list_stats sesevent_list_stats">
                      	<span>
                        	<span>
                            <?php
                              $owner = $itemEvent->getOwner();
                             echo $this->translate('Posted by %1$s', $this->htmlLink($owner->getHref(),$owner->getTitle()));
                            ?>
                          </span>
                        </span>
		                	</div>
		                <?php } ?>
                    
                   <?php
                    if(isset($this->hostActive)){
                      $host = Engine_Api::_()->getItem('sesevent_host', $itemEvent->host);
                      $host ='<div class="sesevent_cat_event_list_stats sesevent_list_stats" title="'.$this->translate("Hosted By ").'"><span><i class="fa fa-male sesbasic_text_light"></i>'.$this->translate("Hosted By "). ' '.$this->htmlLink($host->getHref(),$host->getTitle()).'</span></div>';
                    }else
                      $host = '';
                      echo $host;
                    ?>
		                <?php if(isset($this->startenddateActive)){ ?>
                    <div class="sesevent_cat_event_list_stats sesevent_list_stats sesevent_list_time">
                      <span class="widthfull">
                       	<?php echo $this->eventStartEndDates($itemEvent); ?>
                      </span>
                    </div>
                    <?php } ?>
										<?php if($itemEvent->location && isset($this->locationActive) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)): ?>
                      <div class="sesevent_cat_event_list_stats sesevent_list_stats sesevent_list_location">
                      	<span class="widthfull">
                         <a href="<?php echo $this->url(array('resource_id' => $itemEvent->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"> <?php echo $itemEvent->location; ?></a>
                        </span>
                      </div>
                    <?php endif; ?>
                    <?php
                      if(isset($this->joinedcountActive)){
                         $guestCountStats = $itemEvent->joinedmember ? $itemEvent->joinedmember : 0;
                         $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
                         echo	"<div title=\"$guestCount\" class=\"sesevent_cat_event_list_stats sesevent_list_stats\"><span class='widthfull'><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
                      }
                     // Show Category
                    $showCategory = '';
                    if(isset($this->categoryActive)){
                      if($itemEvent->category_id != '' && intval($itemEvent->category_id) && !is_null($itemEvent->category_id)){
                        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $itemEvent->category_id);
                        $categoryUrl = $categoryItem->getHref();
                        $categoryName = $categoryItem->category_name;
                        if($categoryItem){
                          $showCategory .= "<div class=\"sesevent_list_stats sesevent_cat_event_list_stats\">
                            <span class=\"widthfull\">
                              <i class=\"fa fa-folder-open sesbasic_text_light\"></i> 
                              <span><a href=\"$categoryUrl\">$categoryName</a>";
                              $subcategory = Engine_Api::_()->getItem('sesevent_category',$itemEvent->subcat_id);
                              if($subcategory && $itemEvent->subcat_id != 0){
                                $subCategoryUrl = $subcategory->getHref();
                                $subCategoryName = $subcategory->category_name;
                                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
                              }
                              $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$itemEvent->subsubcat_id);
                              if($subsubcategory && $itemEvent->subsubcat_id != 0){
                                $subsubCategoryUrl = $subsubcategory->getHref();
                                $subsubCategoryName = $subsubcategory->category_name;
                                 $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
                              }
                           echo   $showCategory .= "<span></span></div>";
                        }
                      }
                    }
                  ?>
		                <div class="sesevent_cat_event_list_stats sesevent_list_stats sesbasic_text_light">
		                  <?php if(isset($this->likeActive) && isset($itemEvent->like_count)) { ?>
		                    <span title="<?php echo $this->translate(array('%s like', '%s likes', $itemEvent->like_count), $this->locale()->toNumber($itemEvent->like_count)); ?>"><i class="fa fa-thumbs-up"></i><?php echo $itemEvent->like_count; ?></span>
		                  <?php } ?>
		                  <?php if(isset($this->commentActive) && isset($itemEvent->comment_count)) { ?>
		                    <span title="<?php echo $this->translate(array('%s comment', '%s comments', $itemEvent->comment_count), $this->locale()->toNumber($itemEvent->comment_count))?>"><i class="fa fa-comment"></i><?php echo $itemEvent->comment_count;?></span>
		                  <?php } ?>
		                  <?php if(isset($this->viewActive) && isset($itemEvent->view_count)) { ?>
		                    <span title="<?php echo $this->translate(array('%s view', '%s views', $itemEvent->view_count), $this->locale()->toNumber($itemEvent->view_count))?>"><i class="fa fa-eye"></i><?php echo $itemEvent->view_count; ?></span>
		                  <?php } ?>
		                   <?php  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($itemEvent->favourite_count)) { ?>
		                    <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $itemEvent->favourite_count), $this->locale()->toNumber($itemEvent->favourite_count))?>"><i class="fa fa-heart"></i><?php echo $itemEvent->favourite_count; ?></span>
		                  <?php } ?>
                      <?php if(isset($this->ratingActive)  && isset($itemEvent->rating) && $itemEvent->rating > 0 ): ?>
                        <span title="<?php echo $this->translate(array('%s rating', '%s ratings', $itemEvent->rating), $this->locale()->toNumber($itemEvent->rating))?>"><i class="fa fa-star"></i><?php echo round($itemEvent->rating,1).'/5';?></span>
                      <?php endif; ?>
		                </div>
		              </div>
		            </div>
		          </div>
		        </a>
			    </div>
		    </div>
			</div>          
	    <?php 
	    $changeClass++;
	    }
	    $changeClass = 0;
	    ?>
      <?php } ?>
    <?php } elseif($this->view_type == 2) {  ?>
    <?php foreach($this->resultArray['event_data'][$item->category_id] as $itemEvent){ 
      $href = $itemEvent->getHref();
	    $imageURL = $itemEvent->getPhotoUrl('thumb.normalmain');
    ?>
    <?php $photoWidth =  is_numeric($this->photo_width) ? $this->photo_width.'px' : $this->photo_width ?>
    <?php $photoHeight =  is_numeric($this->photo_height) ? $this->photo_height.'px' : $this->photo_height ?>
    <?php $infoHeight =  is_numeric($this->info_height) ? $this->info_height.'px' : $this->info_height ?>
    <div class="sesevent_grid1 sesbasic_bxs sesbm" style='height:<?php echo $infoHeight ?>;width:<?php echo  $photoWidth ?>' >
			<div style="height:<?php echo $photoHeight; ?>;" class="sesevent_list_thumb sesevent_grid_btns_wrap">
				<a href="<?php echo $href; ?>" class="sesevent_list_thumb_img">
			    <span style="background-image:url(<?php echo $imageURL; ?>);"></span>
        </a>
				<?php if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive) || isset($this->verifiedLabelActive)){ ?>
					<p class="sesevent_labels">
						<?php if(isset($this->featuredLabelActive) && $itemEvent->featured == 1){ ?>
							<span class="sesevent_label_featured"><?php echo $this->translate("Featured"); ?></span>
						<?php } ?>
						<?php if(isset($this->sponsoredLabelActive) && $itemEvent->sponsored == 1){ ?>
							<span class="sesevent_label_sponsored"><?php echo $this->translate("Sponsored"); ?></span>
						<?php } ?>
            <?php if(isset($this->verifiedLabelActive) && $itemEvent->verified == 1){ ?>
							<span class="sesevent_label_sponsored"><?php echo $this->translate("Verified"); ?></span>
						<?php } ?>
					</p>
				<?php } ?>
			<?php 
		  if(isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) {
        $shareOptions = '';
				$urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $itemEvent->getHref());
				$shareOptions .= "<div class='sesevent_grid_btns sesbasic_pinboard_list_btns'>";
				if(isset($this->socialSharingActive)) {

					$shareOptions .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $itemEvent, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit));
				}
				$canComment =  $itemEvent->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
				if(isset($this->likeButtonActive) && $canComment){
					$LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($itemEvent->event_id,$itemEvent->getType());
					$likeClass = ($LikeStatus) ? ' button_active' : '' ;
					$shareOptions .= "<a href='javascript:;' data-url=\"$itemEvent->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $itemEvent->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$itemEvent->like_count</span></a>";
				}
				if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && isset($itemEvent->favourite_count) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
					$favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$itemEvent->event_id));
					$favClass = ($favStatus)  ? 'button_active' : '';
					$shareOptions .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $itemEvent->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$itemEvent->event_id\"><i class='fa fa-heart'></i><span>$itemEvent->favourite_count</span></a>";
				}
         if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
           $shareOptions .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$itemEvent->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$itemEvent->event_id.'"><i class="fa fa-plus"></i></a>';
         }
				$shareOptions .= "</div>";
				echo $shareOptions;
			} ?>
        
      </div>
      <div style='height:<?php echo $infoHeight; ?>' class="sesevent_list_info">
      	<?php
        // Category Only for grid view
		$showgrid1Category ='';
    $colorCategory = '#990066';
    if(isset($this->categoryActive)){
     if($itemEvent->category_id != '' && intval($itemEvent->category_id) && !is_null($itemEvent->category_id)){
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $itemEvent->category_id);
        $categoryUrl = $categoryItem->getHref();
        $categoryName = $categoryItem->category_name;
          if($categoryItem){
            $colorCategory = (!empty($categoryItem->color)) ? '#'.$categoryItem->color : '#990066';
            $showgrid1Category .= "<span> 
                <a href=\"$categoryUrl\">$categoryName</a>";
                $subcategory = Engine_Api::_()->getItem('sesevent_category',$itemEvent->subcat_id);
                if($subcategory && $itemEvent->subcat_id){
                  $subCategoryUrl = $subcategory->getHref();
                  $subCategoryName = $subcategory->category_name;
                  $showgrid1Category .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
                }
                $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$itemEvent->subsubcat_id);
                if($subsubcategory && $itemEvent->subsubcat_id){
                  $subsubCategoryUrl = $subsubcategory->getHref();
                  $subsubCategoryName = $subsubcategory->category_name;
                  $showgrid1Category .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
                }
               $showgrid1Category .="<style type='text/css'>.sesevent_grid_bubble_$categoryItem->category_id > span:after{border-top-color:$colorCategory ;}</style>";
              $showgrid1Category .= "</span>";
              
          }
       }
    }
    ?>
     <?php
    if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
			if(isset($this->buyActive)){
			$params['event_id'] = $event->event_id;
			$params['checkEndDateTime'] = date('Y-m-d H:i:s');
			$ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
			if(count($ticket))
				$buyTicket = '<a class="sesbasic_link_btn" href="'.$this->url(array('event_id' => $event->custom_url), 'sesevent_ticket', true).'">'.$this->translate("Book Now").'</a>';
			 else
				$buyTicket = '';
			}else
				$buyTicket = '';
		}
    echo "<div class='sesevent_grid_bubble sesevent_grid_bubble_$categoryItem->category_id sesbasic_clearfix' style='background-color:$colorCategory ;'>
					$showgrid1Category
          $buyTicket  
				</div>";
		?>
				<?php if(isset($this->titleActive)) { ?>
					<div class="sesevent_list_title">
          	<?php 
              		if(strlen($itemEvent->title) > $this->title_truncation)
                  	$eventTitle = mb_substr($itemEvent->title,0,($this->title_truncation - 3)).'...';
                  else
              			$eventTitle = $itemEvent->title; 
              ?>
						<?php echo $this->htmlLink($itemEvent->getHref(),$eventTitle,array('class'=>'ses_tooltip','data-src'=>$itemEvent->getGuid()) ) ?>
					</div>
				<?php } ?>											
	        <?php if(isset($this->byActive)){ ?>
	          <div class="sesevent_list_stats">
	            <span>
                <i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate('Posted by'); ?>"></i>
                  <?php
                    $owner = $itemEvent->getOwner();
                    echo $this->translate('%1$s', $this->htmlLink($owner->getHref(),$owner->getTitle()));
                  ?>
              </span>
	          </div>
          <?php } ?>
				<?php
              if(isset($this->hostActive)){
              	$host = Engine_Api::_()->getItem('sesevent_host', $itemEvent->host);
                $host ='<div class="sesevent_list_stats" title="'.$this->translate("Hosted By ").'"><span><i class="fa fa-male sesbasic_text_light"></i>'.$this->htmlLink($host->getHref(),$host->getTitle()).'</span></div>';
              }else
                $host = '';
                echo $host;
              ?>	
		    <?php if($itemEvent->location && isset($this->locationActive) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)): ?>
					<div class="sesevent_list_stats sesevent_list_location">
						<span class="widthfull">
							<i title="Location" class="fas fa-map-marker-alt sesbasic_text_light"></i>
							<span title="<?php echo $itemEvent->location ?>">
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                  <a href="<?php echo $this->url(array('resource_id' => $itemEvent->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $itemEvent->location ?></a>
                <?php } else { ?>
                  <?php echo $itemEvent->location;?>
                <?php } ?>
							</span>
						</span>
					</div>
				<?php endif; ?>
				
				<?php if(isset($this->startenddateActive)): ?>
					<div class="sesevent_list_stats sesevent_list_time">
						<span class="widthfull">
							<i title="<?php echo $this->translate('Start &amp; End Time'); ?>" class="far fa-calendar-alt sesbasic_text_light"></i>
							 <?php echo $this->eventStartEndDates($itemEvent); ?>
						</span>
					</div>
			  <?php endif; ?>
        <?php
          if(isset($this->joinedcountActive)){
             $guestCountStats = $itemEvent->joinedmember ? $itemEvent->joinedmember : 0;
             $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
              echo	"<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
          }
        ?>
				<div class="sesevent_list_stats">
					<?php if(isset($this->likeActive) && isset($itemEvent->like_count)) { ?>
	          <span title="<?php echo $this->translate(array('%s like', '%s likes', $itemEvent->like_count), $this->locale()->toNumber($itemEvent->like_count)); ?>"><i class="fa fa-thumbs-up sesbasic_text_light"></i><?php echo $itemEvent->like_count; ?></span>
	        <?php } ?>
	        <?php if(isset($this->commentActive) && isset($itemEvent->comment_count)) { ?>
	          <span title="<?php echo $this->translate(array('%s comment', '%s comments', $itemEvent->comment_count), $this->locale()->toNumber($itemEvent->comment_count))?>"><i class="fa fa-comment sesbasic_text_light"></i><?php echo $itemEvent->comment_count;?></span>
	        <?php } ?>
	        <?php if(isset($this->viewActive) && isset($itemEvent->view_count)) { ?>
	          <span title="<?php echo $this->translate(array('%s view', '%s views', $itemEvent->view_count), $this->locale()->toNumber($itemEvent->view_count))?>"><i class="fa fa-eye sesbasic_text_light"></i><?php echo $itemEvent->view_count; ?></span>
	        <?php } ?>
	         <?php  if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($itemEvent->favourite_count)) { ?>
	          <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $itemEvent->favourite_count), $this->locale()->toNumber($itemEvent->favourite_count))?>"><i class="fa fa-heart sesbasic_text_light"></i><?php echo $itemEvent->favourite_count; ?></span>
	        <?php } ?>
          <?php if(isset($this->ratingActive)  && isset($itemEvent->rating) && $itemEvent->rating > 0 ): ?>
            <span title="<?php echo $this->translate(array('%s rating', '%s ratings', $itemEvent->rating), $this->locale()->toNumber($itemEvent->rating))?>"><i class="fa fa-star"></i><?php echo round($itemEvent->rating,1).'/5';?></span>
          <?php endif; ?>
				</div>
	      <?php if(isset($this->descriptionActive)){ ?>
          <div class="sesevent_list_des">
          <?php if(strlen(strip_tags($itemEvent->description)) > $this->description_truncation){
                    $description = mb_substr(strip_tags($itemEvent->description),0,($this->description_truncation - 3 )).'...';
                   }else{ ?>
            <?php $description = strip_tags($itemEvent->description); ?>
              <?php } ?>
              <?php echo $description; ?>
          </div>
        <?php } ?>
    	</div>
    	<div class="sesevent_list_footer"><a style="background-color:<?php echo $colorCategory; ?>" class="sesevent_animation" href="<?php echo $href ?>"><?php echo $this->translate("View Details"); ?></a></div>
    </div>
     <?php }
		}else if($this->view_type == 5){
    $advGrid = '';
    foreach($this->resultArray['event_data'][$item->category_id] as $event){ 
      $buyTicket = '';
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) {
      if(isset($this->buyActive)){
        $params['event_id'] = $event->event_id;
        $params['checkEndDateTime'] = date('Y-m-d H:i:s');
        $ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
        if(count($ticket))
          $buyTicket = '<a class="sesbasic_link_btn" href="'.$this->url(array('event_id' => $event->custom_url), 'sesevent_ticket', true).'">'.$this->translate("Book Now").'</a>';
         else
          $buyTicket = '';
      }else
        $buyTicket = '';
    }
    	$host = Engine_Api::_()->getItem('sesevent_host', $event->host);
     	$advgridHeight =  is_numeric($this->info_height) ? $this->info_height.'px' : $this->info_height; 
      $advgridWidth =  is_numeric($this->photo_width) ? $this->photo_width.'px' : $this->photo_width  ;
     	
    	//Advanced Grid View
          $advGrid .= "<li class='sesbasic_item_grid sesbasic_clearfix sesbasic_bxs sesbasic_item_grid_btns_wrap sesbm' style='width:$advgridWidth;height:$advgridHeight'>
          <div class='sesbasic_item_grid_thumb floatL'>";?>    
          <?php
          $advGrid .=
            '<a href="'.$event->getHref().'" class="sesbasic_item_grid_thumb_img floatL">
              <span class="floatL" style="background-image:url('.$event->getPhotoUrl().');"></span>
              <div class="sesbasic_item_grid_thumb_overlay"></div>
            </a>';
           
           $advLabels = '';
            if(isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive) || isset($this->verifiedLabel)) {
              $advLabels .= "<p class=\"sesbasic_item_grid_labels\">";
              if(isset($this->featuredLabelActive) && $event->featured == 1) {
                $advLabels .= "<span class=\"sesevent_label_featured\">FEATURED</span>";
              }
              if(isset($this->sponsoredLabelActive) && $event->sponsored == 1) {
                $advLabels .= "<span class=\"sesevent_label_sponsored\">SPONSORED</span>";
              }
              $advLabels .= "</p>";
            }
            
            $shareoptionsAdv = '';
            if((isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) && $event->is_approved) {
              $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $event->getHref());
              $shareoptionsAdv .= "<div class='sesbasic_item_grid_btns sesevent_grid_btns'>";
              if(isset($this->socialSharingActive)) {

              $shareOptions .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $event, 'param' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit));
              }
              $canComment =  $event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
              if(isset($this->likeButtonActive) && $canComment){
                $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($event->event_id,$event->getType());
                $likeClass = ($LikeStatus) ? ' button_active' : '' ;
          $shareoptionsAdv .= "<a href='javascript:;' data-url=\"$event->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $event->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$event->like_count</span></a>";
              }
              if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($event->favourite_count)  ){ 
                $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$event->event_id));
                $favClass = ($favStatus)  ? 'button_active' : '';
                $shareoptionsAdv .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $event->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$event->event_id\"><i class='fa fa-heart'></i><span>$event->favourite_count</span></a>";
              }
              if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
              $shareoptionsAdv .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$event->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$event->event_id.'"><i class="fa fa-plus"></i></a>';
              }
              $shareoptionsAdv .= "</div>";
            }
             $verifiedlabelAdvGrid = '';
          if(isset($this->verifiedLabelActive) && $event->verified == 1) {
            $verifiedlabelAdvGrid = "<i class=\"sesevent_verified_sign fa fa-check-circle\"></i>";
          }
          if(strlen($event->getTitle()) > $this->advgrid_title_truncation) {
            $advGridViewTitle = mb_substr($event->getTitle(),0,($this->title_truncation-3)).'...';
          }else{
            $advGridViewTitle = $event->getTitle();
          }
          $eventAdvGridTitle =	"<div class=\"sesbasic_item_grid_title\">
													".$this->htmlLink($event->getHref(), $advGridViewTitle,array('class'=>'ses_tooltip','data-src'=>$event->getGuid())).$verifiedlabelAdvGrid."
												</div>";
             
             $location = '';
            ?>
            <?php if(isset($this->locationActive) && $event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)):?>
		<?php $locationText = $this->translate('Location');?>
    <?php $locationvalue = $event->location;?>
    <?php $location = "<div class=\"sesevent_list_stats sesevent_list_location\">
												<span class=\"widthfull\">
													<i class=\"fas fa-map-marker-alt sesbasic_text_light\" title=\"$locationText\"></i>
													<span title=\"$locationvalue\"><a href='".$this->url(array('resource_id' => $event->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true)."' class=\"openSmoothbox\">$locationvalue</a></span>
												</span>
											</div>"; 
    ?>
    <?php endif;?>
            
           
           <?php 
           
             $eventStartEndDate = '';
        if(isset($this->startenddateActive)){
          $eventStartEndDate = "<div class='sesevent_list_stats sesevent_list_time'>
                                <span class='widthfull'>
                                  <i class='far fa-calendar-alt sesbasic_text_light' title='".$this->translate('Start & End Time')."'></i>
                                   ".$this->eventStartEndDates($event)."
                                </span>
                              </div>";	
        }
           // Show Category
        $showCategory = '';
    if(isset($this->categoryActive)){
      if($event->category_id != '' && intval($event->category_id) && !is_null($event->category_id)){
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $event->category_id);
        $categoryUrl = $categoryItem->getHref();
        $categoryName = $categoryItem->category_name;
        if($categoryItem){
          $showCategory .= "<div class=\"sesevent_list_stats\">
            <span class=\"widthfull\">
              <i class=\"fa fa-folder-open sesbasic_text_light\"></i> 
              <span><a href=\"$categoryUrl\">$categoryName</a>";
              $subcategory = Engine_Api::_()->getItem('sesevent_category',$event->subcat_id);
              if($subcategory && $event->subcat_id != 0){
                $subCategoryUrl = $subcategory->getHref();
                $subCategoryName = $subcategory->category_name;
                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
              }
              $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$event->subsubcat_id);
              if($subsubcategory && $event->subsubcat_id != 0){
                $subsubCategoryUrl = $subsubcategory->getHref();
                $subsubCategoryName = $subsubcategory->category_name;
                 $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
              }
            	$showCategory .= "<span></span></div>";
        }
      }
    }
    				
            $stats = '<div class="sesevent_list_stats">';
  
            if(isset($this->commentActive)){
            $stats .= '<span title="'.$this->translate(array('%s comment', '%s comments', $event->comment_count), $this->locale()->toNumber($event->comment_count)).'"><i class="fa fa-comment sesbasic_text_light"></i>'.$event->comment_count.'</span>';
            }
            if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive)){
            $stats .= '<span title="'.$this->translate(array('%s favourite', '%s favourites', $event->favourite_count), $this->locale()->toNumber($event->favourite_count)).'"><i class="fa fa-heart sesbasic_text_light"></i>'. $event->favourite_count.'</span>';
            }
            if(isset($this->viewActive)){
            $stats .= '<span title="'. $this->translate(array('%s view', '%s views', $event->view_count), $this->locale()->toNumber($event->view_count)).'"><i class="fa fa-eye sesbasic_text_light"></i>'.$event->view_count.'</span>';
            }
            if(isset($this->likeActive)){
             $stats .= '<span title="'.$this->translate(array('%s like', '%s likes', $event->like_count), $this->locale()->toNumber($event->like_count)).'"><i class="fa fa-thumbs-up sesbasic_text_light"></i>'.$event->like_count.'</span> ';
             }
             if(isset($this->ratingActive)){
              if(Engine_Api::_()->getApi('core', 'sesevent')->allowReviewRating()){
                $stats .= '<span title="'.$this->translate(array('%s rating', '%s ratings', $event->rating), $this->locale()->toNumber($event->rating)).'"><i class="fa fa-star sesbasic_text_light"></i>'.round($event->rating,1).'/5'. '</span>';
              }
             }
            
             $stats .= '</div>';
          
            $advGrid .=  $advLabels.
            $shareoptionsAdv.
            $eventAdvGridTitle.'
            <div class="sesbasic_item_grid_date">
              <span class="day">'.date('d',strtotime($event->starttime)).'</span>
              <span class="month">'.date('M',strtotime($event->starttime)).'</span>
              <span class="year">'. date('Y',strtotime($event->starttime)).'</span>
            </div>
            <div class="sesbasic_item_grid_owner">
              <a href="'.$host->getHref().'" title="'.$host-> host_name.'">
                <img src="'.$host->getPhotoUrl("thumb.icon").'"  class="thumb_icon item_photo_user thumb_icon"></a>
            </div>
          </div>
          <div class="sesbasic_item_grid_info  sesbasic_clearfix">';?>    
            <?php if(isset($this->byActive)){ ?>
              <?php $owner = $event->getOwner(); ?>
              <?php $advGrid .=' <div class="sesevent_list_stats">
                <span>
                <i class="fa fa-user sesbasic_text_light" title="'.$this->translate('By:').'"></i>
                '.$this->htmlLink($owner->getHref(),$owner->getTitle()).'</span>
              </div>';
             } ?>
            <?php if(isset($this->hostActive)){ ?>
             <?php
              $advGrid .= 
              '<div class="sesevent_list_stats">
                <span><i class="fa fa-male sesbasic_text_light" title="'.$this->translate('Hosted By').'"></i>'.$this->htmlLink($host->getHref(), $host->getTitle(), array('class' => 'thumbs_author')).'</span>
              </div>';
             }
              $advGrid .= $location.$eventStartEndDate.$showCategory;
              $advGrid .= $stats
              ?>
            <?php if($buyTicket != ''){ 
            $advGrid .=  '<div class="sesbasic_item_grid_info_btns clear">
                 '.$buyTicket.'
              </div>';?>
            <?php } ?>
         <?php
         $advGrid .='
          </div>
        </li>';  
			}
      echo $advGrid;
    }
		 ?>
    </div>    
 <?php 
 		$eventData = '';
 		endforeach;
     if($this->paginatorCategory->getTotalItemCount() == 0 && !$this->is_ajax){  ?>
     <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has created an event yet.');?>
        <?php if ($this->can_create):?>
          <?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action' => 'create','module'=>'sesevent'), "sesevent_general",true).'">', '</a>'); ?>
        <?php endif; ?>
      </span>
    </div>
		<?php } 
    if($this->loadOptionData == 'pagging'){ ?>
 		 <?php echo $this->paginationControl($this->paginatorCategory, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>$randonNumber)); ?>
 <?php } ?>
 <?php if(!$this->is_ajax){ ?>
  </div>
  	<?php if($this->loadOptionData != 'pagging'){ ?>  
   <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
  
  <?php } ?>
  <?php } ?>
 <?php if(!$this->is_ajax){ ?>
<script type="text/javascript">
<?php if($this->view_type == 0){ ?>
function dynamicWidth(){
	var objectClass = jqueryObjectOfSes('.sesevent_cat_event_list_info');
	for(i=0;i<objectClass.length;i++){
			jqueryObjectOfSes(objectClass[i]).find('div').find('.sesevent_cat_event_list_content').find('.sesevent_cat_event_list_title').width(jqueryObjectOfSes(objectClass[i]).width());
	}
}
dynamicWidth();
<?php } ?>
sesJqueryObject (document).on('click','.sesevent_slideshow_prev',function(e){
		e.preventDefault();
		var activeClassIndex;
		var elem = sesJqueryObject (this).parent().parent().parent().find('.sesevent_categories_events_listing_thumbnails').children();
		var elemLength = elem.length;
		for(i=0;i<elemLength;i++){
			if(elem[i].hasClass('thumbnail_active')){
				 activeClassIndex = i;
				break;	
			}
		}
		if(activeClassIndex == 0){
			var changeIndex = elemLength-1;
		}else if((activeClassIndex+1) == elemLength){
			var changeIndex =activeClassIndex-1 ;	
		}else{
			var changeIndex = activeClassIndex-1; 	
		}
		sesJqueryObject (this).parent().parent().parent().find('.sesevent_categories_events_listing_thumbnails').children().eq(changeIndex).find('a').click();
});

sesJqueryObject (document).on('click','.sesevent_slideshow_next',function(e){
	e.preventDefault();
	var activeClassIndex;
	var elem = sesJqueryObject (this).parent().parent().parent().find('.sesevent_categories_events_listing_thumbnails').children();
	var elemLength = elem.length;
	for(i=0;i<elemLength;i++){
		if(elem[i].hasClass('thumbnail_active')){
			 activeClassIndex = i;
			break;	
		}
	}
	if((activeClassIndex+1) == elemLength){
		var changeIndex = 0;	
	}else if(activeClassIndex == 0){
		var changeIndex = activeClassIndex+1;
	}else{
		var changeIndex = activeClassIndex+1; 	
	}
	sesJqueryObject (this).parent().parent().parent().find('.sesevent_categories_events_listing_thumbnails').children().eq(changeIndex).find('a').click();
});
sesJqueryObject (document).on('click','.slideshow_event_data',function(e){
	e.preventDefault();
	var event_id = sesJqueryObject (this).attr('data-url');
	if(sesJqueryObject (this).parent().hasClass('thumbnail_active')){
			return false;
	}
	if(!album_id)
		return false;
	 var elIndex = sesJqueryObject (this).parent().index();
	 var totalDiv = sesJqueryObject (this).parent().parent().find('div');
	 for(i=0;i<totalDiv.length;i++){
			 totalDiv[i].removeClass('thumbnail_active');
	 }
	 sesJqueryObject (this).parent().addClass('thumbnail_active');
	 var containerElem = sesJqueryObject (this).parent().parent().parent().find('.sesevent_categories_events_conatiner').children();
	 for(i=0;i<containerElem.length;i++){
	 	if(i != (containerElem.length-1))
			containerElem[i].hide();
	 }
	sesJqueryObject (containerElem).get(elIndex).show();
	if(sesJqueryObject (containerElem).get(elIndex).hasClass('nodata')){
	 sesJqueryObject (containerElem).eq(elIndex).html('<div class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
	 new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "sesevent/category/event-data/event_id/"+event_id,
      'data': {
        format: 'html',
				params:'<?php echo json_encode($this->params); ?>',
				event_id : sesJqueryObject (this).attr('data-url'),
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject (containerElem).eq(elIndex).html(responseHTML);
				sesJqueryObject (containerElem).eq(elIndex).removeClass('nodata');
      }
    }).send();
	}
});
var valueTabData ;
// globally define available tab array
	var availableTabs_<?php echo $randonNumber; ?>;
	var requestTab_<?php echo $randonNumber; ?>;
  availableTabs_<?php echo $randonNumber; ?> = <?php echo json_encode($this->defaultOptions); ?>;
<?php if($this->loadOptionData == 'auto_load'){ ?>
		window.addEvent('load', function() {
		 sesJqueryObject (window).scroll( function() {
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
<?php } ?>
function paggingNumber<?php echo $randonNumber; ?>(pageNum){
	 sesJqueryObject ('.overlay_<?php echo $randonNumber ?>').css('display','block');
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>",
      'data': {
        format: 'html',
        page: pageNum,    
				params :'<?php echo json_encode($this->params); ?>', 
				is_ajax : 1,
				identity : '<?php echo $randonNumber; ?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject ('.overlay_<?php echo $randonNumber ?>').css('display','none');
        document.getElementById('scrollHeightDivSes_<?php echo $randonNumber; ?>').innerHTML =  responseHTML;
			<?php if($this->view_type == 1){ ?>
				<?php }else{ ?>
				dynamicWidth();
				<?php } ?>
      }
    })).send();
    return false;
}
</script>
<?php } ?>
<script type="text/javascript">
var defaultOpenTab ;
  viewMoreHide_<?php echo $randonNumber; ?>();
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    if ($('view_more_<?php echo $randonNumber; ?>'))
      $('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginatorCategory->count() == 0 ? 'none' : ($this->paginatorCategory->count() == $this->paginatorCategory->getCurrentPageNumber() ? 'none' : '' )) ?>";
  }
  function viewMore_<?php echo $randonNumber; ?> (){
    var openTab_<?php echo $randonNumber; ?> = '<?php echo $this->defaultOpenTab; ?>';
    document.getElementById('view_more_<?php echo $randonNumber; ?>').style.display = 'none';
    document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = '';    
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>/openTab/" + openTab_<?php echo $randonNumber; ?>,
      'data': {
        format: 'html',
        page: <?php echo $this->page + 1; ?>,    
				params :'<?php echo json_encode($this->params); ?>', 
				is_ajax : 1,
				identity : '<?php echo $randonNumber; ?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById('scrollHeightDivSes_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('scrollHeightDivSes_<?php echo $randonNumber; ?>').innerHTML + responseHTML;
				document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = 'none';
				<?php if($this->view_type == 1){ ?>
				<?php }else{ ?>
				dynamicWidth();
				<?php } ?>
      }
    })).send();
    return false;
  }
</script>
