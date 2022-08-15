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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_discussion.css'); ?>
<?php if( $this->viewer()->getIdentity() || $this->paginator->count() > 1 ): ?>
  <div class="sesbasic_profile_tabs_top sesbasic_clearfix">
    <?php if( $this->viewer()->getIdentity() && $this->canTopicCreate):?>
      <?php
      echo $this->htmlLink(array(
          'route' => 'sesevent_extended',
          'controller' => 'topic',
          'action' => 'create',
          'subject' => $this->subject()->getGuid(),
        ), $this->translate('Post New Topic'), array(
          'class' => 'sesbasic_button sesbasic_icon_add'
      ));?>
    <?php endif;?>
    <?php if( $this->paginator->count() > 1 ): ?>
      <?php echo $this->htmlLink(array(
          'route' => 'sesevent_extended',
          'controller' => 'topic',
          'action' => 'index',
          'subject' => $this->subject()->getGuid(),
        ), 'View All '.$this->paginator->getTotalItemCount().' Topics', array(
          'class' => 'buttonlink icon_viewmore'
      )) ?>
    <?php endif; ?>
  </div>
<?php endif;?>


<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <div class="sesevent_discussions_list sesbasic_bxs sesbasic_clearfix">
    <ul class="sesevent_discussions">
      <?php foreach( $this->paginator as $topic ):
        $lastpost = $topic->getLastPost();
        $lastposter = $topic->getLastPoster();
        ?>
        <li>
          <div class="sesevent_discussions_replies sesbm">
            <span>
              <?php echo $this->locale()->toNumber($topic->post_count - 1) ?>
            </span>
            <?php echo $this->translate(array('reply', 'replies', $topic->post_count - 1)) ?>
          </div>
          <div class="sesevent_discussions_lastreply">
            <?php echo $this->htmlLink($lastposter->getHref(), $this->itemPhoto($lastposter, 'thumb.icon')) ?>
            <div class="sesevent_discussions_lastreply_info">
              <?php echo $this->htmlLink($lastpost->getHref().'?last_post=1', $this->translate('Last Post')) ?> <?php echo $this->translate('by');?> <?php echo $lastposter->__toString() ?>
              <br />
              <?php echo $this->timestamp(strtotime($topic->modified_date), array('tag' => 'div', 'class' => 'sesevent_discussions_lastreply_info_date sesbasic_text_light')) ?>
            </div>
          </div>
          <div class="sesevent_discussions_info">
            <h3<?php if( $topic->sticky ): ?> class='sesevent_discussions_sticky'<?php endif; ?>>
              <?php echo $this->htmlLink($topic->getHref(), $topic->getTitle()) ?>
            </h3>
            <div class="sesevent_discussions_blurb">
              <?php echo $this->viewMore($topic->getDescription()) ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('No topics have been posted in this event yet.');?>
    </span>
  </div>
<?php endif; ?>
<script type="application/javascript">
var tabId_pD = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_pD);	
});
</script>