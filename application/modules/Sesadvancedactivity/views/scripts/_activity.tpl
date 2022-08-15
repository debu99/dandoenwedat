<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: gifcontent.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!empty($this->getAction))
   $action = $this->getAction;
if(empty($action->action_id))
return;
    $attachmentItems = $action->getAttachments();
    $actionAttachment = count($attachmentItems) ? $attachmentItems : array();
   $detailsTable = Engine_Api::_()->getDbTable('details','sesadvancedactivity');

   $actionDetails = $detailsTable->isRowExists($action->action_id);
  if($actionDetails)
    $actionDetails = Engine_Api::_()->getItem('sesadvancedactivity_detail',$actionDetails);
  else
    return;
  ?>
   <?php
    $detail_id = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity')->isRowExists($action->getIdentity());
    if($detail_id) {
     $detailAction = Engine_Api::_()->getItem('sesadvancedactivity_detail',$detail_id);
      }
  ?>
  <?php if( !$this->noList ): ?>
<li id="activity-item-<?php echo $action->action_id ?>" data-activity-feed-item="<?php echo $action->action_id ?>" class="sesact_pinfeed sesact_pinfeed_hidden sesbasic_clearfix _photo<?php echo $this->userphotoalign; ?><?php if(!empty($fromActivityFeed)){ ?> sescommunityads_ad_id sescmads_ads_listing_item<?php } ?>" <?php if(!empty($fromActivityFeed)){ ?> rel="<?php echo $ad->getIdentity(); ?>"<?php } ?>>
  <?php endif; ?>
      
      <?php if(!empty($fromActivityFeed)){ ?>
         <?php include('application/modules/Sescommunityads/views/scripts/widget-data/_hiddenData.php'); ?>
      <?php } ?>
      <section <?php if(!empty($fromActivityFeed)){ ?> class="sescmads_ads_item_img" <?php } ?>>
      <?php !empty($this->commentForm) ? $this->commentForm->setActionIdentity($action->action_id) : ""; ?>
      <?php if(!$this->isOnThisDayPage && !Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){ ?>
      <script type="text/javascript">
        (function(){
          var action_id = '<?php echo $action->action_id ?>';
          en4.core.runonce.add(function(){
            $('activity-comment-body-' + action_id).autogrow();
            en4.activity.attachComment($('activity-comment-form-' + action_id));
          });
        })();
      </script>
      <?php } ?>
      <div class="sesact_feed_header sesbasic_clearfix">
      <?php // User profile photo ?>
      <div class='sesact_feed_item_photo'>
        <?php 
          $getSubject = $action->getSubject();
          if($actionDetails && !empty($actionDetails->sesresource_id) && !empty($actionDetails->sesresource_type)){
             $itemSubject = Engine_Api::_()->getItem($actionDetails->sesresource_type,$actionDetails->sesresource_id); 
             if($itemSubject)
              $getSubject = $itemSubject;
          }
        ?>      
        <?php echo $this->htmlLink($getSubject->getHref(), $this->itemPhoto($getSubject, 'thumb.profile', $getSubject->getTitle(),array('class'=>'ses_tooltip','data-src'=>$getSubject->getGuid())))?>
      </div>
      <div class="sesact_feed_header_cont sesbasic_clearfix">
        <?php if($this->subject() && $pintotop){ ?>
        <?php   $isPinned = $action->isPinPost(array('resource_type'=>$this->subject()->getType(),'resource_id'=>$this->subject()->getIdentity(),'action_id'=>$action->getIdentity()));
           }
        ?>
        <?php if(!empty($fromActivityFeed) && $ad->user_id != $this->viewer()->getIdentity()){ ?>
          <div class="sesact_feed_options sesact_pulldown_wrapper">
            <a href="javascript:void(0);" class="sesact_feed_options_btn"><i class="fa fa-angle-down"></i></a>
            <div class="sesact_pulldown">
              <div class="sesact_pulldown_cont">
                <ul>
                  <li><a href="javascript:;" class="sescomm_hide_ad"><?php echo $this->translate('hide ad'); ?></a></li>
                  <!--<li><a target="_blank" href="<?php echo $this->url(array('module'=>'sescommunityads','controller'=>'index','action'=>'why-seeing'),'sescommunityads_whyseeing',false); ?>" class="sescomm_seeing_ad"><?php echo $this->translate('why am i seeing this?'); ?></a></li>-->
                  <li>
                      <?php $useful = $ad->isUseful(); ?>
                      <a href="javascript:;" class="sescomm_useful_ad<?php $useful ? ' active' : ''; ?>" data-rel="<?php echo $ad->getIdentity() ?>" data-selected="<?php echo $this->translate('This ad is useful'); ?>" data-unselected="<?php echo $this->translate('Remove from useful'); ?>"><?php echo !$useful ? $this->translate('This ad is useful') : $this->translate('Remove from useful');?></a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        <?php } ?>
        <?php if(empty($fromActivityFeed) &&  $this->viewer()->getIdentity() && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost')){ ?>
          <?php if($this->subject() && $pintotop && $isPinned){ ?>
            <span title="<?php echo $this->translate("Pinned Post"); ?>" class="sesadv_tooltip sesadv_pin_post"><i class="fa fa-thumb-tack"></i></span>
          <?php } ?>
          <div class="sesact_feed_options sesact_pulldown_wrapper">
            <a href="javascript:void(0);" class="sesact_feed_options_btn"><i class="fa fa-angle-down"></i></a>
            <div class="sesact_pulldown">
              <div class="sesact_pulldown_cont">
                <ul>
                <?php if(!$detailAction->sesapproved){ ?>
                <li><a href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module'=> 'sesadvancedactivity', 'controller'=> 'index', 'action' => 'approve-feed', 'action_id' => $action->action_id),'default',true); ?>"><span><?php echo $this->translate("Approve Feed");?></span></a></li>
                  <?php
                }
                ?>
                 <?php if(!$this->isOnThisDayPage && $this->viewer()->getIdentity() && ((
                $this->activity_moderate || (
                  $this->allow_delete && (
                    ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                    ('user' == $action->object_type && $this->viewer()->getIdentity()  == $action->object_id)
                  )
                )
            ) || ($this->subject() && method_exists($this->subject(),'canEditActivity') && $this->subject()->canEditActivity($this->subject())) )): ?>
                
                <?php if(@$action->params['type'] != 'facebookpostembed' && ($action->canEdit() || ($this->subject() && method_exists($this->subject(),'canEditActivity') && $this->subject()->canEditActivity($this->subject())))): ?>
                  <li><a id="sesact_edit_<?php echo $action->getIdentity(); ?>" href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module'=> 'sesadvancedactivity', 'controller'=> 'index', 'action' => 'edit-post', 'action_id' => $action->action_id),'default',true); ?>"><span><?php echo $this->translate("Edit Feed");?></span></a></li>
                <?php endif; ?>
               <?php endif; ?>
               <?php if( $this->viewer()->getIdentity()  && ((
                $this->activity_moderate || (
                  $this->allow_delete && (
                    ('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                    ('user' == $action->object_type && $this->viewer()->getIdentity()  == $action->object_id)
                  )
                )
            ) || $this->subject() && method_exists($this->subject(),'canEditActivity') && $this->subject()->canEditActivity($this->subject())  ) ): ?>
                  <li><a class="sessmoothbox" href="javascript:;" data-url="<?php echo $this->url(array('module'=> 'sesadvancedactivity', 'controller'=> 'index', 'action' => 'delete', 'action_id' => $action->action_id),'default',true); ?>"><span><?php echo $this->translate("Delete Feed");?></span></a></li>
             <?php endif; ?>
              <?php if($pintotop && $this->subject() && $action->subject_id == $this->viewer()->getIdentity()){ ?>
                  <li><a class="pintotopfeedsesadv" href="javascript:;" data-url="<?php echo $this->url(array('module'=> 'sesadvancedactivity', 'controller'=> 'index', 'action' => 'pintotop', 'action_id' => $action->action_id,'res_id'=>$this->subject()->getIdentity(),'res_type'=>$this->subject()->getType()),'default',true); ?>"><span><?php echo !$isPinned ? $this->translate("Pin Post to Top") : $this->translate("Unpin Post From Top"); ?></span></a></li>
            <?php } ?>
             <?php if(!$detailAction->schedule_time){ ?>
                 <?php if(Engine_Api::_()->getDbTable('savefeeds','sesadvancedactivity')->isSaved(array('action_id'=>$action->getIdentity(),'user_id'=>$this->viewer()->getIdentity()))){ ?>
                  <li><a href="javascript:;" class="unsave_feed_adv" data-save="<?php echo $this->translate('Save Feed'); ?>" data-unsave="<?php echo $this->translate('Unsave Feed'); ?>" data-actionid="<?php echo $action->getIdentity(); ?>"><span><?php echo $this->translate("Unsave Feed");?></span></a></li>
                 <?php }else{ ?>
                  <li><a href="javascript:;" class="save_feed_adv"  data-save="<?php echo $this->translate('Save Feed'); ?>" data-unsave="<?php echo $this->translate('Unsave Feed'); ?>" data-actionid="<?php echo $action->getIdentity(); ?>"><span><?php echo $this->translate("Save Feed");?></span></a></li>
                 <?php } ?>
                  <li class="_sep"></li>
                  <li><a href="<?php echo $action->getHref(); ?>" class="sesadv_feed_link"><span><?php echo $this->translate("Feed Link");?></span></a></li>
                <?php if(!$this->isOnThisDayPage && !empty($_SESSION['sesadvcomment']['sesadvcommentActive'])){ ?>
                 <?php if($this->viewer()->getIdentity() == $action->getSubject()->getIdentity()) {
                    $detailTable = Engine_Api::_()->getDbTable('details', 'sesadvancedactivity');
                    if($detailAction->commentable)
                      $text = $this->translate('Disable Comments');
                    else
                      $text = $this->translate('Enable Comments');
                 ?>
                  <li><a href="javascript:;" class="sesadvcommentable" data-commentable="<?php echo $detailAction->commentable; ?>" data-save="<?php echo  $this->translate('Enable Comments'); ?>"  data-unsave="<?php echo  $this->translate('Disable Comments'); ?>" data-href="sesadvancedactivity/ajax/commentable/action_id/<?php echo $action->getIdentity(); ?>"><span><?php echo $text;?></span></a></li>
                <?php } ?>
               <?php } ?>
               <?php if($this->viewer()->getIdentity() != $action->getSubject()->getIdentity()){ ?>
                  <li><a href="javascript:;" class="sesadv_hide_feed" data-name="<?php echo $action->getSubject()->getTitle(); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-subjectid="<?php echo $action->subject_id; ?>"><span><?php echo $this->translate("Hide Feed");?></span></a></li>
                  <li><a href="javascript:;" class="sesadv_hide_feed_all sesadv_hide_feed_all_<?php echo $action->getIdentity(); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-name="<?php echo $action->getSubject()->getTitle(); ?>"><span><?php echo $this->translate("Hide all by %s",$action->getSubject()->getTitle());?></span></a></li>
                  <?php 
                  	if(empty($settings))
                    $settings = Engine_Api::_()->getApi('settings', 'core');
                  $reportEnable = $settings->getSetting('sesadvancedactivity.reportenable', 1);
                  $reportLink = "report/create/subject/" . $action->getGuid();
                  if($reportEnable): ?>
                    <li>
                      <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $reportLink; ?>')" class="sesadv_report_feed" ><?php echo $this->translate("Report"); ?></a>
                    </li>
                  <?php endif; ?> 
               <?php } ?>
             <?php }else{ ?>
              <li><a href="javascript:;" class="sesadv_reschedule_post" data-value="<?php echo date('d-m-Y H:i:s',strtotime($detailAction->schedule_time)); ?>" data-actionid="<?php echo $action->getIdentity(); ?>"><span><?php echo $this->translate("Reschedule Post");?></span></a></li>
             <?php } ?>
                </ul>
              </div>													
            </div>
          </div>
        <?php } ?>
        <?php // Main Content ?>
          <?php $contentData = $this->getContent($action,array('group_feed'=>$group_feed_id,'sesresource_id'=>$actionDetails['sesresource_id'],'sesresource_type'=>$actionDetails['sesresource_type'])); ?>
          <div class="sesact_feed_header_title <?php echo ( empty($action->getTypeInfo()->is_generated) ? 'feed_item_posted' : 'feed_item_generated' ) ?>">													
          <?php if($this->filterFeed == 'hiddenpost'){ ?>
          	<div class="sesact_feed_options sesact_pulldown_wrapper">
            	<a href="javascript:void(0);" class="allowed_hide_post_sesadv sesadv_tooltip sesact_feed_options_btn" data-src="<?php echo $action->getIdentity() ?>" title="Allowed"><i class="far fa-circle"></i></a>
           	</div>
          <?php } ?>
           <?php echo isset($contentData[0]) ? $contentData[0] : '' ; ?>
              <?php $location = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('activity_action',$action->getIdentity()); ?>
              <?php $members = Engine_Api::_()->getDbTable('tagusers','sesadvancedactivity')->getActionMembers($action->getIdentity()); ?>
              <?php $tagItems = Engine_Api::_()->getDbTable('tagitems','sesadvancedactivity')->getActionItems($action->getIdentity()); ?>
              <?php //Feeling Work ?>
              <?php if(defined('SESFEELINGACTIVITYENABLED')) { ?>
              <?php $feelingposts = Engine_Api::_()->getDbTable('feelingposts','sesadvancedactivity')->getActionFeelingposts($action->getIdentity()); ?>
              <?php if($feelingposts) { ?>
                <?php
                  $feelings = Engine_Api::_()->getItem('sesfeelingactivity_feeling', $feelingposts->feeling_id); 
                  if(empty($feelingposts->feeling_custom)) {
                    if($feelings->type == 1) {
                      $feelingIcon = Engine_Api::_()->getItem('sesfeelingactivity_feelingicon', $feelingposts->feelingicon_id); 
                      $photo = Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '');
                      if($photo) {
                      $photo = $photo->getPhotoUrl();
                    ?>
                    <?php echo $this->translate("is "); ?><img title="<?php echo strtolower($feelingIcon->title); ?>" class="sesfeeling_feeling_icon" src="<?php echo Engine_Api::_()->storage()->get($feelingIcon->feeling_icon, '')->getPhotoUrl(); ?>"> <?php echo strtolower($feelings->title); ?> <?php echo strtolower($feelingIcon->title); 
                    } ?>
                    <?php
                    } else if($feelings->type == 2 && $feelingposts->resource_type && $feelingposts->feelingicon_id) {
                      $resource = Engine_Api::_()->getItem($feelingposts->resource_type, $feelingposts->feelingicon_id);
                      if($resource) {
                      echo $this->translate("is "); ?><img title="<?php echo strtolower($resource->title); ?>" class="sesfeeling_feeling_icon" src="<?php echo Engine_Api::_()->storage()->get($feelings->file_id, '')->getPhotoUrl(); ?>"> <?php echo strtolower($feelings->title); ?> <a href="<?php echo strtolower($resource->getHref()); ?>"><?php echo strtolower($resource->title); ?></a>
                    <?php }
                    }
                  } else { ?>
                    <?php echo $this->translate("is "); ?><img title="<?php echo $feelingposts->feeling_customtext; ?>" class="sesfeeling_feeling_icon" src="<?php echo Engine_Api::_()->storage()->get($feelings->file_id, '')->getPhotoUrl(); ?>"> <?php echo strtolower($feelings->title); ?> <?php echo $feelingposts->feeling_customtext; ?>
                  <?php }
                ?>
              <?php } ?>
              <?php } ?>
              <?php //Feeling Work ?>
              <?php if($itemTotalCount = count($tagItems)){ ?>
                  <?php echo $this->translate("in"); ?> 
                  <?php 
                      foreach($tagItems as $tagItem){
                        $item = Engine_Api::_()->getItem($tagItem['resource_type'],$tagItem['resource_id']);
                        if(!$item)
                          continue;
                   ?>                    
                    <a href="<?php echo $item->getHref(); ?>" class="ses_tooltip" data-src="<?php echo $item->getGuid(); ?>"><?php echo $item->getTitle(); ?></a>
                  <?php 
                      } ?>
              <?php } ?>
              <?php if($memberTotalCount = count($members)){ ?>
                  <?php echo $this->translate("with"); ?> 
                  <?php 
                      $counterMember = 1;
                      foreach($members as $member){
                        $user = Engine_Api::_()->getItem('user',$member['user_id']);
                        if(!$user)
                          continue;
                   ?>                    
                    <?php if($counterMember == 2 && $memberTotalCount == 2){ ?>
                      and
                    <?php }else if($counterMember == 2 && $memberTotalCount > 2){ ?>
                     and
                      <a href="javascript:;" class="sessmoothbox" data-url="sesadvancedactivity/ajax/tag-people/action_id/<?php echo $action->getIdentity(); ?>"><?php echo $this->translate(($memberTotalCount - 1).' others') ?></a>
                    <?php 
                      break;
                    } ?>
                    <a href="<?php echo $user->getHref(); ?>" class="ses_tooltip" data-src="<?php echo $user->getGuid(); ?>"><?php echo $user->getTitle(); ?></a>
                  <?php 
                    $counterMember++;
                      } ?>
              <?php } ?>
              <?php if($location){ ?>
                  <?php echo $this->translate("in"); ?> <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?><a href="<?php echo $this->url(array('resource_id' => $action->getIdentity(),'resource_type'=>$action->getType(),'action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" onClick="openSmoothBoxInUrl(this.href);return false;"><?php echo $location->venue;  ?></a><?php } else { ?><?php echo $location->venue;  ?><?php } ?>
              <?php } ?>
          </div> 
<?php   $icon_type = 'activity_icon_'.$action->type;
        list($attachment) = $actionAttachment;
        if( is_object($attachment) && $action->attachment_count > 0 && $attachment->item ):
          $icon_type .= ' item_icon_'.$attachment->item->getType() . ' ';
        endif;
        //https://github.com/Vaibhav-Agarwal06/sedev/issues/406
        if(!empty($detailAction) && $detailAction->reaction_id){
          $icon_type .= ' item_icon_core_sticker';
        }
?>
          <div class="sesact_feed_header_btm">
          <?php if(empty($fromActivityFeed)){ ?>
          	<i class="sesact_feed_header_btm_icon <?php echo $icon_type ?>"></i>
            <?php } ?>
            <?php 
              if($this->subject() && $this->subject()->getType() == "sespage_page" && Engine_Api::_()->getDbTable('pageroles','sespage')->isAdmin(array('page_id'=>$this->subject()->getIdentity(),'user_id'=>$this->viewer()->getIdentity()))){
              ?>
              <span class="sesbasic_text_light">
               <?php echo  $this->translate("Published by"); ?>&nbsp;<a class="ses_tooltip" data-src="<?php echo $action->getSubject()->getGuid(); ?>" href="<?php $action->getSubject()->getHref() ?>"><?php echo $action->getSubject()->getTitle(); ?></a>
               <span class="sesadv_tooltip" title="<?php echo $this->translate("Only people who manage this Page can see who published this post."); ?>">[?]</span>
              </span>
             <span class="sesbasic_text_light">&middot;</span> 
             <?php 
             }else if($this->subject() && $this->subject()->getType() == "sesgroup_group" && Engine_Api::_()->getDbTable('grouproles','sesgroup')->isAdmin(array('group_id'=>$this->subject()->getIdentity(),'user_id'=>$this->viewer()->getIdentity()))){
              ?>
              <span class="sesbasic_text_light">
               <?php echo  $this->translate("Published by"); ?>&nbsp;<a class="ses_tooltip" data-src="<?php echo $action->getSubject()->getGuid(); ?>" href="<?php $action->getSubject()->getHref() ?>"><?php echo $action->getSubject()->getTitle(); ?></a>
               <span class="sesadv_tooltip" title="<?php echo $this->translate("Only people who manage this Group can see who published this post."); ?>">[?]</span>
              </span>
             <span class="sesbasic_text_light">&middot;</span> 
             <?php 
             }else if($this->subject() && $this->subject()->getType() == "businesses" && Engine_Api::_()->getDbTable('businessroles','sesbusiness')->isAdmin(array('business_id'=>$this->subject()->getIdentity(),'user_id'=>$this->viewer()->getIdentity()))){
              ?>
              <span class="sesbasic_text_light">
               <?php echo  $this->translate("Published by"); ?>&nbsp;<a class="ses_tooltip" data-src="<?php echo $action->getSubject()->getGuid(); ?>" href="<?php $action->getSubject()->getHref() ?>"><?php echo $action->getSubject()->getTitle(); ?></a>
               <span class="sesadv_tooltip" title="<?php echo $this->translate("Only people who manage this Businness can see who published this post."); ?>">[?]</span>
              </span>
              <span class="sesbasic_text_light">&middot;</span> 
             <?php 
             }
             
             
             
            ?>
            <?php if(empty($fromActivityFeed)){ ?>
            <?php echo $this->timestamp($action->getTimeValue()) ?>
            <span class="sesbasic_text_light">&middot;</span> 
            <?php }else{ ?>
              <span class="_txt sesbasic_text_light _sponsored"><?php $dot = "";if($ad->sponsored){ echo $this->translate('Sponsored'); $dot= "&middot;"; } ?><?php echo $ad->featured && !$ad->sponsored ? $dot.$this->translate('Featured') : ""; ?></span>            
            <?php } ?>
            <?php if($action->privacy == 'onlyme'){
                    $classPrivacy = 'sesact_me';
                    $titlePrivacy = 'Only Me';
                  }else if($action->privacy == 'friends'){
                    $classPrivacy = 'sesact_friends';
                    if($action->getSubject()->getIdentity() != $this->viewer()->getIdentity())
                      $titlePrivacy = ucwords($action->getSubject()->getTitle()).'\'s friends';
                    else
                      $titlePrivacy = 'Your\'s friends';
                  }else if($action->privacy == 'networks'){
                    $classPrivacy = 'sesact_network';
                    $titlePrivacy = 'Friends And Networks';
                  }else if(strpos($action->privacy,'network_list') !== false){
                    $classPrivacy = 'sesact_network';
                    $explode = explode(',',$action->privacy);
                    $titlePrivacy = '';
                    $counter = 1;
                    foreach($explode as $ex){
                      $item = Engine_Api::_()->getItem('network',str_replace('network_list_','',$ex));
                      if(!$item)
                        continue;
                      $titlePrivacy = $item->getTitle().', '.$titlePrivacy;
                      $counter++;
                    }
                    $titlePrivacy = rtrim($titlePrivacy,', ');
                    if($counter > 2)
                      $titlePrivacy = 'Multiptle Network ( '.$titlePrivacy.')';
                  }else if(strpos($action->privacy,'members_list') !== false || strpos($action->privacy,'member_list') !== false){
                    $classPrivacy = 'sesact_list';
                    $explode = explode(',',$action->privacy);
                    $titlePrivacy = '';
                    $counter = 1;
                    foreach($explode as $ex){
                      $item = Engine_Api::_()->getItem('user_list',str_replace('member_list_','',$ex));
                      if(!$item)
                        continue;
                      $titlePrivacy = $item->getTitle().', '.$titlePrivacy;
                      $counter++;
                    }
                    $titlePrivacy = rtrim($titlePrivacy,', ');
                    if($counter > 2)
                      $titlePrivacy = 'Multiptle Lists ( '.$titlePrivacy.')';
                  }else{
                    $classPrivacy = 'sesact_public';
                    $titlePrivacy = 'Everyone';
                  }
             ?>
             <?php if(empty($fromActivityFeed)){ ?>
            <span class="sesact_feed_header_pr _user"><i class="sesadv_tooltip sesbasic_text_light <?php echo $classPrivacy; ?>" title="<?php echo $this->translate('Shared with: '); echo $this->translate($titlePrivacy); ?>"></i></span>
          <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesiosapp') && !empty($actionDetails->posting_type) && $actionDetails->posting_type == 1){ ?>
            <span class="sesbasic_text_light">&middot;</span>
            <span class="sesbasic_text_light updatefrom"><?php echo $this->translate('From iPhone'); ?></span>
            <i class="fa fa-mobile sesbasic_text_light updatefrom_icon"></i>
          <?php }else if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesandroidapp') && !empty($actionDetails->posting_type) && $actionDetails->posting_type == 2){ ?>
            <span class="sesbasic_text_light">&middot;</span>
            <span class="sesbasic_text_light updatefrom"><?php echo $this->translate('From Android'); ?></span>
            <i class="fa fa-mobile sesbasic_text_light updatefrom_icon"></i>
          <?php } ?>
          <?php } ?>
						<?php if(strip_tags($action->body) && $islanguageTranslate){ ?>
              <span class="sesact_feed_header_tl_link">
                <a href="javascript:void(0);" onClick="socialSharingPopUp('https://translate.google.com/#auto/<?php echo $languageTranslate; ?>/<?php echo urlencode(strip_tags($action->body)); ?>','Google');return false;"><?php echo $this->translate("Translate"); ?></a>
              </span>	
            <?php } ?> 
          </div>
        </div>
      </div>
      <?php
       //Feed Background Image Work
       if(defined('SESFEEDBGENABLED') && $actionDetails->feedbg_id) { ?>
        <?php 
            $background = Engine_Api::_()->getItem('sesfeedbg_background', $actionDetails->feedbg_id);
            $photo = Engine_Api::_()->storage()->get($background->file_id, '');
            if($photo) {
              $photo = $photo->getPhotoUrl();
            }
        ?>
      <?php } //Feed Background Image Work ?>
      <div class='feed_item_body sesbasic_clearfix <?php if(defined('SESFEEDBGENABLED') && $actionDetails->feedbg_id && $photo && empty($location) && strlen(strip_tags($contentData[1])) <= $sesAdvancedactivitytextlimit) { ?> feed_background_image <?php } ?>' <?php if(defined('SESFEEDBGENABLED') && $actionDetails->feedbg_id && $photo && empty($location) && strlen(strip_tags($contentData[1])) <= $sesAdvancedactivitytextlimit) { ?> style="background-image:url(<?php echo $photo ?>);" <?php } ?>>
        <?php if(!empty($contentData[1])) { ?>
          <span class="sesact_feed_item_bodytext" >
            <?php  
            if(isset($contentData[1])) {
              echo $contentData[1];
            } else {
              echo '';
            } ?>
          </span> 
        <?php } ?>
        <?php
        if($action->type == 'friends') {
          if(isset($group_feed_id)) {
            $groupFeed = explode(',', $group_feed_id); 
            if(count($groupFeed) > 1) {
              $getTotalFeedBySubject = Engine_Api::_()->sesadvancedactivity()->fetchAction($groupFeed, 1);
              foreach($getTotalFeedBySubject as $actionId) {
                $actionSubject = $actionId->getSubject();
                if($actionSubject) { ?>
                  <div class="feed_item_friends_members">
                    <?php if(!$actionSubject->photo_id) { ?>
                      <a href="<?php echo $actionSubject->getHref(); ?>"><img src="application/modules/User/externals/images/nophoto_user_thumb_profile.png"></a>
                    <?php } else { ?>
                      <a href="<?php echo $actionSubject->getHref(); ?>"><img src="<?php echo $actionId->getSubject()->getPhotoUrl(); ?>"></a>
                    <?php } ?>
                    <a class="feed_item_friends_title" href="<?php echo $actionSubject->getHref(); ?>"><span><?php echo $actionId->getSubject()->getTitle(); ?></span></a>
                  </div>
                <?php }
              }
            }
          }
        } 
        ?>
        <?php // Main Content ?>        
        <?php 
         $buysellActive = false;
         $buysellattachment = '';
         $action->intializeAttachmentcount();
       
        if($action->type == 'post_self_buysell' || ($action->attachment_count == 1 && count($actionAttachment) == 1 && $buysellattachment = current($actionAttachment))){
          if($action->type == 'post_self_buysell' || (!empty($buysellattachment->item) && $buysellattachment->item->getType() == 'sesadvancedactivity_buysell')){
            if(empty($buysellattachment)){
              $buysell = $action->getBuySellItem();
            }else{
              $changeAction = $action;
              $buysellAction = $buysellattachment->meta->action_id;
              
              $buysell = Engine_Api::_()->getItem('sesadvancedactivity_buysell',$buysellattachment->meta->id);
              $action = Engine_Api::_()->getItem('sesadvancedactivity_action',$buysell->action_id);  
              $buysellattachment = '';
            }
              if($buysell){
                $locationBuySell = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('sesadvancedactivity_buysell',$buysell->getIdentity()); 
                $buysellActive = true;     
              } ?>
        <?php 
          }
        }
        ?>
        <?php // Attachments 
          $action->intializeAttachmentcount();
        ?>
        <?php $classnumber = $action->attachment_count;
              $attachmentsData =$actionAttachment;
         ?>
        <?php $countAttachment = $attachmentsData ? count($attachmentsData) : 0; 
              $counterAttachment = 0;
              $totalAttachmentAttachInFeed = $action->params;
              $totalAttachmentAttachInFeed = !empty($totalAttachmentAttachInFeed['count']) ? $totalAttachmentAttachInFeed['count'] : $countAttachment;
              $viewMoreText = $totalAttachmentAttachInFeed - $attachmentShowCount;
              $showCountAttachment = $attachmentShowCount - 1;
              if($classnumber > $attachmentShowCount)
                $classnumber = $attachmentShowCount;
        ?>
        <?php 
        if($attachmentsData && count($attachmentsData) == 1) {
          $actionAttachmentLocation = $attachmentsData[0];  
          if($actionAttachmentLocation->item->getType() == 'activity_action') { 
            $locationAttachment = Engine_Api::_()->getDbTable('locations','sesbasic')->getLocationData('activity_action',$actionAttachmentLocation->item->getIdentity());
          }
        }
        ?>
        <?php
        if(!$countAttachment && $location && $googleKey && $action->type != 'post_self_buysell' && !$detailAction->reaction_id  && empty($actionDetails->image_id)){ ?>
        	<div class="feed_item_map">
            <div class="feed_item_map_overlay" onClick="style.pointerEvents='none'"></div>
            <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
              <iframe class="feed_item_map_map" frameborder="0" allowfullscreen="" src="https://www.google.com/maps/embed/v1/place?q=<?php echo $location->venue; ?>&key=<?php echo $googleKey; ?>" style="border:0"></iframe>
          	<?php } ?>
          </div>
        <?php } ?>
       <?php if($buysellActive){ ?>
        <div class="sesact_feed_item_buysell">
          <div class="sesact_feed_item_buysell_title"><?php echo $buysell->title; ?></div>
          <div class="sesact_feed_item_buysell_price"><?php echo Engine_Api::_()->sesadvancedactivity()->getCurrencyPrice($buysell->price,$buysell->currency); ?></div>
          <?php if($locationBuySell){ ?>
            <div class="sesact_feed_item_buysell_location sesbasic_text_light">
            	<i class="fas fa-map-marker-alt"></i>
              <span><?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?><a href="<?php echo $this->url(array('resource_id' => $buysell->getIdentity(),'resource_type'=>$buysell->getType(),'action'=>'get-direction'), 'sesbasic_get_direction', true); ?>" onClick="openSmoothBoxInUrl(this.href);return false;"><?php echo $locationBuySell->venue; ?></a><?php } else { ?><?php echo $locationBuySell->venue; ?><?php } ?></span>
            </div>
          <?php } ?>
          <?php if($buysell->description){ ?>
          	<div class="sesact_feed_item_buysell_des"><?php echo $this->viewMoreActivity($buysell->description); ?></div>
          <?php } ?>
        </div>
      <?php } ?>
        <?php if(!empty($detailAction) && $detailAction->reaction_id && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){ ?> 
          <?php $reaction = Engine_Api::_()->getItem('sesadvancedcomment_emotionfile',$detailAction->reaction_id); ?>
          <?php if($reaction){ ?>
              <div class="feed_item_sticker"><img class="_sesactpinimg" src="<?php echo Engine_Api::_()->storage()->get($reaction->photo_id, '')->getPhotoUrl(); ?>"></div>
          <?php } ?>
        <?php } ?>
        <?php if(defined('SESFEEDGIFENABLED') && isset($actionDetails->image_id)){ ?> 
          <?php $gif = Engine_Api::_()->getItem('sesfeedgif_image',$actionDetails->image_id); ?>
          <?php if($gif) { ?>
            <?php $photo = Engine_Api::_()->storage()->get($gif->file_id, ''); ?>
            <?php if($photo) { ?>
              <div class="feed_item_gif"><img class="_sesactpinimg" src="<?php echo $photo->getPhotoUrl(); ?>"></div>
            <?php } ?>
          <?php } ?>
        <?php } ?>
        <?php if( ($action->getTypeInfo()->attachable || Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesfeelingactivity')) && $action->attachment_count > 0 ): // Attachments ?>
          <?php 
            //Core link image work if width is greater than 250
            $width = '250';
            $attachment = $actionAttachment;
             if (!empty($attachment) && $attachment[0]->item->getType() == "core_link") {
              $attachment = $attachment[0];
                if($attachment->item->photo_id)
                {
                  $photoURL = $attachment->item->getPhotoUrl();
                if(strpos($photoURL,'http') === false){
                  $baseURL =(!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : 'http://';
                  $photoURL = $baseURL. $_SERVER['HTTP_HOST'].$photoURL;
                }
                  if($photoURL){
                    $imageHeightWidthData = getimagesize($photoURL); 
                    $width = isset($imageHeightWidthData[0]) ? $imageHeightWidthData[0] : '250';
                  }
                 }
              }
          ?>
          <?php 
            $imageType = "";
          foreach($actionAttachment as $attachment ){ 
          			$imageType = $attachment->item->getType();
                break;
          
           } ?>
           <div class='<?php if($width > 250): ?> link_attachment_big <?php endif; ?> feed_item_attachments <?php  if($action->type == "post_self_buysell" || strpos($imageType, '_photo') == true ||  strpos($action->type, '_photo') == true): ?> feed_images feed_images_<?php echo $classnumber; ?><?php endif; ?>'>
            <?php if( $action->attachment_count > 0 && count($actionAttachment) > 0 ): ?>
              <?php if( count($actionAttachment) == 1 &&
                      null != ( $richContent = current($actionAttachment)->item->getRichContent()) ): ?>                    
                <?php echo $richContent; ?>
              <?php else: ?>
                <?php foreach( $actionAttachment as $attachment ):
                      if($attachmentShowCount == $counterAttachment)
                        break;
                      
                      if(!empty($attachment->item->link_id))
                        $sesactLinks = Engine_Api::_()->getDbTable('links', 'sesadvancedactivity')->rowExists($attachment->item->link_id);
                ?>
                  <span class='feed_attachment_<?php echo $attachment->meta->type ?><?php if(!empty($sesactLinks) && !empty($sesactLinks->ses_aaf_gif) && $sesactLinks->ses_aaf_gif == 1){ ?> sesact_attachement_gif<?php } ?>'>
                  <?php if( $attachment->meta->mode == 0 ): // Silence ?>
                  <?php elseif( $attachment->meta->mode == 1 ): // Thumb/text/title type actions ?>
                   
                    <div>
                      <?php 
                        if ($attachment->item->getType() == "core_link")
                        {
                          $attribs = Array('target'=>'_blank');
                        }
                        else
                        {
                          $attribs = Array();
                        } 
                      ?>
                      <?php if( $attachment->item->getPhotoUrl() ): ?>
                        <?php if($countAttachment > 1 )
                                $imageType = 'thumb.normalmain';
                               else
                                $imageType = 'thumb.main';
                        ?>
                        <?php //member profile photo feed with cover work
                        if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesusercoverphoto') && $action->type == 'profile_photo_update') { ?>
                          <?php $cover = $action->getSubject()->coverphoto; ?>
                          <?php if($cover) { ?>
                            <?php $memberCover =	Engine_Api::_()->storage()->get($cover, ''); 
                            if($memberCover) { 
                                $memberCover = $memberCover->getPhotoUrl(); ?>
                            <div class="sesact_feed_usercover">
                              <div class="_cover">
                                <img id="sesusercoverphoto_cover_id" src="<?php echo $memberCover; ?>" />
                              </div>
                              <div class="_mainphoto">
                                <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $imageType, $attachment->item->getTitle(), array()),array_merge( $attribs,array())); ?>
                              </div>
                            </div>
                          <?php } } else { ?>
                            <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $imageType, $attachment->item->getTitle(), array('class'=>'_sesactpinimg')),array_merge( $attribs,array())); ?>
                          <?php } ?>
                        <?php } else {  ?>
                          <?php echo $this->htmlLink($attachment->item->getHref(), $this->itemPhoto($attachment->item, $imageType, $attachment->item->getTitle(), array('class'=>'_sesactpinimg')),array_merge( $attribs,array())); ?>
                         <?php } ?>
                      <?php endif; ?>
                      <?php if(!empty($attachment->item) && $attachment->item instanceof Core_Model_Link and (!empty($sesactLinks->ses_aaf_gif) && ($sesactLinks->ses_aaf_gif == 1  || $sesactLinks->ses_aaf_gif == 2)) ){ ?>
                        <?php if($sesactLinks->ses_aaf_gif == 1){ ?>
                        <div class="composer_link_gif_content">
                          <img class="_sesactpinimg" src="<?php echo  $attachment->item->title; ?>" data-original="<?php echo  $attachment->item->description; ?>" data-still="<?php echo  $attachment->item->title; ?>">
                          <a href="javascript:;" class="link_play_activity" title="PLAY"></a>
                       </div>
                       <?php }else{ 
                          $explodeCode = explode('|| IFRAMEDATA',$attachment->item->description);
                       ?>
                       <div class="composer_link_iframe_content sesbasic_clearfix">
                         <div class="composer_link_iframe sesbasic_clearfix">
                          	<?php echo $explodeCode[1]; ?>
                         </div>
                         <div class="composer_link_iframe_content_info sesbasic_clearfix">
                           <div class="feed_item_link_title">
                            <a href="<?php echo  $attachment->item->getHref(); ?>" target="_blank"> <?php echo  $attachment->item->title; ?></a>
                           </div>
                           <div class="feed_item_link_desc">
                             <?php echo  $explodeCode[0]; ?>
                           </div>
                         </div>
                       </div>
                       <?php } ?>
                      <?php }else if($action->type == 'sesadvancedactivity_event_share'){ ?>
                        <?php echo $this->partial('_events.tpl','sesadvancedactivity',array('events'=>$attachment->item,'share'=>false)); ?>
                      <?php }
                      else if($attachment->item->getType() == 'sesadvancedactivity_file'){ ?>
                      <div class="sesact_attachment_file sesbasic_clearfix">
                      	<div class="sesact_attachment_file_img">
                        	<?php 
                          $storage = Engine_Api::_()->getItem('storage_file',$attachment->item->item_id);      
                          $filetype = current(explode('_',Engine_Api::_()->sesadvancedactivity()->file_types($storage->mime_major.'/'.$storage->mime_minor)));
                         ?>
                          <?php if($filetype){ ?>
                            <img src="application/modules/Sesadvancedactivity/externals/images/file-icons/<?php echo $filetype.'.png'; ?>">
                          <?php }else{ ?>
                          <img src="application/modules/Sesadvancedactivity/externals/images/file-icons/default.png">
                          <?php } ?>
                        </div>
                        <div class="sesact_attachment_file_info">
                          <div class='feed_item_link_title'>
                            <?php   echo $storage->name; //$this->htmlLink($attachment->item->getHref(), $storage->name ? $attachment->name : '');
                            ?>
                          </div>
                          <div class="sesact_attachment_file_type sesbasic_text_light"><?php echo ucfirst($filetype); ?></div>
                          <div class='sesact_attachment_file_btns'>
                          <?php if($this->viewer()->getIdentity() != 0){ ?>
                            <a href="<?php echo $this->url(Array('module' => 'sesadvancedactivity', 'controller' => 'index', 'action' => 'download','file_id' => $attachment->item->item_id ), 'default'); ?>" class="sesbasic_button"><span><?php echo $this->translate("Download");?></span></a>
                          <?php } ?>
                          <?php if($filetype == 'image'){ ?>
                            <a href="<?php echo $storage->map(); ?>" class="sesbasic_button sesadvactivity_popup_preview"><span><?php echo $this->translate("Preview");?></span></a>
                         <?php }else if($filetype == 'pdf'){ ?>
                            <a href="<?php echo $storage->map(); ?>" target="_blank" class="sesbasic_button"><span><?php echo $this->translate("Preview");?></span></a>
                         <?php } ?>
                          </div>
                        </div>
                      </div>
                      <?php }else{
                         ?>
                      <div>
                        <div class='feed_item_link_title'>
                          <?php   echo $this->htmlLink($attachment->item->getHref(), $attachment->item->getTitle() ? $attachment->item->getTitle() : '', $attribs);
                          ?>
                        </div>
                        <div class='feed_item_link_desc'>
                          <?php 
                           
                            if($attachment->item->getType() == 'activity_action'){
                             $previousAction = $action;
                             $previousAttachment = $attachment;
                             $action = Engine_Api::_()->getItem('sesadvancedactivity_action',$attachment->item->getIdentity());;
                             include('application/modules/Sesadvancedactivity/views/scripts/_activity.tpl');
                            //echo $this->getContent($attachment->item,array(),false,true);
                             $action = $previousAction;
                             $attachment = $previousAttachment;
                             $previousAction = $previousAttachment = "";
                          }else{ ?>
                          <?php $attachmentDescription = $attachment->item->getDescription();?>
                          <?php if ($action->body != $attachmentDescription): ?>
                            <?php echo $this->viewMoreActivity($attachmentDescription); ?>
                          <?php endif; 
                          }
                          ?>
                        </div>
                        <?php if($attachment->item && $attachment->item->getType() == 'core_link') { ?>
                          <?php $link = Engine_Api::_()->getItem('core_link', $attachment->item->getIdentity());
                          $parse = parse_url($link->uri);
                          ?>
                          <?php if(!empty($parse['host']) && isset($parse['host'])) { ?>
                            <?php $host = (preg_match("#https?://#", $parse['host']) === 0) ? 'http://'.$parse['host'] : $parse['host']; ?>
                            <div class="_link_source"><a href="<?php echo $host; ?>"><?php echo strtoupper($parse['host']); ?></a></div>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <?php 
                      } ?>
                    </div>
                    <?php
                    if(@$locationAttachment && $googleKey && $action->type != 'post_self_buysell' && !$detailAction->reaction_id && empty($actionDetails->image_id)){ ?>
                      <!--<div class="feed_item_map">
                        <div class="feed_item_map_overlay" onClick="style.pointerEvents='none'"></div>
                        <iframe class="feed_item_map_map" frameborder="0" allowfullscreen="" src="https://www.google.com/maps/embed/v1/place?q=<?php echo $locationAttachment->venue; ?>&key=<?php echo $googleKey; ?>" style="border:0"></iframe>
                      </div>-->
                    <?php } ?>
                  <?php elseif( $attachment->meta->mode == 2 ): // Thumb only type actions 
                        if($action->type == 'post_self_buysell'){
                          $imageAttribs = array('data-url'=>'sesadvancedactivity/ajax/feed-buy-sell/action_id/'.$action->getIdentity().'/photo_id/'.$attachment->item->getIdentity().'/main_action/'.(!empty($changeAction) ? $changeAction->getIdentity() : $action->getIdentity()),'class'=>'sessmoothbox');
                          $linkHref = 'javascript:;';
                          $classbuysell ="sesadvancedactivity_buysell";
                          }else{
                          $imageAttribs = array();
                          $classbuysell = '';
                          $linkHref = $attachment->item->getHref();
                          }
                  ?>
                    <?php if($counterAttachment == $showCountAttachment && $viewMoreText > 0){ ?>
                      <?php $imageMoreText = '<p class="_photocounts"><span>+'.$viewMoreText.'</span></p>'; ?>
                    <?php }else{$imageMoreText = '';} ?>
                      <div class="feed_attachment_photo <?php echo $classbuysell; ?>">
                      <?php echo $this->htmlLink($linkHref, $this->itemPhoto($attachment->item, 'thumb.main', $attachment->item->getTitle(), array('class' => '_sesactpinimg')).$imageMoreText, $imageAttribs) ?>
                      </div>
                  <?php elseif( $attachment->meta->mode == 3 ): // Description only type actions ?>
                    <?php echo $this->viewMoreActivity($attachment->item->getDescription()); ?>
                  <?php elseif( $attachment->meta->mode == 4 ): // Multi collectible thingy (@todo) ?>
                  <?php endif; ?>
                  </span>
                <?php 
                $counterAttachment++;
                endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
          </pre>
        <?php endif; ?>
        <?php if($action->type == 'post_self_buysell'){ ?>
        	<div class="sesact_feed_item_buysell_btn">
           <?php if($buysell->buy){ ?>
            <a class="sesbasic_link_btn" href="<?php echo $buysell->buy; ?>" target="_blank" ><i class="fa fa-shopping-cart"></i><?php echo $this->translate("Buy Now"); ?></a>
            <?php } ?>
          <?php if($this->viewer()->getIdentity() != 0){ ?>
           <?php if(!$buysell->is_sold){ ?>
            <?php if($action->subject_id != $this->viewer()->getIdentity()){ ?>
              <button onClick="openSmoothBoxInUrl('sesadvancedactivity/ajax/message/action_id/<?php echo $action->getIdentity(); ?>');return false;"><i class="fa fa-comment"></i>Message Seller</button>
            <?php }else{ ?>
              <button class="mark_as_sold_buysell mark_as_sold_buysell_<?php echo $action->getIdentity(); ?>" data-sold="<?php echo $this->translate('Sold'); ?>" data-href="<?php echo $action->getIdentity(); ?>"><i class="fa fa-check"></i><?php echo $this->translate("Mark as Sold"); ?></button>
            <?php } ?>
           <?php }else{ ?>
              <button><i class="fa fa-check"></i><?php echo $this->translate("Sold"); ?></button>
           <?php } ?>
          <?php } ?>
         </div>
        <?php } ?>
        <?php $getAllHashtags = !is_string($action) ? Engine_Api::_()->getDbTable('hashtags', 'sesadvancedactivity')->getAllHashtags($action->getIdentity()) : 0; ?>
        <?php if(count($getAllHashtags) > 0) { ?>
          <div class="sesact_feed_tags">
            <?php
              $hashTagsString = '';
              foreach($getAllHashtags as $value) {
                if($value == '') continue;
                if(strpos($action->body,$value) === false){}else{
                  $hashTagsString .= '<a target="_blank" href="hashtag?hashtag='.$value.'">#'.ltrim($value).'</a>&nbsp;&nbsp;';
                }
              }
              echo $hashTagsString;
            ?>
          </div>
        <?php } ?>
        <?php if(!empty($changeAction)){
          $action = $changeAction;
          $changeAction = '';
        } ?>
       <?php if(!empty($detailAction) && $detailAction->schedule_time && empty($previousAction)){ ?>
        <div class="sesact_feed_schedule_post_time">
        	<?php echo $this->translate("This post will be publish on"); ?> <b><?php echo date('Y-m-d H:i:s',strtotime($detailAction->schedule_time)); ?></b>.       </div>
        <?php } ?>
      </div>
      <?php $sescommunityads = empty($sescommunityads) ? Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sescommunityads') : $sescommunityads; ?>
      <?php if(empty($fromActivityFeed) && empty($_SESSION['fromActivityFeed']) && !empty($detailAction) && !$detailAction->schedule_time && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost') && empty($previousAction) && $sescommunityads && in_array('boos_post',Engine_Api::_()->sescommunityads()->allowedTypes($action)) && Engine_Api::_()->sescommunityads()->getAllowedActivityType($action->type) && ($action->subject_id == $this->viewer()->getIdentity() && $action->subject_type == "user")){ 
        if(Engine_Api::_()->authorization()->isAllowed('sescommunityads', $this->viewer(), 'create')){
      ?>
      <div class="sesact_boost_post sesbasic_clearfix clear">
        <?php if(!empty($actionDetails->view_count)){ ?>
          <div class="sesact_boost_post_reach sesbasic_clearfix floatL sesbasic_text_light">
            <?php echo $this->translate("%s people Reached",$actionDetails->view_count); ?>
          </div>
        <?php } ?>
          <div class="sesbasic_clearfix sesact_boost_btn floatR">
            <a  href="<?php echo $this->url(array("controller"=> "index", "action" => "create",'action_id'=>$action->action_id),'sescommunityads_general',true); ?>"><?php echo $this->translate('Boost Post'); ?></a>
          </div>
        </div>
      <?php 
        }
      } 
        if(!empty($_SESSION['fromActivityFeed']))
          $_SESSION['fromActivityFeed'] =  "";
      ?>
      
      <?php 
        if(empty($this->ulInclude) && $action->subject_id != $this->viewer()->getIdentity() && $action->subject_type == "user"  && empty($previousAction) && empty($fromActivityFeed) && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost')){
          $actionDetails->view_count++;
          $actionDetails->save();
        } ?>
      
      
      <?php if(!empty($detailAction) && !$detailAction->schedule_time && (empty($this->filterFeed) || $this->filterFeed != 'hiddenpost') && empty($previousAction)){ ?>
      <div class="comment_cnt sesact_comments sesbasic_clearfix" id='comment-likes-activity-item-<?php echo $action->action_id ?>'>
        <?php if(!is_string($action)): ?>
          <?php echo $this->activity($action, array('noList' => true,'isOnThisDayPage'=>$this->isOnThisDayPage,'viewAllLikes'=>$this->viewAllLikes), 'update',$this->viewAllComments); ?>   
        <?php endif; ?>
      </div> <!-- End of Comment Likes -->
      <?php } ?>      
    <?php if( !$this->noList ): ?></section></li><?php endif; ?>
