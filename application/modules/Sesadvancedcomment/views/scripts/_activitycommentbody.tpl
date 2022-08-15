<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _activitycommentbody.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $comment = $this->comment; 
     
      $actionBody = $this->action;
      if(!$actionBody)
        return;
      $page = !empty($this->page) ? $this->page : 'zero';
      $isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer();
      $viewmore = !empty($this->viewmore) ? $this->viewmore : false ;
      $canComment =( $actionBody->getTypeInfo()->commentable &&
            $this->viewer()->getIdentity() &&
            Engine_Api::_()->authorization()->isAllowed($actionBody->getCommentableItem(), null, 'comment')
             );
     $islanguageTranslate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.translate', 0);
     $languageTranslate = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.language', 'en');
?>
<?php if(!$viewmore){ ?>
<li id="comment-<?php echo $comment->comment_id ?>" class="sesadvancedcomment_cnt_li">
  <div class="comments_author_photo">
    <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(),
      $this->itemPhoto($this->item($comment->poster_type, $comment->poster_id), 'thumb.icon', $actionBody->getSubject()->getTitle())
    ) ?>
  </div>
  <div class="comments_info">
  	<div class="sesadvcmt_comments_options">
  		<a href="javascript:void(0);" class="sesadvcmt_cmt_hideshow sesadvcmt_comments_options_icon" onclick="showhidecommentsreply('<?php echo $comment->comment_id ?>', '<?php echo $actionBody->getIdentity(); ?>')"><i id="hideshow_<?php echo $comment->comment_id ?>_<?php echo $actionBody->getIdentity(); ?>" class="far fa-minus-square"></i></a>
   <?php if ( $this->viewer()->getIdentity() ): ?>
  		<div class="sesadvcmt_pulldown_wrapper sesact_pulldown_wrapper">
        <a href="javascript:void(0);" class="sesadvcmt_comments_options_icon"><i class="fa fa-angle-down"></i></a>
        <div class="sesadvcmt_pulldown">
          <div class="sesadvcmt_pulldown_cont">
            <ul>
               <?php if($this->viewer()->getIdentity() == $comment->poster_id || Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->viewer()->level_id, 'activity')){ ?>
            <?php if(($this->subject() && method_exists($this->subject(),'canDeleteComment') && $this->subject()->canDeleteComment($this->subject())) || !method_exists($this->subject(),'canDeleteComment')){ ?>
              <li>
                <?php echo $this->htmlLink(array(
                'route'=>'default',
                'module'    => 'sesadvancedactivity',
                'controller'=> 'index',
                'action'    => 'delete',
                'action_id' => $actionBody->action_id,
                'comment_id'=> $comment->comment_id,
                ), $this->translate('Delete'), array('class' => 'sescommentsmoothbox')) ?>
              </li>
              <?php } ?>
             <?php if(empty($comment->gif_id) && empty($comment->emoji_id) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.editenable', 1)){ ?>
             <?php if(($this->subject() && method_exists($this->subject(),'canEditComment') && $this->subject()->canEditComment($this->subject())) || !method_exists($this->subject(),'canEditComment')){ ?>
              <li><?php echo $this->htmlLink(('javascript:;'), $this->translate('Edit'), array('class' => 'sesadvancedcomment_edit')) ?></li>
              <?php } ?>
            <?php } ?>
          <?php } ?>
            <?php if($this->viewer()->getIdentity() != $comment->poster_id){ ?>
              <?php $reportEnable = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.reportenable', 1); ?>
              <?php if($reportEnable) { ?>
                <li>
                  <?php echo $this->htmlLink(Array("module"=> "core", "controller" => "report", "action" => "create", "route" => "default", "subject" => $comment->getGuid()), '<span>'. $this->translate("Report") . '</span>', array('onclick' => "openSmoothBoxInUrl(this.href);return false;" ,"class" => "")); ?>
                </li>
              <?php } ?>
            <?php  } ?>
            </ul>
          </div>
        </div>
      </div>  
   	<?php endif; ?>
   </div> 
   
   <span class='comments_author ses_tooltip' data-src="<?php echo $this->item($comment->poster_type, $comment->poster_id)->getGuid(); ?>">
     <?php echo $this->htmlLink($this->item($comment->poster_type, $comment->poster_id)->getHref(), $this->item($comment->poster_type, $comment->poster_id)->getTitle()); ?>
   </span>
   <?php if(strip_tags($comment->body) && $islanguageTranslate){ ?>
          <a href="javascript:void(0);" class="comments_translate_link floatR" onClick="socialSharingPopUp('https://translate.google.com/#auto/<?php echo $languageTranslate; ?>/<?php echo urlencode(strip_tags($comment->body)); ?>','Google');return false;"><?php echo $this->translate("Translate"); ?></a>
   <?php } ?>
<?php
  echo $this->partial(
          '_activitycommentcontent.tpl',
          'sesadvancedcomment',
          array('comment'=>$comment,'isPageSubject'=>$isPageSubject)
        );    
?>          
 <?php
  echo $this->partial(
          '_activitycommentbodyoptions.tpl',
          'sesadvancedcomment',
          array('comment'=>$comment,'actionBody'=>$actionBody,'canComment'=>$canComment,'isPageSubject'=>$isPageSubject)
        );    
?>
  <div class="comments_reply sesadvcmt_replies sesbasic_clearfix" id="comments_reply_reply_<?php echo $comment->comment_id; ?>_<?php echo $actionBody->getIdentity(); ?>" style="display:block;">
     <ul class="comments_reply_cnt">
   <?php } ?>
        <?php $commentReply = $actionBody->getReply($comment->comment_id,$page); ?>
        <?php if( $commentReply->getCurrentPageNumber() > 1 ): ?>
        <li class="comment_reply_view_more">
          <div> </div>
          <div class="comments_viewall">
             <?php if($comment instanceof Activity_Model_Comment){ $module = 'activity';}else{ $module="core";} ?>
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View more replies'), array(
              'onclick' => 'sesadvancedcommentactivitycommentreply("'.$actionBody->getIdentity().'","'.$comment->getIdentity().'", "'.($commentReply->getCurrentPageNumber() - 1).'",this,"'.$module.'")'
            )) ?>
          </div>
        </li>
      <?php endif; ?>
        <?php foreach($commentReply as $commentreply){ ?>
        <?php
         echo $this->partial(
            '_activitycommentreply.tpl',
            'sesadvancedcomment',
            array('commentreply'=>$commentreply,'action'=>$actionBody,'canComment'=>$canComment,'isPageSubject'=>$this->isPageSubject)
          );                    
        }
        ?>
  <?php if(!$viewmore){ ?>
     </ul>
     <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0){ ?>
     <div class="comment_reply_form" style="display:none;">
      <form class="sesadvancedactivity-comment-form-reply advcomment_form" method="post" style="display:none;">
        <div class="comment_usr_img comments_author_photo">        
        <?php echo $this->itemPhoto($isPageSubject, 'thumb.icon', $isPageSubject->getTitle()); ?>
        </div>
        <?php
          $session = new Zend_Session_Namespace('sesadvcomment');
           $albumenable = $session->albumenable;
           $videoenable = $session->videoenable;
           // $enableattachement = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enableattachement', ''));
            $viewer = Engine_Api::_()->user()->getViewer();
            $enableattachement = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sesadvactivity', $viewer, 'cmtattachement');
        ?>
        <div class="_form_container sesbasic_clearfix">
          <div class="comment_form sesbasic_clearfix">
            <textarea class="body" name="body" cols="45" rows="1" placeholder="Write a reply..."></textarea>
            <div class="_sesadvcmt_post_icons sesbasic_clearfix">
            	<span>
              <?php if($albumenable && Engine_Api::_()->authorization()->isAllowed('album', null, 'create')){ ?>
              	<a href="javascript:;" class="sesadv_tooltip file_comment_select" title="<?php echo $this->translate('Attach 1 or more Photos'); ?>"></a>
              <?php } ?>
                <input type="file" name="Filedata" class="select_file" multiple style="display:none;">
                <input type="hidden" name="emoji_id" class="select_emoji_id" value="0" style="display:none;">
                <input type="hidden" name="gif_id" class="select_gif_id" value="0" style="display:none;">
                <input type="hidden" name="file_id" class="file_id" value="0">
                <input type="hidden" class="file" name="action_id" value="<?php echo $actionBody->getIdentity(); ?>">
                <input type="hidden" class="comment_id" name="comment_id" value="<?php echo $comment->comment_id; ?>">
              </span>
              <?php if($videoenable && Engine_Api::_()->authorization()->isAllowed('video', $viewer, 'create')){ ?>
                <span><a href="javascript:;" class="sesadv_tooltip video_comment_select" title="<?php echo $this->translate('Attach 1 or more Videos'); ?>"></a></span>
              <?php } ?>
              <?php if(in_array('emotions', $enableattachement) || in_array('stickers', $enableattachement)) { ?>
                <span class="sesact_post_tool_i tool_i_emoji">
                  <a href="javascript:;" class="sesadv_tooltip emoji_comment_select" title="<?php echo $this->translate('Post an Emoticon or a Sticker'); ?>"></a>
                </span>
              <?php } ?>
              
              <?php if(defined('SESFEEDGIFENABLED') && in_array('gif', $enableattachement)) { ?>
                <?php $enable = Engine_Api::_()->authorization()->isAllowed('sesfeedgif', null, 'enablecommentgif'); ?>
                <?php if($enable) { ?>
                  <span class="sesact_post_tool_i tool_i_gif">
                    <a href="javascript:;" class="sesadv_tooltip gif_comment_select" title="<?php echo $this->translate('Post GIF'); ?>"></a>
                  </span>
                <?php } ?>
              <?php } ?>
              
              
              <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesemoji') && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesemoji.pluginactivated') && in_array('emojis', $enableattachement)) {
                $enableemojis = Engine_Api::_()->authorization()->isAllowed('sesemoji', null, 'enableemojis');
                $getEmojis = Engine_Api::_()->getDbTable('emojis', 'sesemoji')->getEmojis(array('fetchAll' => 1)); 
                if(count($getEmojis) > 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesemoji.enableemoji', 1) && $enableemojis) { ?>
                  <span class="sesact_post_tool_i tool_i_feelings">
                    <a href="javascript:;" class="sesadv_tooltip feeling_emoji_comment_select" title="<?php echo $this->translate('Post Emojis'); ?>">&nbsp;</a>
                  </span>
                <?php } ?>
              <?php } ?>
            </div>
          </div>
          <div class="uploaded_file" style="display:none;" ></div>
          <button type="submit">Post Reply</button>
        </div>
       </form>
      </div>
      <?php  } ?>
    </div>
	</div>       
</li>
<?php } ?>
