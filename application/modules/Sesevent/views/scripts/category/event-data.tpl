<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: event-data.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(empty($this->resultArray['event_data'][0])){ die($this->translate('No event found with this criteria.'));}
$eventData = $this->resultArray['event_data'][0];?>
<div class="sesevent_categories_events_item sesbasic_clearfix clear">
        <?php if(isset($this->eventPhotoActive)) { ?>
          <div class="sesevent_categories_events_items_photo floatL sesevent_grid_btns_wrap">
          	<a class="sesevent_thumb_img" href="<?php echo $eventData->getHref(); ?>">
            	<span style="background-image:url(<?php echo $eventData->getPhotoUrl('thumb.main'); ?>);"></span>
            </a>
          	<?php 
              if(isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) {
                $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $eventData->getHref());
                $shareOptions .= "<div class='sesevent_grid_btns'>";
                if(isset($this->socialSharingActive)) {

                  
                  $shareOptions .= $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $eventData));
                }
                $canComment =  $eventData->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
                if(isset($this->likeButtonActive) && $canComment){
                  $LikeStatus = Engine_Api::_()->sesbasic()->getLikeStatus($eventData->event_id,$eventData->getType());
                  $likeClass = ($LikeStatus) ? ' button_active' : '' ;
                  $shareOptions .= "<a href='javascript:;' data-url=\"$eventData->event_id\" class='sesbasic_icon_btn sesevent_like_sesevent_event_". $eventData->event_id." sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_sesevent_event ".$likeClass ." '><i class='fa fa-thumbs-up'></i><span>$eventData->like_count</span></a>";
                }
                if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && isset($eventData->favourite_count) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
                  $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_event','resource_id'=>$eventData->event_id));
                  $favClass = ($favStatus)  ? 'button_active' : '';
                  $shareOptions .= "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_". $eventData->event_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_event ".$favClass ."' data-url=\"$eventData->event_id\"><i class='fa fa-heart'></i><span>$eventData->favourite_count</span></a>";
                }
                if(isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) { 
                $shareOptions .= '<a href="javascript:;" onclick="opensmoothboxurl('."'".$this->url(array('action' => 'add','module'=>'sesevent','controller'=>'list','event_id'=>$eventData->event_id),'default',true)."'".');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="'.$this->translate('Add To List').'" data-url="'.$eventData->event_id.'"><i class="fa fa-plus"></i></a>';
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
