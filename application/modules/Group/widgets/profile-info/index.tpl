<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9987 2013-03-20 00:58:10Z john $
 * @author		 John
 */
?>

<h3><?php echo $this->translate("Group Info") ?></h3>

<ul>
  <li class="group_stats_title">
    <span>
      <?php echo $this->group->getTitle() ?>
    </span>
    <?php if( !empty($this->group->category_id) &&
    ($category = $this->group->getCategory()) instanceof Core_Model_Item_Abstract &&
    !empty($category->title)): ?>
    <?php echo $this->htmlLink(array('route' => 'group_general', 'action' => 'browse', 'category_id' => $this->group->category_id), $this->translate((string)$category->title)) ?>
    <?php endif; ?>
  </li>
  <?php if( '' !== ($description = Engine_Api::_()->core()->smileyToEmoticons($this->group->description)) ): ?>
  <li class="group_stats_description">
    <?php echo $this->viewMore($description, null, null, null, true) ?>
  </li>
  <?php endif; ?>
  <li class="group_stats_staff">
    <ul>
      <?php foreach( $this->staff as $info ): ?>
      <li>
        <?php echo $info['user']->__toString() ?>
        <?php if( $this->group->isOwner($info['user']) ): ?>
        (<?php echo ( !empty($info['membership']) && $info['membership']->title ? $info['membership']->title : $this->translate('owner') ) ?>)
        <?php else: ?>
        (<?php echo ( !empty($info['membership']) && $info['membership']->title ? $info['membership']->title : $this->translate('officer') ) ?>)
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  </li>
  <li class="group_stats_info">
    <ul>
      <li><i class="far fa-user"></i><?php echo $this->translate(array('%s total view', '%s Total views', $this->group->view_count), $this->locale()->toNumber($this->group->view_count)) ?></li>
      <li><i class="far fa-eye"></i><?php echo $this->translate(array('%s total member', '%s Total members', $this->group->member_count), $this->locale()->toNumber($this->group->member_count)) ?></li>
      <li><i class="far fa-clock"></i><span><?php echo $this->translate('Last updated %s', $this->timestamp($this->group->modified_date)) ?></span></li>
    </ul>
  </li>
</ul>

<script type="text/javascript">
    $$('.core_main_group').getParent().addClass('active');
</script>
