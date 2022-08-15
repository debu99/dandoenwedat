<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _activitycommentreply.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $commentreply = $this->commentreply; 
      $activitycomments = Engine_Api::_()->getDbTable('activitycomments', 'sesadvancedactivity')->rowExists($commentreply->getIdentity());
      $isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
      $action = $this->action;
      $canComment =( $action->getTypeInfo()->commentable &&
            $this->viewer()->getIdentity() &&
            Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')
             );
      $islanguageTranslate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.translate', 0);
     $languageTranslate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.language', 'en');
?>
<?php if(empty($this->likeOptions)){ ?>
<li id="comment-<?php echo $commentreply->comment_id; ?>">
  <div class="comments_author_photo">
  <?php echo $this->htmlLink($this->item($commentreply->poster_type, $commentreply->poster_id)->getHref(),
    $this->itemPhoto($this->item($commentreply->poster_type, $commentreply->poster_id), 'thumb.icon', $action->getSubject()->getTitle())
  ) ?>
  </div>
  <div class="comments_reply_info comments_info">
  	<div class="sesadvcmt_comments_options">
      <a href="javascript:void(0);" class="sesadvcmt_cmt_hideshow sesadvcmt_comments_options_icon" onclick="showhidecommentsreply('<?php echo $commentreply->comment_id ?>', '<?php echo $action->getIdentity(); ?>')"><i id="hideshow_<?php echo $commentreply->comment_id ?>_<?php echo $action->getIdentity(); ?>" class="far fa-minus-square"></i></a>
       <?php if ( $this->viewer()->getIdentity() ): ?>
      <div class="sesadvcmt_pulldown_wrapper sesact_pulldown_wrapper">
        <a href="javascript:void(0);" class="sesadvcmt_comments_options_icon"><i class="fa fa-angle-down"></i></a>
        <div class="sesadvcmt_pulldown">
          <div class="sesadvcmt_pulldown_cont">
            <ul>
              <?php if($this->viewer()->getIdentity() == $commentreply->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')){ ?>
              <?php if(($this->subject() && method_exists($this->subject(),'canDeleteComment') && $this->subject()->canDeleteComment($this->subject())) ||  !method_exists($this->subject(),'canDeleteComment')){ ?>
              <li>
               <?php echo $this->htmlLink(array(
                    'route'=>'default',
                    'module'    => 'sesadvancedactivity',
                    'controller'=> 'index',
                    'action'    => 'delete',
                    'action_id' => $action->action_id,
                    'comment_id'=> $commentreply->comment_id,
                    ), $this->translate('Delete'), array('class' => 'sescommentsmoothbox')) ?>
              </li>
              <?php } ?>
              <?php if(empty($commentreply->gif_id) && empty($commentreply->emoji_id) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.editenable', 1)){ ?>
               <?php if(($this->subject() && method_exists($this->subject(),'canEditComment') && $this->subject()->canEditComment($this->subject())) ||  !method_exists($this->subject(),'canEditComment')){ ?>
                <li><?php echo $this->htmlLink(('javascript:;'), $this->translate('Edit'), array('class' => 'sesadvancedcomment_reply_edit')) ?></li>
                <?php } ?>
              <?php } ?>
               <?php } ?>
            <?php if($this->viewer()->getIdentity() != $commentreply->poster_id){ ?>
              <?php $reportEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.reportenable', 1); ?>
              <?php if($reportEnable) { ?>
                <li>
                  <?php echo $this->htmlLink(Array("module"=> "core", "controller" => "report", "action" => "create", "route" => "default", "subject" => $commentreply->getGuid()), '<span>'. $this->translate("Report") . '</span>', array('onclick' => "openSmoothBoxInUrl(this.href);return false;" ,"class" => "")); ?>
                </li>
              <?php } ?>
            <?php  } ?>
            </ul>
          </div>
        </div>
      </div>
   	<?php endif; ?>
   </div> 
  
   <span class='comments_reply_author comments_author ses_tooltip' data-src="<?php echo $this->item($commentreply->poster_type, $commentreply->poster_id)->getGuid(); ?>">
     <?php echo $this->htmlLink($this->item($commentreply->poster_type, $commentreply->poster_id)->getHref(), $this->item($commentreply->poster_type, $commentreply->poster_id)->getTitle()); ?>
   </span>
    <?php if(strip_tags($commentreply->body) && $islanguageTranslate){ ?>
      <a href="javascript:void(0);" class="comments_translate_link floatR" onClick="socialSharingPopUp('https://translate.google.com/#auto/<?php echo $languageTranslate; ?>/<?php echo urlencode(strip_tags($commentreply->body)); ?>','Google');return false;"><?php echo $this->translate("Translate"); ?></a>
    <?php } ?>
    <?php
        echo $this->partial(
          '_activitycommentreplycontent.tpl',
          'sesadvancedcomment',
          array('commentreply'=>$commentreply,'isPageSubject'=>$isPageSubject)
        );    
?>    
 <?php } ?>
   <ul class="comments_reply_date comments_date" id="comments_reply_<?php echo $commentreply->comment_id; ?>_<?php echo $action->getIdentity(); ?>" style="display:block;">
                  
    <?php if( $canComment ): ?>
    <?php $isLiked = $commentreply->likes()->isLike($isPageSubject); ?>
    <?php if( $this->viewer()->getIdentity() && $this->canComment ):
      if($likeRow =  $commentreply->likes()->getLike($isPageSubject)){
          if($likeRow->getType() == 'activity_like') {
            $item_activity_like = Engine_Api::_()->getDbTable('activitylikes', 'sesadvancedactivity')->rowExists($likeRow->like_id); 
          } else {
            $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($likeRow->like_id); 
          }
          $like = true;
          if($item_activity_like)
            $type = $item_activity_like->type;
          else 
            $type = 1;;
          $imageLike = Engine_Api::_()->sesadvancedcomment()->likeImage($type);
          $text = Engine_Api::_()->sesadvancedcomment()->likeWord($type);
       }else{
          $like = false;
          $type = '';
          $imageLike = '';
          $text = 'SESADVLIKE';
       }
       ?>
        <li class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.reactionenable', 1)):?> sesadvcmt_hoverbox_wrapper <?php endif; ?>">
          <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'sesadvancedcomment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
          <?php if(count($getReactions) > 0): ?>
            <div class="sesadvcmt_hoverbox">
              <?php foreach($getReactions as $getReaction): ?>
                <span>
                  <span  data-text="<?php echo $this->translate($getReaction->title);?>" data-actionid="<?php echo  $action->getIdentity(); ?>" data-commentid = "<?php echo  $commentreply->getIdentity(); ?>"  data-type="<?php echo $getReaction->reaction_id; ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>"  class="sesadvancedcommentcommentlike reaction_btn sesadvcmt_hoverbox_btn"><div class="reaction sesadvcmt_hoverbox_btn_icon"> <i class="react"  style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($getReaction->reaction_id);?>)"></i> </div></span>
                  <div class="text">
                    <div><?php echo $this->translate($getReaction->title); ?></div>
                  </div>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <a href="javascript:void(0);" <?php if(!empty($_SESSION["sesfromLightbox"])){ ?> id="sesadvancedcomment_like_action_<?php echo $commentreply->getIdentity(); ?>" <?php $_SESSION["sesfromLightbox"] = ''; }else{ ?> id="sesadvancedcomment_like_actionrec_<?php echo $commentreply->getIdentity(); ?>" <?php } ?> data-like="<?php echo $this->translate('SESADVLIKEC') ?>" data-unlike="<?php echo $this->translate('SESADVUNLIKEC') ?>" data-actionid="<?php echo  $action->getIdentity(); ?>" data-commentid = "<?php echo  $commentreply->getIdentity(); ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>" data-type="1" class="sesadvancedcommentcomment<?php echo $like ? 'unlike _reaction' : 'like' ;  ?>">
            <i style="background-image:url(<?php echo $imageLike; ?>)"></i>
            <span><?php echo $this->translate($text);?></span>
          </a> 
        </li>
    <?php endif; ?>
    <li class="sep">&middot;</li> 
  <?php endif ?>
          
                         
      <?php if( $commentreply->likes()->getLikeCount() > 0 ): ?>
    <?php $likesGroup = Engine_Api::_()->sesadvancedcomment()->commentLikesGroup($commentreply,false); 
      if(count($likesGroup['data'])){ 
    ?>
    <li class="comments_likes_total">
       <span class="comments_likes_reactions">
       <?php foreach($likesGroup['data'] as $type){ ?>
        <a title="<?php echo $this->translate('%s (%s)',$type['counts'],Engine_Api::_()->sesadvancedcomment()->likeWord($type['type'])) ?>" href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $commentreply->getIdentity(), 'id' => $action->getIdentity(),'resource_type'=>$action->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><i style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($type['type']);?>);"></i></a>
        <?php } ?>
      </span>
        <a href="javascript:;" class="sessmoothbox" data-url="<?php echo $this->url(array('module' => 'sesadvancedactivity', 'controller' => 'ajax', 'action' => 'comment-likes', 'comment_id' => $commentreply->getIdentity(), 'id' => $action->getIdentity(),'resource_type'=>$action->getType(), 'format' => 'smoothbox'), 'default', true); ?>"><?php echo $commentreply->likes()->getLikeCount(); ?></a>
    </li>
    <li class="sep">&middot;</li>
    <?php } ?>
  <?php endif ?>
      
      <?php if(empty($_SESSION['fromActivityFeed']) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablencommentupdownvote', 1)){ ?>
      <?php echo $this->partial('_updownvote.tpl', 'sesadvancedcomment', array('item' => $commentreply,'isPageSubject'=>$this->isPageSubject)); ?>
    	<li class="sep">&middot;</li>
    <?php } ?>
      
      
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablenestedcomments', 1)): ?>
        <li class="comments_reply_btn">
        	<?php echo $this->htmlLink('javascript:;', $this->translate('SESADVREPLY'), array('class' => 'sesadvancedcommentreplyreply')) ?>
        </li>
        <li class="sep">&middot;</li>
      <?php endif; ?>
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablemessagesellpost', 1) && $this->viewer()->getIdentity() && $commentreply->poster_id != $this->viewer()->getIdentity() && $action->type == "post_self_buysell"): ?>
      <li class="comments_reply">
        <?php echo $this->htmlLink($this->url(array('owner_id' =>$commentreply->poster_id,'action'=>'contact','controller'=>'index','module'=>'sesadvancedcomment'), 'default', true), $this->translate('SESADVMESSAGE'), array('class' => 'sessmoothbox')) ?>
      </li>
      <li class="sep">&middot;</li>
    <?php endif; ?>
      <?php if ( $this->viewer()->getIdentity() &&
               (('user' == $action->subject_type && $this->viewer()->getIdentity() == $action->subject_id) ||
                ($this->viewer()->getIdentity() == $commentreply->poster_id) ||
                Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')  ) ): ?>
      <?php if($activitycomments->preview && empty($activitycomments->showpreview)) { ?>
        <li id="remove_preview_<?php echo $commentreply->comment_id ?>">
          <a  href="javascript:void(0);" onclick="removePreview('<?php echo $commentreply->comment_id; ?>', '<?php echo $commentreply->getType(); ?>')">
            <?php echo $this->translate("Remove Preview"); ?>
          </a>
        </li>
        <li id="remove_previewli_<?php echo $commentreply->comment_id ?>" class="sep">&middot;</li>
      <?php } endif; ?>
      
       <li class="comments_reply_timestamp">
         <?php echo $this->timestamp($commentreply->creation_date); ?>
       </li>       
    </ul>
 <?php if(empty($this->likeOptions)){ ?>
  </div>
</li>
<?php } ?>
