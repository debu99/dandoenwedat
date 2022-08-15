<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: list.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/mention/jquery.mentionsInput.css'); ?>    

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'application/modules/Sesbasic/externals/scripts/mention/underscore-min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl .'application/modules/Sesbasic/externals/scripts/mention/jquery.mentionsInput.js'); ?>
  
<?php $this->headTranslate(array(
  'Are you sure you want to delete this?',
)); ?>
<?php if($this->is_ajax_load){ ?>
  <div id="comment_ajax_load_cnt" class="comment_ajax_load_cnt" style="position:relative;">
  <div class="sesbasic_loading_container" style="display:block;"></div>
<?php } ?>
<?php if(!$this->is_ajax_load){ ?>
<?php $canComment = $this->canComment; ?>
<?php if( !$this->page ): ?>
<div class='sescmt_list_wrapper comment-feed sesbasic_bxs sesbasic_clearfix' id="comments">
      <div class='sesadvcmt_options sesbasic_clearfix'>
        <ul class="sesbasic_clearfix">
          <?php if( $this->viewer()->getIdentity() && $this->canComment ):
            if($likeRow =  $this->subject()->likes()->getLike($this->viewer())){ 
                if($likeRow->getType() == 'activity_like') {
                  $item_activity_like = Engine_Api::_()->getDbTable('activitylikes', 'sesadvancedactivity')->rowExists($likeRow->like_id); 
                } else {
                  $item_activity_like = Engine_Api::_()->getDbTable('corelikes', 'sesadvancedactivity')->rowExists($likeRow->like_id); 
                }
                $like = true;
                if($item_activity_like)
                  $type = $item_activity_like->type;
                else 
                  $type = 1;
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
                        <span  data-text="<?php echo $this->translate($getReaction->title);?>" data-subjectid = "<?php echo  $this->subject()->getIdentity(); ?>" data-sbjecttype = "<?php echo  $this->subject()->getType(); ?>" data-type="<?php echo $getReaction->reaction_id; ?>" class="sesadvancedcommentlike reaction_btn sesadvcmt_hoverbox_btn"><div class="reaction sesadvcmt_hoverbox_btn_icon"> <i class="react"  style="background-image:url(<?php echo Engine_Api::_()->sesadvancedcomment()->likeImage($getReaction->reaction_id);?>)"></i> </div></span>
                        <div class="text">
                          <div><?php echo $this->translate($getReaction->title); ?></div>
                        </div>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <a href="javascript:void(0);" <?php if(!empty($_SESSION["sesfromLightbox"])){ ?> id="sesadvancedcomment_like_action_<?php echo $this->subject()->getIdentity(); ?>" <?php $_SESSION["sesfromLightbox"] = ''; }else{ ?> id="sesadvancedcomment_like_actionrec_<?php echo $this->subject()->getIdentity(); ?>" <?php } ?> data-like="<?php echo $this->translate('SESADVLIKEC') ?>" data-unlike="<?php echo $this->translate('SESADVUNLIKEC') ?>" data-subjectid = "<?php echo  $this->subject()->getIdentity(); ?>" data-sbjecttype = "<?php echo  $this->subject()->getType(); ?>" data-type="1" class="sesadvancedcomment<?php echo $like ? 'unlike _reaction' : 'like' ;  ?>">
                  <i style="background-image:url(<?php echo $imageLike; ?>)"></i>
                  <span><?php echo $this->translate($text);?></span>
                </a> 
              </li>
              <li class="feed_item_option_comment">
              	<a href="javascript:void(0);" id="adv_comment_subject_btn_<?php echo $this->subject()->getIdentity(); ?>" class="sesadvanced_comment_btn">
                	<i></i>
                  <span><?php echo $this->translate('SESADVCOMMENT');?></span>
                </a>
              </li>
          <?php endif; ?>
          <?php //if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.enablencommentupdownvote', 1)){ ?>
            <?php //echo $this->partial('_updownvote.tpl', 'sesadvancedcomment', array('item' => $this->subject(),'isPageSubject'=>$this->viewer())); ?>
          <?php //} ?>
          <?php 
              $params = isset($params) ? $params : "";
              echo $this->partial('_sespage_content.tpl', 'sesadvancedcomment', array('subject' => $this->subject(),'params'=>$params));
              echo $this->partial('_sesgroup_content.tpl', 'sesadvancedcomment', array('subject' => $this->subject(),'params'=>$params));
              echo $this->partial('_sesbusiness_content.tpl', 'sesadvancedcomment', array('subject' => $this->subject(),'params'=>$params));
          ?> 
          
        </ul>
      </div>      
      <div class='comments sesadvcmt_comments' >
          <?php if( $canComment ){ ?>
            <form class="sesadvancedactivity-comment-form advcomment_form" method="post" style="display:<?php echo ( $this->subject()->comments()->getCommentCount() > 0 || Engine_Api::_()->getApi('settings', 'core')->getSetting('sesadvancedcomment.opencommentbox', 1)) ? 'block' : 'none';  ?>">
              <div class="comments_author_photo comment_usr_img">
              <?php
                echo $this->itemPhoto($this->item('user', Engine_Api::_()->user()->getViewer()->getIdentity()), 'thumb.icon', $this->item('user', Engine_Api::_()->user()->getViewer()->getIdentity())->getTitle());
                ?>
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
                  <textarea class="body" name="body" cols="45" rows="1" placeholder="<?php echo $this->translate('Write a comment...'); ?>"></textarea>
                  <div class="_sesadvcmt_post_icons sesbasic_clearfix">
                    <span>
                      <?php if($albumenable && Engine_Api::_()->authorization()->isAllowed('album', null, 'create')){ ?>
                        <a href="javascript:;" class="sesadv_tooltip file_comment_select"  title="<?php echo $this->translate('Attach 1 or more Photos'); ?>"></a>
                      <?php } ?>
                      <input type="file" name="Filedata" class="select_file" multiple value="0" style="display:none;">
                      <input type="hidden" name="emoji_id" class="select_emoji_id" value="0" style="display:none;">
                      <input type="hidden" name="gif_id" class="select_gif_id" value="0" style="display:none;">
                      <input type="hidden" name="file_id" class="file_id" value="0">
                      <input type="hidden" class="file" name="subject_id" value="<?php echo $this->subject()->getIdentity(); ?>">
                      <input type="hidden" class="file_type" name="subject_type" value="<?php echo $this->subject()->getType(); ?>">
                      </span>
                      <?php if($videoenable && Engine_Api::_()->authorization()->isAllowed('video', $viewer, 'create')){ ?>
                        <span><a href="javascript:;" class="sesadv_tooltip video_comment_select" title="<?php echo $this->translate('Attach 1 or more Videos'); ?>"></a></span>
                      <?php } ?>
                      
                      <?php if(in_array('emotions', $enableattachement) || in_array('stickers', $enableattachement)) { ?>
                        <span>
                          <a href="javascript:;" class="sesadv_tooltip emoji_comment_select" title="<?php echo $this->translate('Post an Emoticon or a Sticker'); ?>">&nbsp;</a>
                        </span>
                      <?php } ?>

                      <?php //GIF Work ?>
                      <?php if(defined('SESFEEDGIFENABLED') && in_array('gif', $enableattachement)) { ?>
                        <?php $enable = Engine_Api::_()->authorization()->isAllowed('sesfeedgif', null, 'enablecommentgif'); ?>
                        <?php if($enable) { ?>
                          <span class="sesact_post_tool_i tool_i_gif">
                            <a href="javascript:;" class="sesadv_tooltip gif_comment_select" title="<?php echo $this->translate('Post GIF'); ?>">&nbsp;</a>
                          </span>
                        <?php } ?>
                      <?php } //GIF Work ?>

                      <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesemoji')) {
                        $enableemojis = Engine_Api::_()->authorization()->isAllowed('sesemoji', null, 'enableemojis');
                        $getEmojis = Engine_Api::_()->getDbTable('emojis', 'sesemoji')->getEmojis(array('fetchAll' => 1)); 
                        if(count($getEmojis) > 0 && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesemoji.enableemoji', 1) && $enableemojis && in_array('emojis', $enableattachement)) { ?>
                        <span class="sesact_post_tool_i tool_i_feelings">
                          <a href="javascript:;" class="sesadv_tooltip feeling_emoji_comment_select" title="<?php echo $this->translate('Post Emojis'); ?>">&nbsp;</a>
                        </span>
                      <?php } ?>
                      <?php } ?>
                  </div>
                </div>
                <div class="uploaded_file" style="display:none;"></div>
                <button type="submit"><?php echo $this->translate("Post Comment"); ?></button>
              </div>
              </form>
          <?php } ?>
          <ul class="comments_cnt_ul">
              <?php
                   echo $this->partial(
                      'list-comment/_subjectlikereaction.tpl',
                      'sesadvancedcomment',
                      array('subject'=>$this->subject(),'isPageSubject'=>isset($isPageSubject) ? $isPageSubject : "")
                    );                    
                  ?>
<?php endif; ?>
            <?php if($this->comments->getTotalItemCount() > 0):      
              ?>
              <?php foreach($this->comments as $comment):?>
                <?php
                   echo $this->partial(
                      'list-comment/_subjectcommentbody.tpl',
                      'sesadvancedcomment',
                      array('comment'=>$comment,'subject'=>$this->subject(),'isPageSubject'=>isset($isPageSubject) ? $isPageSubject : "")
                    );                 
                  ?>
              <?php endforeach; ?>
              <?php if($this->comments->count() != 0 && $this->comments->getCurrentPageNumber() < $this->comments->count() ): ?>
              <li class="comment_view_more">
                <div> </div>
                <div class="comments_viewall">
                  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View later comments'), array(
                    'onclick' => 'sesadvancedcommentactivitycomment("'.$this->subject()->getIdentity().'", "'.($this->comments->getCurrentPageNumber() + 1).'",this,"'.$this->subject()->getType().'")'
                  )) ?>
                </div>
              </li>
            <?php endif; ?>
            <?php endif; ?>
  <?php if( !$this->page ): ?>          
          </ul>
        </div>

</div>
    <?php endif; ?>
      <?php } ?>
<?php if($this->is_ajax_load){ ?>
</div>
<?php } ?>

<?php if($this->is_ajax_load){ ?>
  <script type="application/javascript">
      en4.core.runonce.add(function()
      {
      sesJqueryObject.post(en4.core.baseUrl+'sesadvancedcomment/comment/list',{is_ajax_load_req:true,id:<?php echo $this->idtype; ?>,type:"<?php echo $this->type ?>"},function(result){
        sesJqueryObject('.comment_ajax_load_cnt').css('position','');
        sesJqueryObject('.comment_ajax_load_cnt').html(result);
      })
    })
    
  </script>
<?php } ?>
<?php if($this->is_ajax_load_req){ die;} ?>
