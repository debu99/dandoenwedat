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
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">
    <h2>
      <?php echo $this->event->__toString()." ".$this->translate("&#187; Discussions") ?>
    </h2>
    <div class="sesevent_discussions_options sesbasic_sidebar_block">
      <?php echo $this->htmlLink(array('route' => 'sesevent_profile', 'id' => $this->event->getIdentity()), $this->translate('Back to Event'), array(
        'class' => 'buttonlink icon_back'
      )) ?>
      <?php if ($this->can_post) { echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'topic', 'action' => 'create', 'subject' => $this->event->getGuid()), $this->translate('Post New Topic'), array(
        'class' => 'buttonlink icon_sesevent_post_new'
      )); }?>
    </div>
    <?php if( $this->paginator->count() > 1 ): ?>
      <div>
        <br />
        <?php echo $this->paginationControl($this->paginator) ?>
        <br />
      </div>
    <?php endif; ?>
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
                <?php echo $this->htmlLink($lastpost->getHref(), $this->translate('Last Post')) ?> by <?php echo $lastposter->__toString() ?>
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
    <?php if( $this->paginator->count() > 1 ): ?>
      <div>
        <?php echo $this->paginationControl($this->paginator) ?>
      </div>
    <?php endif; ?>
	</div>
</div>
<script type="text/javascript">
  $$('.core_main_sesevent').getParent().addClass('active');
</script>
