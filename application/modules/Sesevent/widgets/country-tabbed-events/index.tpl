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
<?php if(isset($this->identityForWidget) && !empty($this->identityForWidget)):?>
  <?php $randonNumber = $this->identityForWidget;?>
<?php else:?>
  <?php $randonNumber = $this->identity;?> 
<?php endif;?>
<?php if(!$this->is_ajax){ ?>
<?php if($this->tab_option == 'default'){ ?>
<div class="layout_core_container_tabs">
<div class="tabs_alt tabs_parent" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
<?php if($this->tab_option == 'advance'){ ?>
<div class="sesbasic_tabs_container sesbasic_clearfix sesbasic_bxs">
  <div class="sesbasic_tabs sesbasic_clearfix" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
<?php if($this->tab_option == 'filter'){ ?>
<div class="sesbasic_filter_tabs_container sesbasic_clearfix sesbasic_bxs">
  <div class="sesbasic_filter_tabs sesbasic_clearfix" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
<?php if($this->tab_option == 'vertical'){ ?>
<div class="sesbasic_v_tabs_container sesbasic_clearfix sesbasic_bxs">
  <div class="sesbasic_v_tabs sesbasic_clearfix" <?php if(count($this->defaultOptions) ==1){ ?> style="display:none" <?php } ?>>
<?php } ?>
    <ul id="tab-widget-sesevent-<?php echo $randonNumber; ?>">
       <?php 
         $defaultOptionArray = array();
         foreach($this->defaultOptions as $key=>$valueOptions){
         $defaultOptionArray[] = $key;
       ?>
       <li <?php if($this->defaultOpenTab == $key){ ?>class="active"<?php } ?> id="sesTabContainer_<?php echo $randonNumber; ?>_<?php echo $key; ?>">
         <a href="javascript:;" data-src="<?php echo $key; ?>" onclick="changeTabSes_<?php echo $randonNumber; ?>('<?php echo $key; ?>')"><?php echo $this->translate(($valueOptions)); ?></a>
       </li>
       <?php } ?>
      </ul>
    </div>
  <div class="sesbasic_tabs_content sesbasic_clearfix">
<?php } ?>
<?php include APPLICATION_PATH . '/application/modules/Sesevent/views/scripts/_eventBrowseWidget.tpl'; ?>
<?php if(!$this->is_ajax){ ?>
	</div>
</div>
<?php } ?>
<?php if(!$this->is_ajax):?>
	<script type="application/javascript"> 
  var availableTabs_<?php echo $randonNumber; ?>;
  var requestTab_<?php echo $randonNumber; ?>;
  <?php if(isset($defaultOptionArray)){ ?>
    availableTabs_<?php echo $randonNumber; ?> = <?php echo json_encode($defaultOptionArray); ?>;
  <?php  } ?>
  var defaultOpenTab ;
  function changeTabSes_<?php echo $randonNumber; ?>(valueTab){
    if(sesJqueryObject("#sesTabContainer_<?php echo $randonNumber; ?>_"+valueTab).hasClass('active'))
   	 return;
    var id = '_<?php echo $randonNumber; ?>';
    var length = availableTabs_<?php echo $randonNumber; ?>.length;
    for (var i = 0; i < length; i++){
      if(availableTabs_<?php echo $randonNumber; ?>[i] == valueTab){
					document.getElementById('sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).addClass('active');
					sesJqueryObject('#sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).addClass('sesbasic_tab_selected');
      }else{
				sesJqueryObject('#sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).removeClass('sesbasic_tab_selected');
				document.getElementById('sesTabContainer'+id+'_'+availableTabs_<?php echo $randonNumber; ?>[i]).removeClass('active');
      }
    }
    if(valueTab){
      if(document.getElementById("error-message_<?php echo $randonNumber;?>"))
        document.getElementById("error-message_<?php echo $randonNumber;?>").style.display = 'none';
			if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
				document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML ='';
      sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
      //sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
			document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = '<div class="sesbasic_view_more_loading" id="loading_image_<?php echo $randonNumber; ?>"> <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sesbasic/externals/images/loading.gif" /> </div>';
      if(typeof(requestTab_<?php echo $randonNumber; ?>) != 'undefined')
				requestTab_<?php echo $randonNumber; ?>.cancel();
      if(typeof(requestViewMore_<?php echo $randonNumber; ?>) != 'undefined')
				requestViewMore_<?php echo $randonNumber; ?>.cancel();
      defaultOpenTab = valueTab;
      requestTab_<?php echo $randonNumber; ?> = new Request.HTML({
				method: 'post',
				'url': en4.core.baseUrl+"widget/index/mod/sesevent/name/<?php echo $this->widgetName; ?>/openTab/"+valueTab,
				'data': {
					format: 'html',  
					params : params<?php echo $randonNumber; ?>, 
					is_ajax : 1,
					searchParams:searchParams<?php echo $randonNumber; ?> ,
					identity : '<?php echo $randonNumber; ?>',
					height:'<?php echo $this->height;?>',
					type:activeType_<?php echo $randonNumber ?>
				},
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
								sesJqueryObject('#map-data_<?php echo $randonNumber;?>').removeClass('checked');
								if(sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').length)
									sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').css('display','none');
								else
									sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').hide(); 
								sesJqueryObject('#error-message_<?php echo $randonNumber;?>').remove();
								sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').html(responseHTML);
								var check = true;
								if(document.getElementById('browse-widget_<?php echo $randonNumber; ?>'))
										document.getElementById('browse-widget_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML ;
								sesJqueryObject('.sesevent_browse_listing_<?php echo $randonNumber;?>').find('#browse-widget_<?php echo $randonNumber; ?>').first().attr('id','');
								oldMapData_<?php echo $randonNumber; ?> = [];
								
								if(document.getElementById('map-data_<?php echo $randonNumber;?>') && sesJqueryObject('.sesbasic_view_type_options_<?php echo $randonNumber; ?>').find('.active').attr('rel') == 'map') {
								if(document.getElementById('sesevent_map_view_<?php echo $randonNumber;?>'))	
									document.getElementById('sesevent_map_view_<?php echo $randonNumber;?>').style.display = 'block';
									var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
									if(sesJqueryObject.isArray(mapData) && sesJqueryObject(mapData).length) {
										newMapData_<?php echo $randonNumber ?> = mapData;
										sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, newMapData_<?php echo $randonNumber ?>);
										initialize_<?php echo $randonNumber?>();	
										mapFunction_<?php echo $randonNumber?>();
									}else{
											if(typeof  map_<?php echo $randonNumber;?> == 'undefined')	{
														sesJqueryObject('#map-data_<?php echo $randonNumber; ?>').html('');
														initialize_<?php echo $randonNumber?>();	
											}	
									}
								}else if(document.getElementById('map-data_<?php echo $randonNumber;?>')){
									var mapData = sesJqueryObject.parseJSON(sesJqueryObject('#temporary-data-<?php echo $randonNumber?>').find('#browse-widget_<?php echo $randonNumber; ?>').find('#map-data_<?php echo $randonNumber; ?>').html());
									sesJqueryObject.merge(oldMapData_<?php echo $randonNumber; ?>, mapData);
									sesJqueryObject('#map-data_<?php echo $randonNumber;?>').addClass('read');
								}
								if(sesJqueryObject('.pin_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									if(document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>'))
										document.getElementById('sesevent_pinboard_view_<?php echo $randonNumber;?>').style.display = 'block';
									pinboardLayout_<?php echo $randonNumber ?>('force','true');
								}
								else if(sesJqueryObject('.masonry_selectView_<?php echo $randonNumber;?>').hasClass('active')) {
									sesJqueryObject("#sesevent_masonry_view_<?php echo $randonNumber;?>").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->masonry_height); ?>});
								}
								if(document.getElementById('temporary-data-<?php echo $randonNumber?>'))
									document.getElementById('temporary-data-<?php echo $randonNumber?>').innerHTML = '';
								sesJqueryObject('.sesbasic_view_more_loading_<?php echo $randonNumber;?>').hide();
								if(typeof viewMoreHide_<?php echo $randonNumber; ?> == 'function')
								viewMoreHide_<?php echo $randonNumber; ?>();
							}
      });
      requestTab_<?php echo $randonNumber; ?>.send();
      return false;			
    }
  }
</script> 
<?php endif;?>