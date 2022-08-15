<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: get-event.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="text/javascript">
Sessmoothbox.css.push("<?php echo $this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'; ?>");
  function viewMore() {
    if ($('view_more'))
    $('view_more').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";       
    document.getElementById('view_more').style.display = 'none';
    document.getElementById('loading_image').style.display = '';
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'sesevent/ajax/get-event/',
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        viewmore: 1,
				params:"<?php echo $this->currentDay; ?>",     
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				document.getElementById('view_more').destroy();
				document.getElementById('loading_image').destroy();
        document.getElementById('like_results').innerHTML = document.getElementById('like_results').innerHTML + responseHTML;
				resizesessmoothbox();
      }
    })).send();
    return false;
  }
<?php if(empty($this->viewmoreT) && empty($this->viewmore)) { ?>
sesJqueryObject(document).on('click','#selectedDay',function(){
	sesJqueryObject(this).addClass('disabled');
	var that = this;
	var params = sesJqueryObject(this).attr('rel');
(new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'sesevent/ajax/get-event/',
      'data': {
        format: 'html',
        page: "1",
				params:params,
				viewmoreT:1,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				sesJqueryObject(that).removeClass('disabled');
			 if(document.getElementById('view_more'))
			  document.getElementById('view_more').destroy();
			 if(document.getElementById('loading_image'))
				document.getElementById('loading_image').destroy();
        document.getElementById('sessmoothbox_container').innerHTML = responseHTML;
				resizesessmoothbox();
      }
    })).send();
    return false;	
})
<?php } ?>
</script>
<?php if (empty($this->viewmore)){ ?>
  <div class="sesevent_list_popup sesbasic_bxs sesbasic_clearfix">
    <div class="sesevent_list_popup_header sesbm">
      <?php echo date('l, F j, Y',strtotime($this->currentDay)); ?>
    </div>
    <div class="sesevent_list_popup_top_btns sesbasic_clearfix sesbm">    
      <!-- PREVIOUS DAY -->
      <?php $previousDay = date('Y-m-d H:i:s',strtotime('-1 day',strtotime($this->currentDay))); ?>
      <a href="javascript:;" id="selectedDay" rel="<?php echo date('Y-m-d',strtotime('-1 day',strtotime($this->currentDay))); ?>" class="sesbasic_button floatL "><i class="fa fa-angle-left"></i><?php echo $this->translate(date('l',strtotime($previousDay))).' '.date('j',strtotime($previousDay)); ?></a>
      <!-- NEXT DAY -->
      <?php $nextDay = date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->currentDay))); ?>
      <a href="javascript:;" id="selectedDay" rel="<?php echo date('Y-m-d',strtotime('+1 day',strtotime($this->currentDay))); ?>" class="sesbasic_button floatL"><?php echo $this->translate(date('l',strtotime($nextDay))).' '.date('j',strtotime($nextDay)); ?><i class="fa fa-angle-right right"></i></a>
    </div>
    <div id="like_results" class="sesevent_list_popup_content">
<?php } ?>
    <?php if (count($this->paginator) > 0) { ?>
      <?php foreach ($this->paginator as $value){ ?>
        <div class="item_list sesbm sesbasic_clearfix">
          <div class="item_list_thumb floatL">
            <?php echo $this->htmlLink($value->getHref(), $this->itemPhoto($value, 'thumb.profile'), array('title' => $value->getTitle(), 'target' => '_parent')); ?>
          </div>
          <div class="item_list_info">
            <div class="item_list_title">
              <?php echo $this->htmlLink($value->getHref(), $value->getTitle(), array('title' => $value->getTitle(), 'target' => '_parent')); ?>
            </div>
             <?php   $host = Engine_Api::_()->getItem('sesevent_host', $value->host);          ?>
              <p class="sesbasic_tooltip_info_stats sesevent_list_stats sesevent_list_time">
                <span class="widthfull">
                  <i class="fa fa-male sesbasic_text_light" title="<?php echo $this->translate("Hosted By"); ?>"></i>
                  <a href="<?php echo $host->getHref(); ?>" class="thumbs_author"><?php echo $host->getTitle(); ?></a>
                </span>
              </p>
            <div class="item_list_date sesevent_list_stats sesevent_list_time">
              <span class="widthfull">
                <i class="far fa-calendar-alt sesbasic_text_light" title="Start & End Time"></i>
                <?php echo $this->eventStartEndDates($value); ?>
              </span>
            </div>
     <?php
     	if(isset($value->category_id) && $value->category_id != '' && intval($value->category_id) && !is_null($value->category_id)){
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $value->category_id);
        $categoryUrl = $categoryItem->getHref();
        $categoryName = $categoryItem->category_name;
        $showCategory = '';
        if($categoryItem){
          $showCategory .= "<p class=\"sesevent_list_stats sesevent_list_time\">
            <span class=\"widthfull\">
              <i class=\"fa fa-folder-open sesbasic_text_light\"></i> 
              <a href=\"$categoryUrl\">$categoryName</a>";
              $subcategory = Engine_Api::_()->getItem('sesevent_category',$value->subcat_id);
              if($subcategory && $value->subcat_id){
                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$subCategoryName</a>";
                $subCategoryUrl = $subcategory->getHref();
                $subCategoryName = $subcategory->category_name;
              }
              $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$value->subsubcat_id);
              if($subsubcategory && $value->subsubcat_id){
                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$subsubCategoryName</a>";
                $subsubCategoryUrl = $subsubcategory->getHref();
                $subsubCategoryName = $subsubcategory->category_name;
              }
            	$showCategory .= "</span></p>";
              echo $showCategory;
        }
      }
     ?>
     <?php if( isset($value->location) &&  $value->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)){ ?>
      <p class="sesbasic_tooltip_info_stats sesevent_list_stats sesevent_list_location">
      	<span class="widthfull">
        	<i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $value->location; ?>"></i>
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
            <a href="<?php echo $this->url(array('resource_id' => $value->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $value->location ?></a>
          <?php } else { ?>
            <?php echo $value->location;?>
          <?php } ?>
        </span>
      </p>
     <?php } ?>
          </div>
        </div>
      <?php } ?> 
      <?php } else { ?>
        <div class="tip">
					<span><?php echo $this->translate("Nobody has created an event on this date."); ?></span>          
        </div>
      <?php }?>     
    <?php if (!empty($this->paginator) && $this->paginator->count() > 1){ ?>
      <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()){ ?>        
         <div class="sesbasic_load_btn" id="view_more" onclick="viewMore();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn" id="loading_image" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
        
  <?php } ?>
    <?php } ?>
  <?php if (empty($this->viewmore)){ ?>
  	</div>
  </div>
<?php } ?>
<?php if($this->viewmoreT || $this->viewmore){ die;} ?>
