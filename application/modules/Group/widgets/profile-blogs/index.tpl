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
    var anchor = $('profile_groups_blogs').getParent();
    $('profile_groups_blogs_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    $('profile_groups_blogs_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    $('profile_groups_blogs_previous').removeEvents('click').addEvent('click', function(){
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

    $('profile_groups_blogs_next').removeEvents('click').addEvent('click', function(){
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
        'route' => 'blog_general',
        'controller' => 'blog',
        'action' => 'create',
        'parent_type'=> 'group',
        'subject_id' => $this->subject()->getIdentity(),
      ), $this->translate('Add Blogs'), array(
        'class' => 'buttonlink icon_group_photo_new'
    )) ?>
  <?php endif; ?>
</div>

<br />

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

  <ul id="profile_groups_blogs" class="blogs_browse">
    <?php foreach( $this->paginator as $blog ): ?>
      <li>
      <div class='blogs_browse_photo'>
        <?php echo $this->htmlLink($blog->getHref(), $this->itemPhoto($blog, '')) ?>
      </div>
      <div class='blogs_browse_info'>
        <p class='blogs_browse_info_title'>
          <?php echo $this->htmlLink($blog->getHref(), $blog->getTitle()) ?>
        </p>
        <p class='blogs_browse_info_date'>
          <?php echo $this->translate('Posted');?> <?php echo $this->timestamp($blog->creation_date) ?>
        </p>
        <p class='blogs_browse_info_blurb'>
          <?php echo $this->string()->truncate($this->string()->stripTags($blog->body),110) ?>
        </p>
      </div>
    </li>
    <?php endforeach;?>
  </ul>

  <div>
    <div id="profile_groups_blogs_previous" class="paginator_previous">
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
        'onclick' => '',
        'class' => 'buttonlink icon_previous'
      )); ?>
    </div>
    <div id="profile_groups_blogs_next" class="paginator_next">
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
        'onclick' => '',
        'class' => 'buttonlink_right icon_next'
      )); ?>
    </div>
  </div>

<?php else: ?>

  <div class="tip">
    <span>
      <?php echo $this->translate('No Blog have been added to this group yet.');?>
    </span>
  </div>

<?php endif; ?>
