<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesalbum
 * @package    Sesalbum
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: all-photos.tpl 2015-06-16 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $randonNumber = 'sesphotolightbox_123'; ?>
<?php if(!$this->is_ajax){ ?>
<div class="ses_ml_overlay"></div>
<div class="ses_ml_photos_panel_wrapper sesbasic_clearfix sesbasic_bxs" id="ses_ml_photos_panel_wrapper">
  <div class="ses_ml_photos_panel_header">
      <span class="photoscount"><?php echo $this->translate("View All Photos")."(".$this->allPhotos->getTotalItemCount().")" ; ?></span>
      <a href="javascript:;" id="close-all-photos" class="photospanel_closebtn">
        <i class="fas fa-times" id="a_btn_btn"></i>
      </a>
  </div>
  <div class="ses_ml_photos_panel_content" style="height:200px;">
      <div id="all-photo-container">
      <div class="ses_media_lightbox_all_photo" id="ses_media_lightbox_all_photo_id">
<?php } ?>
			<?php $limit = $this->limit; 
      			$viewer = Engine_Api::_()->user()->getViewer();
      ?>
      <?php foreach($this->allPhotos as $valuePhoto) {
        if (!$valuePhoto instanceof Sesalbum_Model_Photo && !isset($this->sesplugins)) {
          $valuePhoto = Engine_Api::_()->getItem('album_photo', $valuePhoto->photo_id);
          $photoitem = 'thumb.icon';
        } else if(isset($this->sesplugins)) {
          $photoitem = '';
          $valuePhoto = Engine_Api::_()->getItem($this->childItem, $valuePhoto->{$this->child_item_primary});
        } else
          $photoitem = '';
        $album = $valuePhoto->getParent();
        if(!$album->authorization()->isAllowed($viewer, 'view')) {
          $limit++;
          continue;
        }
      ?>
       		<?php if(!isset($this->sesplugins)) { ?>
          <?php $imageURL = Engine_Api::_()->sesbasic()->getImageViewerHref($valuePhoto,array_merge($this->params,array('limit' => $limit))); ?>
          	<a id="photo-lightbox-id-<?php echo $valuePhoto->photo_id; ?>" onclick="getRequestedAlbumPhotoForImageViewer('<?php echo $valuePhoto->getPhotoUrl(); ?>','<?php echo $imageURL	; ?>')" href="<?php echo Engine_Api::_()->sesalbum()->getHrefPhoto($valuePhoto->getIdentity(),$valuePhoto->album_id); ?>" class="ses-image-viewer ses_ml_photos_panel_photo_thumb">
          <?php } else { ?>
          	<a id="photo-lightbox-id-<?php echo $valuePhoto->getIdentity(); ?>" onclick="openLightBoxForSesPlugins('<?php echo $valuePhoto->getHref(); ?>','<?php echo $valuePhoto->getPhotoUrl(); ?>')" href="<?php echo $valuePhoto->getHref(); ?>" class="ses-image-viewer ses_ml_photos_panel_photo_thumb">
          <?php } ?>
          <span style="background-image:url(<?php echo $valuePhoto->getPhotoUrl($photoitem); ?>);"></span>
         </a>
       <?php 
       	$limit++;
      } ?>
<?php if(!$this->is_ajax){ ?>
       </div>
      </div>
  </div>
</div>
<?php } ?>
<script type="application/javascript">
viewMoreHide_<?php echo $randonNumber; ?>();
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    canPaginateAllPhoto = "<?php echo ($this->allPhotos->count() == 0 ? '0' : ($this->allPhotos->count() == $this->allPhotos->getCurrentPageNumber() ? '0' : '1' ))  ?>";
  }
function <?php echo $randonNumber; ?> (album_id,photo_id){
    (new Request.HTML({
      method: 'post',
      'url': requestPhotoSesalbumURL,
      'data': {
        format: 'html',
        page: <?php echo $this->page + 1; ?>,    
				is_ajax : 1,
				url: window.location.href,
				identity : '<?php echo $randonNumber; ?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById('ses_media_lightbox_all_photo_id').innerHTML = document.getElementById('ses_media_lightbox_all_photo_id').innerHTML + responseHTML;
				sesJqueryObject('#all-photo-container').slimscroll({
					 height: 'auto',
					 alwaysVisible :true,
					 color :'#ffffff',
					 railOpacity :'0.5',
					 disableFadeOut :true,					 
					});
      }
    })).send();
    return false;
  }
</script>