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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/SesLightbox/photoswipe.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.flex-images.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/SesLightbox/photoswipe-ui-default.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/flexcroll.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/SesLightbox/lightbox.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/photoswipe.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_album.css'); ?>
<?php
if(!$this->is_ajax && isset($this->docActive)){
	$imageURL = $this->album->getPhotoUrl();
	if(strpos($this->album->getPhotoUrl(),'http') === false)
          	$imageURL = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://". $_SERVER['HTTP_HOST'].$this->album->getPhotoUrl();
  $this->doctype('XHTML1_RDFA');
  $this->headMeta()->setProperty('og:title', strip_tags($this->album->getTitle()));
  $this->headMeta()->setProperty('og:description', strip_tags($this->album->getDescription()));
  $this->headMeta()->setProperty('og:image',$imageURL);
  $this->headMeta()->setProperty('twitter:title', strip_tags($this->album->getTitle()));
  $this->headMeta()->setProperty('twitter:description', strip_tags($this->album->getDescription()));
}
 if(isset($this->identityForWidget) && !empty($this->identityForWidget)){
    $randonNumber = $this->identityForWidget;
 }else{
    $randonNumber = $this->identity; 
 } ?>
 <?php 
if(isset($this->canEdit)){
// First, include the Webcam.js JavaScript Library 
  $base_url = $this->layout()->staticBaseUrl;
  $this->headScript()->appendFile($base_url . 'application/modules/Sesbasic/externals/scripts/webcam.js'); 
  }
?>
<?php 
            $editItem = true;
            if($this->canEditMemberLevelPermission == 1){
              if($this->viewer->getIdentity() == $this->album->owner_id){
                $editItem = true;
              }else{
                $editItem = false;
              }
            }else if($this->canEditMemberLevelPermission == 2){
               $editItem = true;
            }else{
                $editItem = false;
            } 
            $deleteItem = true;
            if($this->canDeleteMemberLevelPermission == 1){
              if($this->viewer->getIdentity() == $this->album->owner_id){
                $deleteItem = true;
              }else{
                $deleteItem = false;
              }
            }else if($this->canDeleteMemberLevelPermission == 2){
               $deleteItem = true;
            }else{
                $deleteItem = false;
            }
             $createItem = true;
            if($this->canCreateMemberLevelPermission == 1){
              if($this->viewer->getIdentity() == $this->album->owner_id){
                $createItem = true;
              }else{
                $createItem = false;
              }
            }else{
                $createItem = false;
            }
          ?>
<?php
 if(!$this->is_ajax){
  $this->headTranslate(array(
    'Save', 'Cancel', 'delete',
  ));
?>
<script type="text/javascript">
sesJqueryObject(document).click(function(event){
	if(event.target.id != 'sesevent_dropdown_btn' && event.target.id != 'a_btn' && event.target.id != 'i_btn'){
		sesJqueryObject('#sesevent_dropdown_btn').find('.sesevent_album_option_box1').css('display','none');
		sesJqueryObject('#a_btn').removeClass('active');
	}
	if(event.target.id == 'change_cover_txt' || event.target.id == 'cover_change_btn_i' || event.target.id == 'cover_change_btn'){
		if(sesJqueryObject('#sesevent_album_change_cover_op').hasClass('active'))
			sesJqueryObject('#sesevent_album_change_cover_op').removeClass('active')
		else
			sesJqueryObject('#sesevent_album_change_cover_op').addClass('active')
	}else{
			sesJqueryObject('#sesevent_album_change_cover_op').removeClass('active')
	}
	if(event.target.id == 'a_btn'){
			if(sesJqueryObject('#a_btn').hasClass('active')){
				sesJqueryObject('#a_btn').removeClass('active');
				sesJqueryObject('.sesevent_album_option_box1').css('display','none');
			}
			else{
				sesJqueryObject('#a_btn').addClass('active');
				sesJqueryObject('.sesevent_album_option_box1').css('display','block');
			}
		}else if(event.target.id == 'i_btn'){
			if(sesJqueryObject('#a_btn').hasClass('active')){
				sesJqueryObject('#a_btn').removeClass('active');
				sesJqueryObject('.sesevent_album_option_box1').css('display','none');
			}
			else{
				sesJqueryObject('#a_btn').addClass('active');
				sesJqueryObject('.sesevent_album_option_box1').css('display','block');
			}
	}	
});
</script>
<div class="sesevent_album_cover_container sesbasic_bxs">
  <?php if(isset($this->album->art_cover) && $this->album->art_cover != 0 && $this->album->art_cover != ''){ 
  			 $albumArtCover =	Engine_Api::_()->storage()->get($this->album->art_cover, '')->getPhotoUrl(); 
   }else
   		$albumArtCover =''; 
?>
  <div id="sesevent_album_cover_default" class="sesevent_album_cover_thumbs" style="display:<?php echo $albumArtCover == '' ? 'block' : 'none'; ?>;">
  <ul>
  <?php
     $albumImage = Engine_Api::_()->sesevent()->getAlbumPhoto($this->album->getIdentity(),0,3); 
     $countTotal = count($albumImage);
  	 foreach( $albumImage as $photo ){
     		 $imageURL = $photo->getPhotoUrl('thumb.normalmain');
          if(strpos($imageURL,'http') === false){
          	$http_s = (!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://";
          	$imageURL = $http_s.$_SERVER['HTTP_HOST'].$imageURL;
           }
           $widthPer = $countTotal == 3 ? "33.33" : ($countTotal == 2 ? "50" : '100') ; ?> 
         		<li style="height:300px;width:<?php echo $widthPer; ?>%">
                <span style="background-image:url(<?php echo $imageURL; ?>);"></span> 
           	</li>
 		<?php } ?>
 </ul>
 </div>
	<span class="sesevent_album_cover_image" id="cover_art_work_image" style="background-image:url(<?php echo $albumArtCover; ?>);"></span>
 <div style="display:none;" id="sesevent-pos-btn" class="sesevent_album_cover_positions_btns">
  	<a id="saveCoverPosition" href="javascript:;" class="sesbasic_button"><?php echo $this->translate("Save");?></a>
    <a href="javascript:;" id="cancelCoverPosition" class="sesbasic_button"><?php echo $this->translate("Cancel");?></a>
  </div>
  <span class="sesevent_album_cover_fade"></span>
  <?php if( $this->mine || $this->canEdit || $editItem): ?>
    <div class="sesevent_album_coverphoto_op" id="sesevent_album_change_cover_op">
      <a href="javascript:;" id="cover_change_btn"><i class="fa fa-camera" id="cover_change_btn_i"></i><span id="change_cover_txt"><?php echo $this->translate("Upload Cover Photo"); ?></span></a>
      <div class="sesevent_album_coverphoto_op_box sesbasic_option_box">
      	<i class="sesevent_album_coverphoto_op_box_arrow"></i>
        <?php if($this->canEdit){ ?>
          <input type="file" id="uploadFilesesevent" name="art_cover" onchange="uploadCoverArt(this);"  style="display:none" />
          <a id="uploadWebCamPhoto" href="javascript:;"><i class="fa fa-camera"></i><?php echo $this->translate("Take Photo"); ?></a>
          <a id="coverChangesesevent" data-src="<?php echo $this->album->art_cover; ?>" href="javascript:;"><i class="fa fa-plus"></i><?php echo (isset($this->album->art_cover) && $this->album->art_cover != 0 && $this->album->art_cover != '') ? $this->translate('Change Cover Photo') : $this->translate('Add Cover Photo');; ?></a>
           <a id="coverRemovesesevent" style="display:<?php echo (isset($this->album->art_cover) && $this->album->art_cover != 0 && $this->album->art_cover != '') ? 'block' : 'none' ; ?>;" data-src="<?php echo $this->album->art_cover; ?>" href="javascript:;"><i class="fa fa-trash"></i><?php echo $this->translate('Remove Cover Photo'); ?></a>
        <?php } ?>
      </div>
    </div>
  <?php endif;?>
	<div class="sesevent_album_cover_inner">
  	<div class="sesevent_album_cover_album_cont sesbasic_clearfix">
			<div class="sesevent_album_cover_album_cont_inner">
      	<div class="sesevent_album_cover_album_owner_photo">
        	<?php $coverAlbumPhoto = $this->album->getPhotoUrl('thumb.profile','status'); 
          		if($coverAlbumPhoto == ''){
               echo $this->itemPhoto($this->albumUser, 'thumb.profile');
             	}else{
               $photoCover = Engine_Api::_()->getItem('sesevent_photo',$this->album->photo_id);
               $imageURL = $photoCover->getHref(); ?>
              <a class="ses-image-viewer" onclick="openLightBoxForSesPlugins('<?php echo $imageURL	; ?>','<?php echo $photoCover->getPhotoUrl(); ?>')" href="<?php echo $imageURL ?>"> 
          		<img src="<?php echo $coverAlbumPhoto; ?>" />	
              </a>
            <?php } ?>
        </div>
        <div class="sesevent_album_cover_album_info">
          <h2 class="sesevent_album_cover_title">
          	<?php echo trim($this->album->getTitle()) ? $this->album->getTitle() : '<em>' . $this->translate('Untitled') . '</em>'; ?>
          </h2>
          <div class="sesevent_album_cover_date clear sesbasic_clearfix">
            <?php  $event = Engine_Api::_()->getItem('sesevent_event',$this->album->event_id); ?>
           <?php echo  $this->translate('in').' <a href="'.$event->getHref().'"> '.$event->getTitle().'</a>'; ?><br />
          	<?php echo  $this->translate('by').' '.$this->albumUser->__toString(); ?>&nbsp;&nbsp;|&nbsp;&nbsp;<?php echo $this->translate('Added %1$s', $this->timestamp($this->album->creation_date)); ?>           
          </div>
          <div class="clear sesbasic_clearfix sesevent_album_cover_album_info_btm">
            <div class="sesevent_album_cover_stats">
            	<div title="<?php echo $this->translate(array('%s photo', '%s photos', $this->album->count()), $this->locale()->toNumber($this->album->count()))?>">
              	<span class="sesevent_album_cover_stat_count"><?php echo $this->album->count(); ?></span>
              	<span class="sesevent_album_cover_stat_txt"><?php echo str_replace(',','',preg_replace('/[0-9]+/', '', $this->translate(array('%s Photo', '%s Photos', $this->album->count()), $this->locale()->toNumber($this->album->count())))); ?></span>
            	</div>
            	<div title="<?php echo $this->translate(array('%s view', '%s views', $this->album->view_count), $this->locale()->toNumber($this->album->view_count))?>">
              	<span class="sesevent_album_cover_stat_count"><?php echo $this->album->view_count; ?></span>
              	<span class="sesevent_album_cover_stat_txt"><?php echo str_replace(',','',preg_replace('/[0-9]+/','',$this->translate(array('%s View', '%s Views', $this->album->view_count), $this->locale()->toNumber($this->album->view_count)))); ?></span>
            	</div>
            	<div title="<?php echo $this->translate(array('%s like', '%s likes', $this->album->like_count), $this->locale()->toNumber($this->album->like_count))?>">
              	<span class="sesevent_album_cover_stat_count"><?php echo $this->album->like_count; ?></span>
              	<span class="sesevent_album_cover_stat_txt"><?php echo str_replace(',','',preg_replace('/[0-9]+/', '', $this->translate(array('%s Like', '%s Likes', $this->album->like_count), $this->locale()->toNumber($this->album->like_count)))); ?></span>
            	</div>
            	<div title="<?php echo $this->translate(array('%s comment', '%s comments',$this->album->comment_count), $this->locale()->toNumber($this->album->comment_count))?>">
              	<span class="sesevent_album_cover_stat_count"><?php echo $this->album->comment_count; ?></span>
              	<span class="sesevent_album_cover_stat_txt"><?php echo str_replace(',','',preg_replace('/[0-9]+/', '',  $this->translate(array('%s Comment', '%s Comments',$this->album->comment_count), $this->locale()->toNumber($this->album->comment_count)))); ?></span>
            	</div>
            </div>
          </div>
				</div>          
      </div>
    </div>
    <div class="sesevent_album_cover_footer clear sesbasic_clearfix">
      <ul id="tab_links_cover" class="sesevent_album_cover_tabs sesbasic_clearfix">
        <li data-src="album-info" class="tab_cover"><a href="javascript:;" ><?php echo $this->translate("Album Info") ; ?></a></li>
        <li class="<?php echo $this->paginator->getTotalItemCount() == 0  ? '' : "sesevent_album_cover_tabactive" ; ?> tab_cover" data-src="album-photo" style="display:<?php echo $this->paginator->getTotalItemCount() == 0  ? '' : "" ; ?>"><a href="javascript:;"><?php echo $this->translate("Photos") ; ?></a></li>
        <li class="tab_cover" data-src="album-discussion" ><a href="javascript:;"><?php echo $this->translate("Discussion") ; ?></a></li>
      </ul>
      <?php
         $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $this->album->getHref()); ?>
      <div class="sesevent_album_cover_user_options sesbasic_clearfix">
          <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $this->album, 'param' => 'photoviewpage')); ?>
          

          <?php if( $this->mine || $this->canEdit || $editItem || $deleteItem || $createItem): ?>
            <span class="sesevent_album_cover_user_options_drop_btn" id="sesevent_dropdown_btn">
              <a title="<?php echo $this->translate('Options'); ?>" href="javascript:;" id="a_btn">
                <i class="fa fa-ellipsis-v" id="i_btn"></i>
              </a>
              <div class="sesbasic_option_box sesevent_album_option_box1">
                <?php if($createItem){ ?>
                  <a href="<?php echo $this->url(array('module' => 'sesevent', 'action' => 'create', 'album_id' => $this->album_id), 'sesevent_specific_album', true); ?>" class="sesbasic_icon_add">
                    <?php echo $this->translate('Add More Photos'); ?>
                  </a>
                <?php } ?>
                <?php if($editItem){ ?>
                  <a href="<?php echo $this->url(array('module' => 'sesevent', 'action' => 'edit', 'album_id' => $this->album_id), 'sesevent_specific_album', true); ?>" class="sesbasic_icon_edit"><?php echo $this->translate('Edit Settings'); ?></a>
                  <a href="<?php echo $this->url(array('module' => 'sesevent', 'action' => 'editphotos', 'album_id' => $this->album_id), 'sesevent_specific_album', true); ?>" class="sesevent_photos_icon"><?php echo $this->translate('Edit Photos'); ?></a>
                <?php } ?>
                <?php if($deleteItem){ ?>
                  <a class="smoothbox sesbasic_icon_delete" href="<?php echo $this->url(array('module' => 'sesevent', 'action' => 'delete', 'album_id' => $this->album_id, 'format' => 'smoothbox'), 'sesevent_specific_album', true); ?>"><?php echo $this->translate('Delete Album'); ?></a>
                <?php } ?>
                 <?php echo $this->htmlLink(Array("module"=> "core", "controller" => "report", "action" => "create", "route" => "default", "subject" => $this->album->getGuid(),'format' => 'smoothbox'),$this->translate("Report"), array("class" => "smoothbox sesbasic_icon_report")); ?>               
              </div>
        		</span>
        	<?php endif;?>
      </div>
    </div>
  </div>
</div>
<?php $baseUrl = $this->layout()->staticBaseUrl; ?>
<div class="clear sesbasic_clearfix sesbasic_bxs" id="scrollHeightDivSes_<?php echo $randonNumber; ?>">
  <ul id="ses-image-view" class="album-photo sesevent_album_listings sesevent_album_photos_listings sesevent_album_photos_flex_view sesbasic_clearfix" style="<?php echo $this->paginator->getTotalItemCount() == 0  ? 'none' : "" ; ?>">
<?php } ?>
    <?php 
    			$limit = 0;
          foreach( $this->paginator as $photo ){
           if($this->view_type != 'masonry'){ ?>
            <li id="thumbs-photo-<?php echo $photo->photo_id ?>" class="ses_album_image_viewer sesevent_album_list_photo_grid sesevent_album_list_grid  sesea-i-<?php echo (isset($this->insideOutside) && $this->insideOutside == 'outside') ? 'outside' : 'inside'; ?> sesea-i-<?php echo (isset($this->fixHover) && $this->fixHover == 'fix') ? 'fix' : 'over'; ?> sesbm" style="width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">
              <?php $imageURL = $photo->getHref(); ?>
              <a class="sesevent_album_list_grid_img" href="<?php echo $photo->getHref(); ?>" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;"> 
                <span style="background-image: url(<?php echo $photo->getPhotoUrl('thumb.normalmain'); ?>);"></span>
              </a>
              <?php 
              if(isset($this->socialSharing) || isset($this->likeButton) ){
              //album viewpage link for sharing
                $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $photo->getHref()); ?>
      					<span class="sesevent_album_list_grid_btns">
                  <?php if(isset($this->socialSharing)){ ?>
                    
                    <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $photo)); ?>
                    

        					<?php } 
                  $canComment =  $canComment =  $this->event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
                  	if(isset($this->likeButton) && Engine_Api::_()->user()->getViewer()->getIdentity() !=0 && $canComment){  ?>
                    <?php $albumLikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($photo->photo_id,'sesevent_photo'); ?>
                    <a href="javascript:;" data-src='<?php echo $photo->photo_id; ?>' class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_photolike <?php echo ($albumLikeStatus) ? 'button_active' : '' ; ?>">
                      <i class="fa fa-thumbs-up"></i>
                      <span><?php echo $photo->like_count; ?></span>
                    </a>
                  <?php } 
                  ?>
              	</span>
      				<?php } ?>
              
              <?php if(isset($this->like) || isset($this->comment) || isset($this->view) || isset($this->title) || isset($this->by)){ ?>
                <p class="sesevent_album_list_grid_info sesbasic_clearfix">
                  <?php if(isset($this->title)) { ?>
                    <span class="sesevent_album_list_grid_title">
                      <?php echo $this->htmlLink($photo, $this->htmlLink($photo, $this->string()->truncate($photo->getTitle(), $this->title_truncation), array('title'=>$photo->getTitle()))) ?>
                    </span>
                  <?php } ?>
                  <span class="sesevent_album_list_grid_stats">
                    <?php if(isset($this->by)) { ?>
                      <span class="sesevent_album_list_grid_owner">
                        <?php echo $this->translate('By');?>
                        <?php echo $this->htmlLink($photo->getOwner()->getHref(), $photo->getOwner()->getTitle(), array('class' => 'thumbs_author')) ?>
                      </span>
                    <?php }?>
                  </span>
                  <span class="sesevent_album_list_grid_stats sesbasic_text_light">
                    <?php if(isset($this->like)) { ?>
                      <span class="sesevent_album_list_grid_likes" title="<?php echo $this->translate(array('%s like', '%s likes', $photo->like_count), $this->locale()->toNumber($photo->like_count))?>">
                        <i class="fa fa-thumbs-up"></i>
                        <?php echo $photo->like_count;?>
                      </span>
                    <?php } ?>
                  <?php if(isset($this->comment)) { ?>
                    <span class="sesevent_album_list_grid_comment" title="<?php echo $this->translate(array('%s comment', '%s comments', $photo->comment_count), $this->locale()->toNumber($photo->comment_count))?>">
                      <i class="fa fa-comment"></i>
                      <?php echo $photo->comment_count;?>
                    </span>
                 <?php } ?>
                 <?php if(isset($this->view)) { ?>
                  <span class="sesevent_album_list_grid_views" title="<?php echo $this->translate(array('%s view', '%s views', $photo->view_count), $this->locale()->toNumber($photo->view_count))?>">
                    <i class="fa fa-eye"></i>
                    <?php echo $photo->view_count;?>
                  </span>
                 <?php } ?>
                    </span>
                </p>         
              <?php } ?>
            </li>
         <?php }else{
          $imageURL = $photo->getPhotoUrl('thumb.normalmain');
          if(strpos($imageURL,'http://') === FALSE && strpos($imageURL,'https://') === FALSE)
    					$imageGetSizeURL = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . substr($imageURL, 0, strpos($imageURL, "?"));
          else
          	$imageGetSizeURL = $imageURL;
    			$imageHeightWidthData = getimagesize($imageGetSizeURL);
          $width = isset($imageHeightWidthData[0]) ? $imageHeightWidthData[0] : '300';
          $height = isset($imageHeightWidthData[1]) ? $imageHeightWidthData[1] : '200'; ?>
         		<li id="thumbs-photo-<?php echo $photo->photo_id ?>" data-w="<?php echo $width ?>" data-h="<?php echo $height; ?>" class="ses_album_image_viewer sesevent_album_list_flex_thumb sesevent_album_list_photo_grid sesevent_album_list_grid sesbasic_list_photo_grid sesea-i-inside sesea-i-<?php echo (isset($this->fixHover) && $this->fixHover == 'fix') ? 'fix' : 'over'; ?>">
              <?php $imageViewerURL = $photo->getHref() ?>
              <a class="sesevent_album_list_flex_img"  href="<?php echo $photo->getHref(); ?>"> 
                <img data-src="<?php echo $imageURL; ?>" src="<?php $this->layout()->staticBaseUrl; ?>application/modules/Sesevent/externals/images/blank-img.gif" /> 
              </a>
              <?php 
              if(isset($this->socialSharing) || isset($this->likeButton)){
              //album viewpage link for sharing
                $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $photo->getHref()); ?>
      					<span class="sesevent_album_list_grid_btns">
                  <?php if(isset($this->socialSharing)){ ?>
                    
                    <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $photo)); ?>

        					<?php }
                   		$canComment =  $this->event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
                  	 if(isset($this->likeButton) && Engine_Api::_()->user()->getViewer()->getIdentity() !=0 && $canComment){  ?>	
                    <?php $albumLikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($photo->photo_id,'sesevent_photo'); ?>
                    <a href="javascript:;" data-url='<?php echo $photo->photo_id; ?>' class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_photolike <?php echo ($albumLikeStatus) ? 'button_active' : '' ; ?>">
                      <i class="fa fa-thumbs-up"></i>
                      <span><?php echo $photo->like_count; ?></span>
                    </a>
                  <?php } 
                 ?>
              	</span>
      				<?php } ?>
              
          
              
              <?php if(isset($this->like) || isset($this->comment) || isset($this->view) || isset($this->title) || isset($this->by)){ ?>
                <p class="sesevent_album_list_grid_info sesbasic_clearfix">
                  <?php if(isset($this->title)) { ?>
                    <span class="sesevent_album_list_grid_title">
                      <?php echo $this->htmlLink($photo, $this->htmlLink($photo, $this->string()->truncate($photo->getTitle(), $this->title_truncation), array('title'=>$photo->getTitle()))) ?>
                    </span>
                  <?php } ?>
                  <span class="sesevent_album_list_grid_stats">
                    <?php if(isset($this->by)) { ?>
                      <span class="sesevent_album_list_grid_owner">
                        <?php echo $this->translate('By');?>
                        <?php echo $this->htmlLink($photo->getOwner()->getHref(), $photo->getOwner()->getTitle(), array('class' => 'thumbs_author')) ?>
                      </span>
                    <?php }?>
                  </span>
                  <span class="sesevent_album_list_grid_stats sesbasic_text_light">
                    <?php if(isset($this->like)) { ?>
                      <span class="sesevent_album_list_grid_likes" title="<?php echo $this->translate(array('%s like', '%s likes', $photo->like_count), $this->locale()->toNumber($photo->like_count))?>">
                        <i class="fa fa-thumbs-up"></i>
                        <?php echo $photo->like_count;?>
                      </span>
                    <?php } ?>
                  <?php if(isset($this->comment)) { ?>
                    <span class="sesevent_album_list_grid_comment" title="<?php echo $this->translate(array('%s comment', '%s comments', $photo->comment_count), $this->locale()->toNumber($photo->comment_count))?>">
                      <i class="fa fa-comment"></i>
                      <?php echo $photo->comment_count;?>
                    </span>
                 <?php } ?>
                 <?php if(isset($this->view)) { ?>
                  <span class="sesevent_album_list_grid_views" title="<?php echo $this->translate(array('%s view', '%s views', $photo->view_count), $this->locale()->toNumber($photo->view_count))?>">
                    <i class="fa fa-eye"></i>
                    <?php echo $photo->view_count;?>
                  </span>
                 <?php } ?>
                    </span>
                </p>         
              <?php } ?>   
            </li>
         <?php } 
         		 $limit++;
           }
         		 if($this->loadOptionData == 'pagging'){ ?>
             <?php echo $this->paginationControl($this->paginator, null, array("_pagging.tpl", "sesevent"),array('identityWidget'=>$randonNumber)); ?>
       		  <?php }
         
          ?>
<?php if(!$this->is_ajax) { ?>
  </ul>
  <!--Album Info Tab-->
	<div class="clear sesbasic_clearfix sesevent_album_info">
    <div class="sesevent_album_info_left album-info" style="display:none;">
      <?php if( '' != trim($this->album->getDescription())){ ?>
        <div class="sesevent_album_info_desc clear"><?php echo nl2br($this->album->getDescription()); ?></div>  
      <?php }else{ ?>
      	<div class="tip">
        	<span>
        		<?php echo $this->translate("No description found.");?>
          </span>
        </div>
      <?php } ?>
		</div>
   	<div class="sesevent_album_info_left album-discussion layout_core_comments" style="display:none">
  		<?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){ ?>
      <?php echo $this->action("list", "comment", "sesadvancedcomment", array("type" => "sesevent_album", "id" => $this->album->getIdentity(),'is_ajax_load'=>true)); 
        }else{ echo $this->action("list", "comment", "core", array("type" => "sesevent_album", "id" => $this->album->getIdentity()));
        }
         ?>
  	</div>
	 </div>
  <?php } ?>
  <?php if(!$this->is_ajax){ ?>
   <?php if($this->loadOptionData != 'pagging'){ ?>    
     <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" > 
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'sesbasic_animation sesbasic_link_btn fa fa-sync')); ?> </div>
  <div class="sesbasic_load_btn" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span> </div>  
  <?php } ?>
</div>

<script type="text/javascript">
<?php if(!$this->is_ajax && $this->canEdit){ ?>
sesJqueryObject('<div class="sesevent_album_photo_update_popup sesbasic_bxs" id="sesevent_popup_cam_upload" style="display:none"><div class="sesevent_album_photo_update_popup_overlay"></div><div class="sesevent_album_photo_update_popup_container sesevent_album_photo_update_webcam_container"><div class="sesevent_album_photo_update_popup_header"><?php echo $this->translate("Click to Take Cover Photo") ?><a class="fa fa-times" href="javascript:;" onclick="hideProfilePhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="sesevent_album_photo_update_popup_webcam_options"><div id="sesevent_camera" style="background-color:#ccc;"></div><div class="centerT sesevent_album_photo_update_popup_btns">   <button onclick="take_snapshot()" style="margin-right:3px;" ><?php echo $this->translate("Take Cover Photo") ?></button><button onclick="hideProfilePhotoUpload()" ><?php echo $this->translate("Cancel") ?></button></div></div></div></div><div class="sesevent_album_photo_update_popup sesbasic_bxs" id="sesevent_popup_existing_upload" style="display:none"><div class="sesevent_album_photo_update_popup_overlay"></div><div class="sesevent_album_photo_update_popup_container" id="sesevent_popup_container_existing"><div class="sesevent_album_photo_update_popup_header"><?php echo $this->translate("Select a cover photo") ?><a class="fa fa-times" href="javascript:;" onclick="hideProfilePhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="sesevent_album_photo_update_popup_content"><div id="sesevent_album_existing_data"></div><div id="sesevent_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Sesbasic/externals/images/loading.gif" alt="<?php echo $this->translate("Loading"); ?>" style="margin-top:10px;"  /></div></div></div></div>').appendTo('body');
var canPaginatePageNumber = 1;
sesJqueryObject(document).on('click','#uploadWebCamPhoto',function(){
	sesJqueryObject('#sesevent_popup_cam_upload').show();
	<!-- Configure a few settings and attach camera -->
	Webcam.set({
		width: 320,
		height: 240,
		image_format:'jpeg',
		jpeg_quality: 90
	});
	Webcam.attach('#sesevent_camera');
});
<!-- Code to handle taking the snapshot and displaying it locally -->
function take_snapshot() {
	// take snapshot and get image data
	Webcam.snap(function(data_uri) {
		Webcam.reset();
		sesJqueryObject('#sesevent_popup_cam_upload').hide();
		// upload results
		sesJqueryObject('.sesevent_album_cover_container').append('<div id="sesevent_album_cover_loading" class="sesbasic_loading_cont_overlay"></div>');
		 Webcam.upload( data_uri, en4.core.staticBaseUrl+'sesevent/album/upload-cover/album_id/<?php echo $this->album_id ?>' , function(code, text) {
				response = sesJqueryObject.parseJSON(text);
				sesJqueryObject('#sesevent_album_cover_loading').remove();
				sesJqueryObject('.sesevent_album_cover_image').css('background-image', 'url(' + response.file + ')');
				sesJqueryObject('#sesevent_album_cover_default').hide();
				sesJqueryObject('#coverChangesesevent').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Change Cover Photo'));
				sesJqueryObject('#coverRemovesesevent').css('display','block');
			} );
	});
}
function hideProfilePhotoUpload(){
	if(typeof Webcam != 'undefined')
	 Webcam.reset();
	canPaginatePageNumber = 1;
	sesJqueryObject('#sesevent_popup_cam_upload').hide();
	sesJqueryObject('#sesevent_popup_existing_upload').hide();
	if(typeof Webcam != 'undefined'){
		sesJqueryObject('.slimScrollDiv').remove();
		sesJqueryObject('.sesevent_album_photo_update_popup_content').html('<div id="sesevent_album_existing_data"></div><div id="sesevent_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Sesbasic/externals/images/loading.gif" alt="Loading" style="margin-top:10px;"  /></div>');
	}
}

sesJqueryObject(document).on('click','#coverChangesesevent',function(){
	document.getElementById('uploadFilesesevent').click();	
});
function uploadCoverArt(input){
	 var url = input.value;
    var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
    if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG')){
				uploadFileToServer(input.files[0]);
    }else{
				//Silence
		}
}
sesJqueryObject('#coverRemovesesevent').click(function(){
		sesJqueryObject(this).css('display','none');
		sesJqueryObject('.sesevent_album_cover_image').css('background-image', 'url()');
		sesJqueryObject('#sesevent_album_cover_default').show();
		var album_id = '<?php echo $this->album->album_id; ?>';
		uploadURL = en4.core.staticBaseUrl+'sesevent/album/remove-cover/album_id/'+album_id;
		var jqXHR=sesJqueryObject.ajax({
			url: uploadURL,
			type: "POST",
			contentType:false,
			processData: false,
			cache: false,
			success: function(response){
				sesJqueryObject('#coverChangesesevent').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Add Cover Photo'));
				//silence
			 }
			}); 
});
sesJqueryObject('#changePositionOfCoverPhoto').click(function(){
		sesJqueryObject('.sesevent_album_cover_fade').css('display','none');
		sesJqueryObject('.sesevent_album_cover_inner').css('display','none');
		sesJqueryObject('#sesevent-pos-btn').css('display','inline-block');
});
sesJqueryObject(document).on('click','#cancelCoverPosition',function(){
	sesJqueryObject('.sesevent_album_cover_fade').css('display','block');
	sesJqueryObject('.sesevent_album_cover_inner').css('display','block');
	sesJqueryObject('#sesevent-pos-btn').css('display','none');
});
sesJqueryObject('#saveCoverPosition').click(function(){
	var album_id = '<?php echo $this->album->album_id; ?>';
	var bgPosition = sesJqueryObject('#cover_art_work_image').css('background-position');
	sesJqueryObject('.sesevent_album_cover_fade').css('display','block');
	sesJqueryObject('.sesevent_album_cover_inner').css('display','block');
	sesJqueryObject('#sesevent-pos-btn').css('display','none');
	var URL = en4.core.staticBaseUrl+'albums/index/change-position/album_id/'+album_id;
	(new Request.HTML({
		method: 'post',
		'url':URL,
		'data': {
			format: 'html',
			position: bgPosition,    
			album_id:'<?php echo $this->album_id; ?>',
		},
		onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
			//silence
		}
	})).send();
});
function uploadFileToServer(files){
	sesJqueryObject('.sesevent_album_cover_container').append('<div id="sesevent_album_cover_loading" class="sesbasic_loading_cont_overlay" style="display:block;"></div>');
	var formData = new FormData();
	formData.append('Filedata', files);
	uploadURL = en4.core.staticBaseUrl+'sesevent/album/upload-cover/album_id/<?php echo $this->album_id ?>';
	var jqXHR=sesJqueryObject.ajax({
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
		cache: false,
		data: formData,
		success: function(response){
			response = sesJqueryObject.parseJSON(response);
			sesJqueryObject('#sesevent_album_cover_loading').remove();
			sesJqueryObject('.sesevent_album_cover_image').css('background-image', 'url(' + response.file + ')');
				sesJqueryObject('#sesevent_album_cover_default').hide();
			sesJqueryObject('#coverChangesesevent').html('<i class="fa fa-plus"></i>'+en4.core.language.translate('Change Cover Photo'));
			sesJqueryObject('#coverRemovesesevent').css('display','block');
     }
    }); 
}
<?php } ?>

<?php if($this->loadOptionData == 'auto_load'){ ?>
		window.addEvent('domready', function() {
		 sesJqueryObject(window).scroll( function() {
			 if(!$('loading_image_<?php echo $randonNumber; ?>'))
			 	return false;				
				var containerId = '#scrollHeightDivSes_<?php echo $randonNumber;?>';
				if(typeof sesJqueryObject(containerId).offset() != 'undefined') {
					var hT = sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').offset().top,
					hH = sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').outerHeight(),
					wH = sesJqueryObject(window).height(),
					wS = sesJqueryObject(this).scrollTop();
					if ((wS + 30) > (hT + hH - wH) && sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').css('display') == 'block') {
						document.getElementById('feed_viewmore_link_<?php echo $randonNumber; ?>').click();
					}
				}				
     });
	});
<?php } ?>
</script>
<?php } ?>
<script type="text/javascript">
<?php if(!$this->is_ajax){ ?>
		sesJqueryObject(document).on('click','#tab_links_cover > li',function(){
			var elemLength = sesJqueryObject('#tab_links_cover').children();	
			for(i=0;i<elemLength.length;i++){
					sesJqueryObject(elemLength[i].removeClass('sesevent_album_cover_tabactive'));
					sesJqueryObject('.'+sesJqueryObject(elemLength[i]).attr('data-src')).css('display','none');
			}
				sesJqueryObject(this).addClass('sesevent_album_cover_tabactive');
				sesJqueryObject('.'+sesJqueryObject(this).attr('data-src')).css('display','block');
				if("<?php echo $this->view_type ; ?>" == 'masonry'){
					sesJqueryObject("#ses-image-view").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->height); ?>});
				}
				if(sesJqueryObject(this).attr('data-src') == 'album-photo'){
					sesJqueryObject('#sesevent-container-right').css('display','none');
					if(sesJqueryObject('#view_more_<?php echo $randonNumber; ?>'))
						sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').css('display','block');
					if(sesJqueryObject('#view_more_<?php echo $randonNumber; ?>'))
						sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').css('display','none');					
					if(sesJqueryObject('#view_more_related_<?php echo $randonNumber; ?>'))							
							sesJqueryObject('#view_more_related_<?php echo $randonNumber; ?>').css('display','none');						
						if(sesJqueryObject('#view_more_related<?php echo $randonNumber; ?>'))
							sesJqueryObject('#loading_image_related_<?php echo $randonNumber; ?>').css('display','none');
				}else{
					sesJqueryObject('#sesevent-container-right').css('display','block');
						if(sesJqueryObject('#view_more_<?php echo $randonNumber; ?>'))							
							sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').css('display','none');						
						if(sesJqueryObject('#view_more_<?php echo $randonNumber; ?>'))
							sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').css('display','none');
						if(sesJqueryObject('#view_more_related_<?php echo $randonNumber; ?>'))							
							sesJqueryObject('#view_more_related_<?php echo $randonNumber; ?>').css('display','none');						
						if(sesJqueryObject('#view_more_related<?php echo $randonNumber; ?>'))
							sesJqueryObject('#loading_image_related_<?php echo $randonNumber; ?>').css('display','none');
				}
		});
	 var divPosition = sesJqueryObject('.sesevent_album_cover_inner').offset();
	 sesJqueryObject('html, body').animate({scrollTop: divPosition.top}, "slow");
	 if("<?php echo $this->view_type ; ?>" == 'masonry'){
		sesJqueryObject("#ses-image-view").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->height); ?>});
	 }
<?php } ?>
viewMoreHide_<?php echo $randonNumber; ?>();
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    if ($('view_more_<?php echo $randonNumber; ?>'))
      $('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
			if(sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').css('display') == 'none'){
				sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').remove();
				sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').remove();
			}	
  }
	 function viewMore_<?php echo $randonNumber; ?> (){
    document.getElementById('view_more_<?php echo $randonNumber; ?>').style.display = 'none';
    document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = '';    
    (new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/album-view-page/',
      'data': {
        format: 'html',
        page: <?php echo $this->page ; ?>,    
				params :'<?php echo json_encode($this->params); ?>', 
				is_ajax : 1,
				album_id:'<?php echo $this->album_id; ?>',
				event_id:'<?php echo $this->event_id ?>',
				identity : '<?php echo $randonNumber; ?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        document.getElementById('ses-image-view').innerHTML = document.getElementById('ses-image-view').innerHTML + responseHTML;
				if("<?php echo $this->view_type ; ?>" == 'masonry'){
							sesJqueryObject("#ses-image-view").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->height); ?>});
				}
				if($('loading_image_<?php echo $randonNumber; ?>'))
					document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = 'none';
      }
    })).send();
    return false;
  }

function paggingNumber<?php echo $randonNumber; ?>(pageNum){
		 sesJqueryObject ('.overlay_<?php echo $randonNumber ?>').css('display','block');
			(new Request.HTML({
				method: 'post',
				'url': en4.core.baseUrl + 'widget/index/mod/sesevent/name/album-view-page/',
				'data': {
					format: 'html',
					page: pageNum,    
					params :'<?php echo json_encode($this->params); ?>', 
					is_ajax : 1,
					identity : '<?php echo $randonNumber; ?>',
					album_id:'<?php echo $this->album_id; ?>',
				},
				onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
					sesJqueryObject ('.overlay_<?php echo $randonNumber ?>').css('display','none');
					document.getElementById('ses-image-view').innerHTML =  responseHTML;
					if("<?php echo $this->view_type ; ?>" == 'masonry'){
						sesJqueryObject("#ses-image-view").sesbasicFlexImage({rowHeight: <?php echo str_replace('px','',$this->height); ?>});
					}
				}
			})).send();
			return false;
	}
</script>