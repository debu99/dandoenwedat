<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _subjectfeedtabs.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
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
          'action_id':sesAdvancedActivityGetAction_id,
          'getUpdates':1,
        'nolayout' : true,
        'ads_ids': adsIdString,
        'subject' : en4.core.subject.guid,
      },
      evalScripts : true,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript) {

          if(!sesAdvancedActivityGetFeeds){
              sesJqueryObject('#activity-feed').append(responseHTML);
          }else{
              sesJqueryObject('#activity-feed').html(responseHTML);
          }
        if(sesJqueryObject('#activity-feed').children().length)
         sesJqueryObject('.sesadv_noresult_tip').hide();
        else
         sesJqueryObject('.sesadv_noresult_tip').show();
        //initialize feed autoload counter
        counterLoadTime = 0;
        sesadvtooltip();
        initSesadvAnimation();
        Smoothbox.bind($('activity-feed'));
        sesJqueryObject('.sesadvancedactivity_filter_img').hide();
          activateFunctionalityOnFirstLoad();
      }
    });
   filterResultrequest.send();
 });
</script>
<?php 
  $lists = $this->lists;
 ?>
<div class="sesact_feed_filters sesbasic_clearfix sesbasic_bxs sesbm" style="display: none;">
  <ul class="sesadvancedactivity_filter_tabs sesbasic_clearfix">
    <li style="display:none;" class="sesadvancedactivity_filter_img"><i class='fas fa-circle-notch fa-spin'></i></li>
    
    <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="<?php echo 'own'; ?>"><?php echo strlen($this->subject()->getTitle()) > 20 ? $this->string()->truncate($this->subject()->getTitle(),20).'...' : $this->subject()->getTitle(); ?></a></li>
    
    <?php 
     $counter = 1;
     foreach($lists as $activeList){ 
       if(@$activeList['filtertype'] == 'all' || @$activeList['filtertype'] == 'post_self_buysell' || @$activeList['filtertype'] == 'post_self_file')
        {
     ?>
      <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="<?php echo @$activeList['filtertype']; ?>"><?php echo $this->translate(@$activeList['title']); ?></a></li>
     <?php 
      }
     } ?>
   <?php if($this->subject() && method_exists($this->subject(),'approveAllowed') && method_exists($this->subject(),'canApproveActivity') && $this->subject()->canApproveActivity($this->subject()) ){
    $approveAllowed = $this->subject()->approveAllowed();
    if($approveAllowed){
   ?>
   <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="<?php echo 'unapprovedfeed'; ?>"><?php echo $this->translate("Un-Approved Feeds"); ?></a></li>
   <?php }
    }
    ?>
   
   <?php if($this->viewer()->getIdentity() && $this->subject()->getGuid() == $this->viewer()->getGuid()){ ?>
     <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="hiddenpost"><?php echo $this->translate("Posts You've Hidden"); ?></a></li>
     <li class="sesadvancedactivity_filter_tabsli"><a href="javascript:;" data-src="taggedinpost"><?php echo $this->translate("Posts You're Tagged In"); ?></a></li>
   <?php } ?>
  </ul>
</div>
<script type="application/javascript">
sesJqueryObject(document).ready(function(e){
  var elem = sesJqueryObject('.sesadvancedactivity_filter_tabs').children();
  if(elem.length == 2){
      sesJqueryObject('.sesact_feed_filters').hide();
  }else{
    sesJqueryObject(elem).eq(1).addClass('active');  
  }
});
</script>
