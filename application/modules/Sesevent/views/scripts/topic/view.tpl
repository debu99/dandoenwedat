<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php  $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_discussion.css'); ?>
<div class="layout_middle">
	<div class="generic_layout_container layout_core_content">
    <div class="layout_page_sesevent_topic_view">
      <div class="generic_layout_container layout_main">
        <div class="generic_layout_container layout_core_content sesbasic_clearfix sesbasic_bxs">
        <h2>
          <?php echo $this->event->__toString();?>
          <?php echo $this->translate('&#187;'); ?>
          <?php echo $this->htmlLink(array(
                'route' => 'sesevent_extended',
                'controller' => 'topic',
                'action' => 'index',
                'event_id' => $this->event->getIdentity(),
              ), $this->translate('Discussions')) ?>
        </h2>
        <br />
        <h3>
          <?php echo $this->topic->getTitle() ?>
        </h3>
    
    <?php $this->placeholder('eventtopicnavi')->captureStart();  ?>
    <div class="sesevent_discussions_thread_options sesbasic_sidebar_block">
      <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'topic', 'action' => 'index', 'event_id' => $this->event->getIdentity()), $this->translate('Back to Topics'), array(
        'class' => 'buttonlink icon_back'
      )) ?>
      <?php if( $this->canPost ): ?>
        <?php echo $this->htmlLink($this->url(array()) . '#reply', $this->translate('Post Reply'), array(
          'class' => 'buttonlink icon_sesevent_post_reply'
        )) ?>
        <?php if( $this->viewer->getIdentity() ): ?>
          <?php if( !$this->isWatching ): ?>
            <?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '1')), $this->translate('Watch Topic'), array(
              'class' => 'buttonlink icon_sesevent_topic_watch'
            )) ?>
          <?php else: ?>
            <?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '0')), $this->translate('Stop Watching Topic'), array(
              'class' => 'buttonlink icon_sesevent_topic_unwatch'
            )) ?>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
      <?php if( $this->canEdit || $this->canAdminEdit ): ?>
        <?php if( !$this->topic->sticky ): ?>
          <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '1', 'reset' => false), $this->translate('Make Sticky'), array(
            'class' => 'buttonlink icon_sesevent_post_stick'
          )) ?>
        <?php else: ?>
          <?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '0', 'reset' => false), $this->translate('Remove Sticky'), array(
            'class' => 'buttonlink icon_sesevent_post_unstick'
          )) ?>
        <?php endif; ?>
        <?php if( !$this->topic->closed ): ?>
          <?php echo $this->htmlLink(array('action' => 'close', 'close' => '1', 'reset' => false), $this->translate('Close'), array(
            'class' => 'buttonlink icon_sesevent_post_close'
          )) ?>
        <?php else: ?>
          <?php echo $this->htmlLink(array('action' => 'close', 'close' => '0', 'reset' => false), $this->translate('Open'), array(
            'class' => 'buttonlink icon_sesevent_post_open'
          )) ?>
        <?php endif; ?>
        <?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array(
          'class' => 'buttonlink smoothbox icon_sesevent_post_rename'
        )) ?>
        <?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array(
          'class' => 'buttonlink smoothbox icon_sesevent_post_delete'
        )) ?>
      <?php elseif( !$this->canEdit ): ?>
        <?php if( $this->topic->closed ): ?>
          <div class="sesevent_discussions_thread_options_closed sesbasic_text_light">
            <?php echo $this->translate('This topic has been closed.')?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php $this->placeholder('eventtopicnavi')->captureEnd(); ?>
    
    
    
    <?php echo $this->placeholder('eventtopicnavi') ?>
    <?php echo $this->paginationControl(null, null, null, array(
      'params' => array(
        'post_id' => null // Remove post id
      )
    )) ?>
    
    
    <script type="text/javascript">
      var quotePost = function(user, href, body) {
        if( $type(body) == 'element' ) {
          body = $(body).getParent('li').getElement('.sesevent_discussions_thread_body_raw').get('html').trim();
        }
        var value ='[blockquote]' + '[b][url=' + href + ']' + user + '[/url] said:[/b]\n' + htmlspecialchars_decode(body) + '[/blockquote]\n\n';
        <?php if ( $this->form && ($this->form->body->getType() === 'Engine_Form_Element_TinyMce') ): ?>
          tinyMCE.activeEditor.execCommand('mceInsertContent', false, value);
          tinyMCE.activeEditor.focus();
        <?php else: ?>
          $('body').value = value;
          $("body").focus();                    
        <?php endif; ?>
        $("body").scrollTo(0, $("body").getScrollSize().y);
      }
      en4.core.runonce.add(function() {
        $$('.sesevent_discussions_thread_body').enableLinks();
      });
    </script>
    
    
    
    <ul class='sesevent_discussions_thread'>
      <?php foreach( $this->paginator as $post ):
        $user = $this->item('user', $post->user_id);
        $isOwner = false;
        $isMember = false;
        $liClass = 'sesevent_discussions_thread_author_none';
        if( $this->event->isOwner($user) ) {
          $isOwner = true;
          $isMember = true;
          $liClass = 'sesevent_discussions_thread_author_isowner';
        } else if( $this->event->membership()->isMember($user) ) {
          $isMember = true;
          $liClass = 'sesevent_discussions_thread_author_ismember';
        }
        ?>
      <li class="<?php echo $liClass ?> sesbasic_clearfix sesbm" id="topc_sesevent_<?php echo $post->getIdentity();?>">
        <div class="sesevent_discussions_thread_author">
          <div class="sesevent_discussions_thread_author_name">
            <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
          </div>
          <div class="sesevent_discussions_thread_photo">
            <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
          </div>
          <div class="sesevent_discussions_thread_author_rank">
            <?php
              if( $isOwner ) {
                echo $this->translate('Host');
              } else if( $isMember ) {
                echo $this->translate('Member');
              }
            ?>
          </div>
        </div>
        <div class="sesevent_discussions_thread_info">
          <div class="sesevent_discussions_thread_details sesbm">
            <div class="sesevent_discussions_thread_details_options">
              <?php if( $this->form ): ?>
                <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Quote'), array(
                  'class' => 'buttonlink icon_sesevent_post_quote',
                  'onclick' => 'quotePost("'.$this->escape($user->getTitle()).'", "'.$this->escape($user->getHref()).'", this);',
                )) ?>
              <?php endif; ?>
              <?php if( $post->user_id == $this->viewer()->getIdentity() ||
                        $this->event->getOwner()->getIdentity() == $this->viewer()->getIdentity() ||
                        $this->canAdminEdit ): ?>
                <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'module' => 'sesevent', 'controller' => 'post', 'action' => 'edit', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Edit'), array(
                  'class' => 'buttonlink smoothbox icon_sesevent_post_edit'
                )) ?>
                <?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'module' => 'sesevent', 'controller' => 'post', 'action' => 'delete', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete'), array(
                  'class' => 'buttonlink smoothbox icon_sesevent_post_delete'
                )) ?>
              <?php endif; ?>
            </div>
            <div class="sesevent_discussions_thread_details_anchor">
              <a href="<?php echo $post->getHref() ?>">
                &nbsp;
              </a>
            </div>
            <div class="sesevent_discussions_thread_details_date sesbaic_text_light">
              <?php echo $this->timestamp(strtotime($post->creation_date)) ?>
              <?php //echo $this->locale()->toDateTime(strtotime($post->creation_date)) ?>
            </div>
          </div>
          <div class="sesevent_discussions_thread_body">
            <?php echo nl2br($this->BBCode($post->body, array('link_no_preparse' => true))) ?>
          </div>
          <span class="sesevent_discussions_thread_body_raw" style="display: none;">
            <?php echo $post->body; ?>
          </span>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    
    
    <?php if($this->paginator->getCurrentItemCount() > 4): ?>
    
      <?php echo $this->paginationControl(null, null, null, array(
        'params' => array(
          'post_id' => null // Remove post id
        )
      )) ?>
      <br />
      <?php echo $this->placeholder('eventtopicnavi') ?>
    
    <?php endif; ?>
    
    <br />
    
    <?php if( $this->form ): ?>
      <a name="reply"></a>
      <?php echo $this->form->setAttrib('id', 'sesevent_topic_reply')->render($this) ?>
    <?php endif; ?>
  
    
    </div>
      </div>
    </div>
  </div>
</div>
<?php if(isset($_GET['last_post'])){ ?>
<script type="application/javascript">
var lastLiElem = sesJqueryObject('.sesevent_discussions_thread').children().length;
if(lastLiElem > 0){
lastLiElem = lastLiElem - 1;
var obj = sesJqueryObject('.sesevent_discussions_thread').children().eq(lastLiElem).attr('id');
obj = sesJqueryObject('#'+obj);
sesJqueryObject('html, body').animate({
							scrollTop: obj.offset().top
						 }, 2000);
}
</script>
<?php } ?>