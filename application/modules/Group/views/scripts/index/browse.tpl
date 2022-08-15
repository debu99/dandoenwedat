<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: browse.tpl 9785 2012-09-25 08:34:18Z pamela $
 * @author	   John
 */
?>

<?php if( count($this->paginator) > 0 ): ?>

<ul class='groups_browse grid_wrapper'>
  <?php foreach( $this->paginator as $group ): ?>
    <li>
      <div class="groups_photo">
        <?php echo $this->htmlLink($group->getHref(), $this->itemBackgroundPhoto($group, 'thumb.profile')) ?>
        <div class="info_stat_grid">
          <?php if( $group->like_count > 0 ) :?>
            <span>
              <i class="fa fa-thumbs-up"></i>
              <?php echo $this->locale()->toNumber($group->like_count) ?>
            </span>
          <?php endif; ?>
          <?php if( $group->comment_count > 0 ) :?>
            <span>
              <i class="fa fa-comment"></i>
              <?php echo $this->locale()->toNumber($group->comment_count) ?>
            </span>
          <?php endif; ?>
          <?php if( $group->view_count > 0 ) :?>
            <span class="views_group">
              <i class="fa fa-eye"></i>
              <?php echo $this->locale()->toNumber($group->view_count) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="groups_options">
      </div>
      <div class="groups_info">
        <div class="groups_members">
          <span><i class="fa fa-user"></i></span>
          <span><?php echo $this->translate(array('%s', '%s', $group->membership()->getMemberCount()),$this->locale()->toNumber($group->membership()->getMemberCount())) ?></span>
        </div>
        <div class="groups_title">
          <h3><?php echo $this->htmlLink($group->getHref(), $group->getTitle()) ?></h3>
          <div class="groups_desc">
            <?php echo $this->viewMore($group->getDescription()) ?>
          </div>
          <?php echo $this->translate('led by');?> <?php echo $this->htmlLink($group->getOwner()->getHref(), $group->getOwner()->getTitle()) ?>
        </div>
      </div>
      <p class="half_border_bottom"></p>
    </li>
  <?php endforeach; ?>
</ul>

<?php elseif( preg_match("/category_id=/", $_SERVER['REQUEST_URI'] )): ?>
<div class="tip">
    <span>
    <?php echo $this->translate('Nobody has created a group with that criteria.');?>
    <?php if( $this->canCreate ): ?>
      <?php echo $this->translate('Why don\'t you %1$screate one%2$s?',
        '<a href="'.$this->url(array('action' => 'create'), 'group_general').'">', '</a>') ?>
    <?php endif; ?>
    </span>
</div>

<?php else: ?>
  <div class="tip">
    <span>
    <?php echo $this->translate('There are no groups yet.') ?>
    <?php if( $this->canCreate): ?>
      <?php echo $this->translate('Why don\'t you %1$screate one%2$s?',
        '<a href="'.$this->url(array('action' => 'create'), 'group_general').'">', '</a>') ?>
    <?php endif; ?>
    </span>
  </div>
<?php endif; ?>

<?php echo $this->paginationControl($this->paginator, null, null, array(
  'query' => $this->formValues
)); ?>


