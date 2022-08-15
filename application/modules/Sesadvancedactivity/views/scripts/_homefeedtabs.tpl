<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _homefeedtabs.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<script type="application/javascript">
var filterResultrequest;
 sesJqueryObject(document).on('click','ul.sesadvancedactivity_filter_tabs li a',function(e){
//    if(sesJqueryObject(this).parent().hasClass('active') || sesJqueryObject(this).hasClass('viewmore'))
//     return false;
   if(sesJqueryObject(this).hasClass('viewmore'))
    return false;
   sesJqueryObject('.sesadvancedactivity_filter_img').show();
   sesJqueryObject('.sesadvancedactivity_filter_tabsli').removeClass('active sesadv_active_tabs');
   sesJqueryObject(this).parent().addClass('active sesadv_active_tabs');
   var filterFeed = sesJqueryObject(this).attr('data-src');
   if(typeof filterResultrequest != 'undefined')
    filterResultrequest.cancel();
    var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'widget', 'action' => 'index', 'content_id' => $this->identity), 'default', true) ?>';
    var hashTag = sesJqueryObject('#hashtagtextsesadv').val();
    
    var adsIds = sesJqueryObject('.sescmads_ads_listing_item');
    var adsIdString = "";
    if(adsIds.length > 0){
       sesJqueryObject('.sescmads_ads_listing_item').each(function(index){
         var dataFeedItem = sesJqueryObject(this).attr('data-activity-feed-item');
         if(typeof dataFeedItem == "undefined")
          adsIdString = sesJqueryObject(this).attr('rel')+ "," + adsIdString ;
       });
    }
      
    filterResultrequest = new Request.HTML({
      url : url+"?hashtag="+hashTag+'&isOnThisDayPage='+isOnThisDayPage+'&isMemberHomePage='+isMemberHomePage,
      data : {
        format : 'html',
        'filterFeed' : filterFeed,
        'feedOnly' : true,
        'ads_ids': adsIdString,
          'getUpdates':1,
        'nolayout' : true,
        'subject' : '<?php echo !empty($this->subjectGuid) ? $this->subjectGuid : "" ?>',
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {
        if(!sesAdvancedActivityGetFeeds){
            sesJqueryObject('#activity-feed').append(responseHTML);
        }else{
            sesJqueryObject('#activity-feed').html(responseHTML);
        }

        if(sesJqueryObject('#activity-feed').children().length){
         sesJqueryObject('.sesadv_noresult_tip').hide();
          if(sesJqueryObject('#feed_viewmore').css('display') == 'none' && sesJqueryObject('#feed_loading').css('display') == 'none')
          sesJqueryObject('#feed_no_more_feed').show();
        }
        else{
          sesJqueryObject('#feed_no_more_feed').hide();
         sesJqueryObject('.sesadv_noresult_tip').show();
          
        }
        //initialize feed autoload counter
        counterLoadTime = 0;
        sesadvtooltip();
        Smoothbox.bind($('activity-feed'));
        sesJqueryObject('.sesadvancedactivity_filter_img').hide();
        initSesadvAnimation();
        feedUpdateFunction();
          activateFunctionalityOnFirstLoad();
      }
    });
   filterResultrequest.send();
 });

</script>
<?php 
  $filterViewMoreCount = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedactivity.visiblesearchfilter',6);
  $lists = $this->lists;
 ?>
<div class="sesact_feed_filters sesbasic_clearfix sesbasic_bxs sesbm" style="display: none;">
  <ul class="sesadvancedactivity_filter_tabs sesbasic_clearfix">
    <li style="display:none;" class="sesadvancedactivity_filter_img"><i class='fas fa-circle-notch fa-spin'></i></li>
   <?php 
   $counter = 1;
   $netwrokStarted = false;
   $listStarted = false;
   $listsCount = count($lists);
   foreach($lists as $activeList){
    if($counter > $filterViewMoreCount)
      break;
    if(isset($activeList['network_id'])){
      if(!$netwrokStarted){  $netwrokStarted = true; ?>
        <li class="_sep sesbm"></li>
     <?php
      } ?>
    <li class="sesadvancedactivity_filter_tabsli <?php echo $counter == 1 ? 'active sesadv_active_tabs' : ''; ?>"><a href="javascript:;" class="sesadv_tooltip" data-src="<?php echo 'network_filter_'.$activeList['network_id']; ?>" title="<?php echo $this->translate($activeList['title']); ?>"><span><?php echo $this->translate($activeList['title']); ?></span></a></li>
   <?php   
    }else if(isset($activeList['list_id'])){
    
      if(!$listStarted){  $listStarted = true; ?>
        <li class="_sep sesbm"></li>
     <?php
      } ?>
    <li class="sesadvancedactivity_filter_tabsli <?php echo $counter == 1 ? 'active sesadv_active_tabs' : ''; ?>"><a href="javascript:;" class="sesadv_tooltip" data-src="<?php echo 'member_list_'.$activeList['list_id']; ?>" title="<?php echo $this->translate($activeList['title']); ?>"><span><?php echo $this->translate($activeList['title']); ?></span></a></li>
   <?php   
    }else{
    ?>
   
    <li class="sesadvancedactivity_filter_tabsli <?php echo $counter == 1 ? 'active sesadv_active_tabs' : ''; ?>"><a href="javascript:;" class="sesadv_tooltip" data-src="<?php echo $activeList['filtertype']; ?>" title="<?php echo $this->translate($activeList['title']); ?>">
       <?php $storage = Engine_Api::_()->storage()->get($activeList['file_id'], '');
         if($storage){
        $image = $storage->getPhotoUrl(); ?>
         <img src="<?php echo $image; ?>" style="height:15px;width:15px;">
     <?php }?>
      <span><?php echo $this->translate($activeList['title']); ?></span></a></li>
   
   <?php 
   }
    ++$counter;
   } ?>
   <?php if($listsCount > $filterViewMoreCount){ ?>
    <li class="sesact_feed_filter_more sesact_pulldown_wrapper">
    	<a href="javascript:;" class="viewmore"><?php echo $this->translate("More"); ?>&nbsp;<i class="fa fa-angle-down"></i></a>
    	<div class="sesact_pulldown">
				<div class="sesact_pulldown_cont isicon">
        	<ul>
          <?php 
           $counter = 1;
           foreach($lists as $activeList){
            if($counter <= $filterViewMoreCount){
              ++$counter;
              continue;
             }
             if(isset($activeList['network_id'])){
                if(!$netwrokStarted){ $netwrokStarted = true; ?>
                  <li class="_sep sesbm"></li>
               <?php
                } ?>
              <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="<?php echo 'network_filter_'.$activeList['network_id']; ?>"><?php echo $this->translate($activeList['title']); ?></a></li>
             <?php   
              }else if(isset($activeList['list_id'])){
                if(!$listStarted){ $listStarted = true; ?>
                  <li class="_sep sesbm"></li>
               <?php
                } ?>
              <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="<?php echo 'member_list_'.$activeList['list_id']; ?>"><?php echo $this->translate($activeList['title']); ?></a></li>
             <?php   
              }else{
            ?>
            <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="<?php echo $activeList['filtertype']; ?>"><?php echo $this->translate($activeList['title']); ?></a></li>
           <?php 
              }
           } ?>
           <!-- <li class="_sep sesbm"></li>-->
        	</ul>
        </div>													
      </div>
    </li>
    <?php if($this->viewer()->getIdentity()){ ?>
    <li class="sesadvancedactivity_filter_tabsli sesact_feed_filter_setting"><a href="javascript:;" class="sessmoothbox viewmore sesadv_tooltip " title="<?php echo $this->translate('Settings');?>" data-url="sesadvancedactivity/ajax/settings/"><i class="fa fa-cog" aria-hidden="true"></i></a></li> 
    <?php } ?>
  <?php } ?>  
    
  </ul>
</div>