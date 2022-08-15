<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author		 Sami
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = $('profile_groups_polls').getParent();
    $('profile_groups_polls_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('profile_groups_polls_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('profile_groups_polls_previous').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    $('profile_groups_polls_next').removeEvents('click').addEvent('click', function(){
      en4.core.request.send(new Request.HTML({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
</script>

<div class="group_album_options">
  <?php if( $this->canAdd ): ?>
    <?php echo $this->htmlLink(array(
        'route' => 'poll_general',
        'controller' => 'poll',
        'action' => 'create',
        'parent_type'=> 'group',
        'subject_id' => $this->subject()->getIdentity(),
      ), $this->translate('Add Polls'), array(
        'class' => 'buttonlink icon_group_photo_new'
    )) ?>
  <?php endif; ?>
</div>

<br />

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

  <ul class="polls_browse" id="profile_groups_polls">
    <?php foreach( $this->paginator as $poll ): ?>
      <li id="poll-item-<?php echo $poll->poll_id ?>">
      <?php echo $this->htmlLink(
        $poll->getHref(),
        $this->itemPhoto($poll->getOwner(), 'thumb.icon', $poll->getOwner()->getTitle()),
        array('class' => 'polls_browse_photo')
      ) ?>
      <div class="polls_browse_info">
        <h3 class="polls_browse_info_title">
          <?php echo $this->htmlLink($poll->getHref(), $poll->getTitle()) ?>
          <?php if( $poll->closed ): ?>
             <i class="fa fa-lock" alt="<?php echo $this->translate('Closed') ?>"></i>
          <?php endif ?>
        </h3>
        <div class="polls_browse_info_date">
          <?php echo $this->translate('Posted by %s', $this->htmlLink($poll->getOwner(), $poll->getOwner()->getTitle())) ?>
          <?php echo $this->timestamp($poll->creation_date) ?>
          -
          <?php echo $this->translate(array('%s vote', '%s votes', $poll->vote_count), $this->locale()->toNumber($poll->vote_count)) ?>
          -
          <?php echo $this->translate(array('%s view', '%s views', $poll->view_count), $this->locale()->toNumber($poll->view_count)) ?>
        </div>
        <?php if (!empty($poll->description)): ?>
          <div class="polls_browse_info_desc">
            <?php echo $poll->description ?>
          </div>
        <?php endif; ?>
      </div>
    </li>
    <?php endforeach;?>
  </ul>

    <div>
      <div id="profile_groups_polls_previous" class="paginator_previous">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
          'onclick' => '',
          'class' => 'buttonlink icon_previous'
        )); ?>
      </div>
      <div id="profile_groups_polls_next" class="paginator_next">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
          'onclick' => '',
          'class' => 'buttonlink_right icon_next'
        )); ?>
      </div>
    </div>

<?php else: ?>

  <div class="tip">
    <span>
      <?php echo $this->translate('No polls have been added to this group yet.');?>
    </span>
  </div>

<?php endif; ?>
