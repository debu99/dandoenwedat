<?php $subject = $this->subject; ?>
  <?php if(in_array('coverphoto',$this->globalEnableTip) && in_array('coverphoto',$this->moduleEnableTip) && isset($this->subject->cover_photo) && $this->subject->cover_photo != 0 && $this->subject->cover_photo != ''){ 
  			 $cover =	Engine_Api::_()->storage()->get($this->subject->cover_photo, '')->getPhotoUrl(); 
   }else
   		$cover =''; 
	?>
<div class="sesbasic_tooltip sesbasic_clearfix sesbasic_bxs<?php if($cover){?> sesbasic_tooltip_cover_wrap<?php } ?>">
	<?php if($cover){?>
  <div class="sesbasic_tooltip_cover">
    <img src="<?php echo $cover; ?>">
  </div>
  <?php } ?>
  <div class="sesbasic_tooltip_content sesbasic_clearfix">
  <?php if(in_array('mainphoto',$this->globalEnableTip) && in_array('mainphoto',$this->moduleEnableTip)){ ?>
    <div class="sesbasic_tooltip_photo sesbd">
      <a href="<?php echo $subject->getHref(); ?>"><img src="<?php echo $subject->getPhotoUrl(); ?>"></a>
    </div>
   <?php } ?>
    <div class="sesbasic_tooltip_info">
      <?php if(in_array('title',$this->globalEnableTip) && in_array('title',$this->moduleEnableTip)){ ?>
        <div class="sesbasic_tooltip_info_title">  
          <a href="<?php echo $subject->getHref(); ?>"><?php echo $subject->getTitle(); ?></a></a>
        </div>
      <?php } ?>
      <?php if( in_array('hostedby',$this->moduleEnableTip)){ 
      	$host = Engine_Api::_()->getItem('sesevent_host', $subject->host);
      ?>
      <p class="sesbasic_tooltip_info_stats sesevent_list_stats sesevent_list_time">
      	<span class="widthfull">
        	<i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate("Hosted By"); ?>"></i>
      		<a href="<?php echo $host->getHref(); ?>" class="thumbs_author"><?php echo $host->getTitle(); ?></a>
        </span>
      </p>
      <?php } ?>
     <?php if( in_array('startendtime',$this->moduleEnableTip)){ ?>
      <p class="sesbasic_tooltip_info_stats sesevent_list_stats sesevent_list_time">
      	<span class="widthfull">
        	<i class="fa fa-calendar sesbasic_text_light" title="<?php echo $this->translate("Start & End Time"); ?>"></i>
      		<?php echo $this->eventStartEndDates($subject); ?>
        </span>
      </p>
     <?php } ?>
     <?php
     if( in_array('category',$this->moduleEnableTip) && isset($subject->category_id) && $subject->category_id != '' && intval($subject->category_id) && !is_null($subject->category_id)){
        $categoryItem = Engine_Api::_()->getItem('sesevent_category', $subject->category_id);
        $categoryUrl = $categoryItem->getHref();
        $categoryName = $this->translate($categoryItem->category_name);
        $showCategory = '';
        if($categoryItem){
          $showCategory .= "<p class=\"sesevent_list_stats sesevent_list_time\">
            <span class=\"widthfull\">
              <i class=\"fa fa-folder-open sesbasic_text_light\"  title=".$this->translate('Category')."></i> 
              <a href=\"$categoryUrl\">$categoryName</a>";
              $subcategory = Engine_Api::_()->getItem('sesevent_category',$subject->subcat_id);
              if($subcategory && $subject->subcat_id){
                $subCategoryUrl = $subcategory->getHref();
                $subCategoryName = $subcategory->category_name;
                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subCategoryUrl\">$this->translate($subCategoryName)</a>";
              }
              $subsubcategory = Engine_Api::_()->getItem('sesevent_category',$subject->subsubcat_id);
              if($subsubcategory && $subject->subsubcat_id){
                $subsubCategoryUrl = $subsubcategory->getHref();
                $subsubCategoryName = $subsubcategory->category_name;
                $showCategory .= "&nbsp;&raquo;&nbsp;<a href=\"$subsubCategoryUrl)\">$this->translate($subsubCategoryName)</a>";
              }
            	$showCategory .= "</span></p>";
              echo $showCategory;
        }
      }
     ?>
     <?php if( in_array('location',$this->moduleEnableTip) && isset($subject->location) &&  $subject->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)){ ?>
      <p class="sesbasic_tooltip_info_stats sesevent_list_stats sesevent_list_location">
      	<span class="widthfull">
        	<i class="fa fa-map-marker sesbasic_text_light" title="<?php echo $this->translate('location'); ?>"></i>
      		<a href="<?php echo $this->url(array('resource_id' => $subject->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" class="opensmoothboxurl"><?php echo $subject->location; ?></a>
        </span>
      </p>
     <?php } ?>
    </div>
	</div>
  <div class="sesbasic_tooltip_footer sesbasic_clearfix sesbm clear">
  <?php
  if(in_array('socialshare',$this->moduleEnableTip)){
  $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $subject->getHref());
  
  $socialshareIcon = $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $subject, 'params' => 'feed', 'socialshare_enable_plusicon' => $this->socialshare_enable_plusiconevent, 'socialshare_icon_limit' => $this->socialshare_icon_limitevent));
  
	$socialshare = '<div>'.$socialshareIcon.'</div>';

  echo $socialshare;
  }
  ?>
	<?php  if(in_array('buybutton',$this->moduleEnableTip) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')) { 
  		$params['event_id'] = $subject->event_id;
			$params['checkEndDateTime'] = date('Y-m-d H:i:s');
			$ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket($params);
			if(count($ticket)){
				echo '<a class="sesbasic_button"  href="'.$this->url(array('event_id' => $subject->custom_url), 'sesevent_ticket', true).'">'.$this->translate("Book Now").'</a>';
      }
   } ?>
  </div>
</div>
<?php die; ?>