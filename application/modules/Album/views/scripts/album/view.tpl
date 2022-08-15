<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>

<h2>
  <?php echo $this->translate('%1$s\'s Album: %2$s',
  $this->album->getOwner()->__toString(),
  ( '' != trim($this->album->getTitle()) ? $this->translate($this->album->getTitle()) : '<em>' . $this->translate('Untitled') . '</em>')
  ); ?>
</h2>

<?php if( $this->mine || $this->canEdit ): ?>
<script type="text/javascript">
    var SortablesInstance;

    en4.core.runonce.add(function() {
        $$('.thumbs_nocaptions > li').addClass('sortable');
        SortablesInstance = new Sortables($$('.thumbs_nocaptions'), {
            clone: true,
            constrain: true,
            //handle: 'span',
            onComplete: function(e) {
                var ids = [];
                $$('.thumbs_nocaptions > li').each(function(el) {
                    ids.push(el.get('id').match(/\d+/)[0]);
                });
                //console.log(ids);

                // Send request
                var url = '<?php echo $this->url(array('action' => 'order')) ?>';
                var request = new Request.JSON({
                    'url' : url,
                    'data' : {
                        format : 'json',
                        order : ids
                    }
                });
                request.send();
            }
        });
    });
</script>
<?php endif ?>

<?php if( '' != trim($this->album->getDescription()) ): ?>
<p>
  <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->album->getDescription()); ?>
</p>
<br />
<?php endif ?>

<?php if( $this->mine || $this->canEdit ): ?>
<div class="album_options">
  <?php echo $this->htmlLink(array('route' => 'album_general', 'action' => 'upload', 'album_id' => $this->album->album_id), $this->translate('Add More Photos'), array(
  'class' => 'buttonlink icon_photos_new'
  )) ?>
  <?php echo $this->htmlLink(array('route' => 'album_specific', 'action' => 'editphotos', 'album_id' => $this->album->album_id), $this->translate('Manage Photos'), array(
  'class' => 'buttonlink icon_photos_manage'
  )) ?>
  <?php echo $this->htmlLink(array('route' => 'album_specific', 'action' => 'edit', 'album_id' => $this->album->album_id), $this->translate('Edit Settings'), array(
  'class' => 'buttonlink icon_photos_settings'
  )) ?>
  <?php echo $this->htmlLink(array('route' => 'album_specific', 'action' => 'delete', 'album_id' => $this->album->album_id, 'format' => 'smoothbox'), $this->translate('Delete Album'), array(
  'class' => 'buttonlink smoothbox icon_photos_delete'
  )) ?>
</div>
<?php endif;?>

<div class="layout_middle">
  <ul class="thumbs thumbs_nocaptions grid_wrapper">
    <?php foreach( $this->paginator as $photo ): ?>
    <li id="thumbs-photo-<?php echo $photo->photo_id ?>">
      <a class="thumbs_photo" href="<?php echo $photo->getHref(); ?>">
        <?php echo $this->itemBackgroundPhoto($photo, 'thumb.normal')?>
        <div class="info_stat_grid">
          <?php if( $photo->like_count > 0 ) :?>
          <span>
                <i class="fa fa-thumbs-up"></i>
            <?php echo  $this->locale()->toNumber($photo->like_count) ?>
              </span>
          <?php endif; ?>
          <?php if( $photo->comment_count > 0 ) :?>
          <span>
                <i class="fa fa-comment"></i>
            <?php echo  $this->locale()->toNumber($photo->comment_count) ?>
              </span>
          <?php endif; ?>
          <?php if( $photo->view_count > 0 ) :?>
          <span class="album_view_count">
                <i class="fa fa-eye"></i>
            <?php echo  $this->locale()->toNumber($photo->view_count) ?>
              </span>
          <?php endif; ?>
        </div>
      </a>
    </li>
    <?php endforeach;?>
  </ul>
  <?php if( $this->paginator->count() > 0 ): ?>
  <br />
  <?php echo $this->paginationControl($this->paginator); ?>
  <?php endif; ?>

</div>


<script type="text/javascript">
    $$('.core_main_album').getParent().addClass('active');
</script>
