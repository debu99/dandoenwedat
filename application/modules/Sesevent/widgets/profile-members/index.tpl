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
<script type="text/javascript">
  var urlGuestInviter = en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>;
</script>
<?php if( !$this->is_ajax ): ?>
<script type="application/javascript">
sesJqueryObject(document).on('click','.openSmoothBoxInUrl',function(e){
	e.preventDefault();
	openSmoothBoxInUrl(sesJqueryObject(this).attr('href'));
});
var requestGuestInvites;
sesJqueryObject(document).on('click','.sesevent_profile_quests_tabs li > a',function(){
 var type = sesJqueryObject(this).attr('id');
 sesJqueryObject('.sesevent_profile_quests_tabs > li').removeClass('selected');
 sesJqueryObject(this).parent().addClass('selected');
 sesJqueryObject('.sesevent_members').html('<div class="sesbasic_view_more_loading"> <img src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sesbasic/externals/images/loading.gif" /> </div>');
 if(typeof requestGuestInvites != 'undefined')	
 	requestGuestInvites.cancel();
	 requestGuestInvites = (new Request.HTML({
			url:urlGuestInviter,
			data: {
			format: 'html',
				'type': type,
				'is_ajax':1,
  			'subject' : en4.core.subject.guid,
				 viewtype:"<?php echo $this->viewtype; ?>",
				'searchVal':sesJqueryObject('#sesevent_members_search_input').val(),
			},
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
					sesJqueryObject('.sesevent_members').html(responseHTML);
				}
		}));
		requestGuestInvites.send();
});
</script>
<?php endif; ?>
<?php if( !$this->is_ajax ): ?>
  <div class="sesevent_members_info sesbasic_clearfix sesbasic_bxs">
    <ul class="sesevent_profile_quests_tabs sesevent_members_filters floatL sesbm">
      <li class="sesbm selected"><a href="javascript:;" id=""><?php echo $this->translate("All"); ?> (<?php echo $this->paginator->getTotalItemCount(); ?>)</a></li>
      <li class="sesbm"><a href="javascript:;" id="attending"><?php echo $this->translate("Attending"); ?> (<?php echo $this->attending; ?>)</a></li>
       <li class="sesbm"><a href="javascript:;" id="maybeattending"><?php echo $this->translate("Maybe Attending"); ?> (<?php echo $this->maybeattending; ?>)</a></li>
      <li class="sesbm"><a href="javascript:;" id="notattending"><?php echo $this->translate("Not Attending"); ?> (<?php echo $this->notattending; ?>)</a></li>
      <?php if($this->canEdit){ ?>
        <li class="sesbm"><a href="javascript:;" id="new"><?php echo $this->translate("Approval or Waiting Request"); ?> (<?php echo $this->newattending; ?>)</a></li>
      <?php } ?>
    </ul>
    <div class="sesevent_members_total floatR">
     <input id="sesevent_members_search_input" type="text" placeholder="<?php echo $this->translate('Search Guests');?>" >
    </div>
  </div>
<?php endif; ?>

<?php if(!$this->is_ajax){ ?>
 <ul class='sesevent_members' id="sesevent_members">
<?php } ?>
<?php if( $this->paginator->getTotalItemCount() > 0 ){ ?>
    <?php foreach( $this->paginator as $member ):
      if( !empty($member->resource_id) ) {
        $memberInfo = $member;
        $member = $this->item('user', $memberInfo->user_id);
      } else {
        $memberInfo = $this->event->membership()->getMemberInfo($member);
      }
?>
      <li id="sesevent_member_<?php echo $member->getIdentity() ?>" class="sesevent_profile_members_list sesevent_grid_btns_wrap sesbasic_bxs">
        <div class="sesevent_profile_members_list_photo">
          <?php echo $this->htmlLink($member->getHref(), $this->itemPhoto($member, 'thumb.profile'), array('class' => 'sesevent_members_icon')) ?>
        </div>
        <a href="<?php echo $member->getHref();?>" class="sesevent_profile_members_list_overlay"></a>
        <div class='sesevent_profile_members_list_info'>
          <div class='sesevent_profile_members_list_title'>
            <?php echo $this->htmlLink($member->getHref(), $member->getTitle()) ?>
            <?php // Titles ?>
            <?php if( $this->event->getParent()->getGuid() == ($member->getGuid())): ?>
              <?php echo $this->translate('(%s)', ( $memberInfo->title ? $memberInfo->title : $this->translate('owner') )) ?>
            <?php endif; ?>
          </div>
          <div class="sesevent_profile_members_list_rsvp">
            <?php if( $memberInfo->rsvp == 0 ): ?>
              <?php echo $this->translate('Not Attending') ?>
            <?php elseif( $memberInfo->rsvp == 1 ): ?>
              <?php echo $this->translate('Maybe Attending') ?>
            <?php elseif( $memberInfo->rsvp == 2 ): ?>
              <?php echo $this->translate('Attending') ?>
            <?php else: ?>
              <?php echo $this->translate('Awaiting Reply') ?>
            <?php endif; ?>
          </div>
        </div>
       
       <?php if($this->event->isOwner($this->viewer()) && !$this->event->isOwner($member)){ ?>
        <div class="sesevent_grid_btns">
          <a href="javascript:void(0);" class="sesbasic_icon_btn"><i class="fa fa-ellipsis-v"></i></a>  
          <div class='sesevent_profile_members_list_options'>
            <?php // Remove/Promote/Demote member ?>
            <?php if( $this->event->isOwner($this->viewer())): ?>
              <?php if( !$this->event->isOwner($member) && $memberInfo->active == true ): ?>
                <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'member', 'action' => 'remove', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Remove Member'), array(
                  'class' => 'openSmoothBoxInUrl'
                )) ?>
              <?php endif; ?>
              <?php if( $memberInfo->active == false && $memberInfo->resource_approved == false ): ?>
                <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'member', 'action' => 'approve', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Approve Request'), array(
                  'class' => 'openSmoothBoxInUrl'
                )) ?>
                <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'member', 'action' => 'remove', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Reject Request'), array(
                  'class' => 'openSmoothBoxInUrl'
                )) ?>
              <?php endif; ?>
              <?php if( $memberInfo->active == false && $memberInfo->resource_approved == true ): ?>
                <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'member', 'action' => 'cancel', 'event_id' => $this->event->getIdentity(), 'user_id' => $member->getIdentity()), $this->translate('Cancel Invite'), array(
                  'class' => 'openSmoothBoxInUrl'
                )) ?>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
       <?php } ?>
      </li>
    <?php endforeach;?>
    <?php if($this->viewtype == 'pagging'){ ?>
      <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>'guest')); ?>
    <?php } ?>
    <?php if($this->viewtype != 'pagging' && !$this->is_ajax):?>      
        <div class="sesbasic_load_btn" id="view_more_guest" onclick="viewMore_guest();" > <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_guest", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_view_more_loading sesbasic_view_more_loading_guest" id="loading_image_guest" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div> 
    <?php endif;?>    
<?php }else{ ?>
<li>
	<div class="tip">
    <span>
     <?php echo $this->translate('Nobody has joined this event that matches your search criteria.');?>
    </span>
  </div>   
</li>
<?php  } ?>

<script type="application/javascript">
<?php $randonNumber = 'guest'; ?>
  <?php if($this->viewtype != 'pagging') { ?>
    viewMoreHide_<?php echo $randonNumber; ?>();
    function viewMoreHide_<?php echo $randonNumber; ?>() {
      if ($('view_more_<?php echo $randonNumber; ?>'))
	$('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
    }
    function viewMore_<?php echo $randonNumber; ?> () {
      sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
      sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show(); 
			requestViewMore_<?php echo $randonNumber; ?> = new Request.HTML({
					method: 'post',
					'url': urlGuestInviter,
					'data': {
						format: 'html',
						'subject' : en4.core.subject.guid,
						searchVal:sesJqueryObject('#sesevent_members_search_input').val(),
						page: <?php echo $this->page + 1; ?>, 
						viewtype:"<?php echo $this->viewtype; ?>",   
						is_ajax : 1,
					},
					onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
						  document.getElementById('sesevent_members').innerHTML = document.getElementById('sesevent_members').innerHTML + responseHTML;
							sesJqueryObject('#loading_image_<?php echo $randonNumber;?>').hide();
							viewMoreHide_<?php echo $randonNumber; ?>();
					}
				});
      requestViewMore_<?php echo $randonNumber; ?>.send();
      return false;
    }
    <?php }else{ ?>
    function paggingNumber<?php echo $randonNumber; ?>(pageNum){
      sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').css('display','block');
      requestViewMore_<?php echo $randonNumber; ?> = (new Request.HTML({
				method: 'post',
				'url': urlGuestInviter,
					'data': {
						format: 'html',
						'subject' : en4.core.subject.guid,
						searchVal:sesJqueryObject('#sesevent_members_search_input').val(),
						viewtype:"<?php echo $this->viewtype; ?>",
						page:pageNum,    
						is_ajax : 1,
					},
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
						document.getElementById('sesevent_members').innerHTML = responseHTML;
						 sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $randonNumber?>').css('display','none');
				}
      }));
      requestViewMore_<?php echo $randonNumber; ?>.send();
      return false;
    }
  <?php } ?>

</script>
<?php if(!$this->is_ajax){ ?>
 </ul>
<script type="application/javascript">
sesJqueryObject('#sesevent_members_search_input').keydown(function(e){
	if(e.keyCode == 13){
		if(sesJqueryObject('#sesevent_members_search_input').val() != ''){
			sesJqueryObject('.sesevent_profile_quests_tabs').find('.selected').find('a').trigger('click');
		}
		return false;
	}
	
});

var tabId_pM = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_pM);	
});
</script>
<?php } ?>
