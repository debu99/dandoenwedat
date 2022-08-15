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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/SesLightbox/photoswipe-ui-default.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/SesLightbox/lightbox.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/flexcroll.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/photoswipe.css'); ?>

<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_album.css'); ?>
<?php
if(!$this->is_ajax && isset($this->docActive)){
	$imageURL = $this->photo->getPhotoUrl();
	if(strpos($this->photo->getPhotoUrl(),'http') === false)
          	$imageURL = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://". $_SERVER['HTTP_HOST'].$this->photo->getPhotoUrl();
  $this->doctype('XHTML1_RDFA');
  $this->headMeta()->setProperty('og:title', $this->photo->getTitle());
  $this->headMeta()->setProperty('og:description', $this->photo->getDescription());
  $this->headMeta()->setProperty('og:image',$imageURL);
  $this->headMeta()->setProperty('twitter:title', $this->photo->getTitle());
  $this->headMeta()->setProperty('twitter:description', $this->photo->getDescription());
}
?>
<?php
  $this->headTranslate(array(
    'Save', 'Cancel', 'delete',
  ));
?>
<?php $baseUrl = $this->layout()->staticBaseUrl; ?>
<script type="text/javascript">
var maxHeight = <?php echo $this->maxHeight; ?>;
function doResizeForButton(){
	var width = sesJqueryObject('.sesevent_view_photo_container_wrapper').width();
  sesJqueryObject('#media_photo').css('max-width',width+'px');
	sesJqueryObject('#media_photo').css('max-height',maxHeight+'px');
	<?php if(Engine_Api::_()->user()->getViewer()->getIdentity() == '0'){ ?>
			return false;
	<?php } ?>
	var topPositionOfParentDiv =  sesJqueryObject(".sesevent_photo_view_option_btn").offset().top + 35;
	topPositionOfParentDiv = topPositionOfParentDiv+'px';
	var leftPositionOfParentDiv =  sesJqueryObject(".sesevent_photo_view_option_btn").offset().left - 135;
	leftPositionOfParentDiv = leftPositionOfParentDiv+'px';
	sesJqueryObject('.sesbasic_option_box').css('top',topPositionOfParentDiv);
	sesJqueryObject('.sesbasic_option_box').css('left',leftPositionOfParentDiv);
}
 var width = sesJqueryObject('.sesevent_view_photo_container_wrapper').width();
  sesJqueryObject('#media_photo').css('max-width',width+'px');
	sesJqueryObject('#media_photo').css('max-height',maxHeight+'px');

window.addEvent('load',function(){
	doResizeForButton();
});
<?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != ''){ ?>
  en4.core.runonce.add(function() {
    var descEls = $$('.sesevent_view_photo_des');
    if( descEls.length > 0 ) {
      descEls[0].enableLinks();
    }
    var taggerInstance = window.taggerInstance = new Tagger('media_photo_next', {
      'title' : '<?php echo $this->string()->escapeJavascript($this->translate('Add Tag'));?>',
      'description' : '<?php echo $this->string()->escapeJavascript($this->translate('Type a tag or select a name from the list.'));?>',
      'createRequestOptions' : {
        'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'add'), 'default', true) ?>',
        'data' : {
          'subject' : '<?php echo $this->subject()->getGuid() ?>'
        }
      },
      'deleteRequestOptions' : {
        'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'remove'), 'default', true) ?>',
        'data' : {
          'subject' : '<?php echo $this->subject()->getGuid() ?>'
        }
      },
      'cropOptions' : {
        'container' : $('media_photo_next')
      },
      'tagListElement' : 'media_tags',
      'existingTags' : <?php echo Zend_Json::encode($this->tags) ?>,
      'suggestProto' : 'request.json',
      'suggestParam' : "<?php echo $this->url(array('module' => 'user', 'controller' => 'friends', 'action' => 'suggest', 'includeSelf' => true), 'default', true) ?>",
      'guid' : <?php echo ( $this->viewer->getIdentity() ? "'".$this->viewer->getGuid()."'" : 'false' ) ?>,
      'enableCreate' : <?php echo ( $this->canTag ? 'true' : 'false') ?>,
      'enableDelete' : <?php echo ( $this->canEdit ? 'true' : 'false') ?>
    });
    // Remove the href attrib while tagging
    var nextHref = $('media_photo_next').get('href');
    taggerInstance.addEvents({
      'onBegin' : function() {
				sesJqueryObject('.sesevent_photo_view_btns').hide();
        $('media_photo_next').erase('href');
      },
      'onEnd' : function() {
				sesJqueryObject('.sesevent_photo_view_btns').show();
        $('media_photo_next').set('href', nextHref);
      }
    });
    var keyupEvent = function(e) {
      if( e.target.get('tag') == 'html' ||
          e.target.get('tag') == 'body' ) {
        if( e.key == 'right' ) {
          $('photo_next').fireEvent('click', e);
          //window.location.href = "<?php echo ( $this->nextPhoto ? $this->nextPhoto->getHref() : 'window.location.href' ) ?>";
        } else if( e.key == 'left' ) {
          $('photo_prev').fireEvent('click', e);
          //window.location.href = "<?php echo ( $this->previousPhoto ? $this->previousPhoto->getHref() : 'window.location.href' ) ?>";
        }
      }
    }
    window.addEvent('keyup', keyupEvent);
    // Add shutdown handler
    en4.core.shutdown.add(function() {
      window.removeEvent('keyup', keyupEvent);
    });
  });
<?php } ?>
</script>
<div class='sesevent_view_photo sesbasic_bxs sesbasic_clearfix'>
  <div class='sesevent_view_photo_container_wrapper sesbasic_clearfix'>
    <?php if( $this->album->count() > 1 ): ?>
      <div class="sesevent_view_photo_nav_btns">
        <?php
        $photoPreviousData = Engine_Api::_()->sesevent()->getPreviousPhoto($this->album->album_id ,$this->photo->order ) ?  Engine_Api::_()->sesevent()->getPreviousPhoto($this->album->album_id,$this->photo->order) : null;
        echo $this->htmlLink((isset($photoPreviousData->album_id) ?  $photoPreviousData->getHref() : null ), '<i class="fa fa-angle-left"></i>', array('id' => 'photo_prev','data-url'=>$photoPreviousData->photo_id, 'class' => 'sesevent_view_photo_nav_prev_btn'));
        $photoNextData = Engine_Api::_()->sesevent()->getNextPhoto($this->album->album_id  ,$this->photo->order ) ?  Engine_Api::_()->sesevent()->getNextPhoto($this->album->album_id  ,$this->photo->order ) : null;
         ?>
        <?php echo $this->htmlLink(( isset($photoNextData->album_id) ?  $photoNextData->getHref() : null ), '<i class="fa fa-angle-right"></i>', array('id' => 'photo_next','data-url'=>$photoNextData->photo_id, 'class' => 'sesevent_view_photo_nav_nxt_btn')) ?>
      </div>
    <?php endif ?>
    <div class='sesevent_view_photo_container' id='media_photo_div'>
      <?php 
        $imageViewerURL = $this->photo->getHref();
        if($imageViewerURL != ''){
      ?>
        <a href="<?php echo $this->photo->getHref(); ?>" title="<?php echo $this->translate('Open image in image viewer'); ?>" onclick="openLightBoxForSesPlugins('<?php echo $imageViewerURL; ?>','<?php echo $this->photo->getPhotoUrl(); ?>');return false;" class="sesevent_view_photo_expend seslightbox_no_prop"><i class="fa fa-expand"></i></a>
      <?php } ?>
      <div id="media_photo_next">
      	<a id="photo_main_next" href="javascript:;">
        <?php echo $this->htmlImage($this->photo->getPhotoUrl(), $this->photo->getTitle(), array(
          'id' => 'media_photo',
          'onload'=>'doResizeForButton()'
        )); ?>
        </a>
      </div>
    </div>
    <?php if( $this->canEdit ): ?>
      <div class="sesevent_view_photo_rotate_btns">          
        <a class="sesevent_icon_photos_rotate_ccw" id="ses-rotate-90" href="javascript:void(0)" onclick="sesPhotoRotate('<?php echo $this->photo->getIdentity() ?>','90')">&nbsp;</a>
        <a class="sesevent_icon_photos_rotate_cw" id="ses-rotate-270" href="javascript:void(0)" onclick="sesPhotoRotate('<?php echo $this->photo->getIdentity() ?>','270')">&nbsp;</a>
        <a class="sesevent_icon_photos_flip_horizontal" id="ses-rotate-horizontal"  href="javascript:void(0)" onclick="sesPhotoRotate('<?php echo $this->photo->getIdentity() ?>','horizontal')">&nbsp;</a>
        <a class="sesevent_icon_photos_flip_vertical" id="ses-rotate-vertical"  href="javascript:void(0)" onclick="sesPhotoRotate('<?php echo $this->photo->getIdentity() ?>','vertical')">&nbsp;</a>          
      </div>
    <?php endif ?>
    <?php
    if($this->canCommentMemberLevelPermission == 0){
    		$canComment = false;
    }else{
    		$canComment = true;
    } 
   
    	$urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $this->photo->getHref());
    ?>
      <div class="sesbasic_clearfix sesevent_photo_view_btns">
        
        <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $this->photo, 'param' => 'photoviewpage')); ?>
        

               <?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != ''){ ?>
        <?php if($this->canTag){ ?>
        <a title="<?php echo $this->translate('Tag'); ?>" href="javascript:void(0);" onclick="taggerInstance.begin();" class="sesevent_view_tag_button"><i class="fa fa-tag"></i></a>
        <?php } ?>
        <span class="sesevent_photo_view_option_btn">
          <a title="<?php echo $this->translate('Options'); ?>" href="javascript:;" id="parent_container_option"><i id="fa-ellipsis-v" class="fa fa-ellipsis-v"></i></a>
        </span>  
      </div>
    <?php } ?>
  </div>
  <div class="sesevent_view_photo_count">
    <?php echo $this->translate('Photo %1$s of %2$s',
        $this->locale()->toNumber($this->photo->getPhotoIndex() + 1),
        $this->locale()->toNumber($this->album->count())) ?>
  </div>
	<div class="sesevent_photo_view_bottom_right">
    <?php if(isset($this->criteria)){ ?>
      <!-- Corresponding photo as per album id -->
      <div class="layout_sesevent_photo_strip">
        <div class="sesevent_photos_strip_slider sesbasic_clearfix clear">
          <a id="prevSlide" class="sesevent_photos_strip_slider_btn btn-prev"><i class="fa fa-angle-left"></i></a>
          <div class="sesevent_photos_strip_slider_content">
            <div id="sesevent_corresponding_photo" style="width:257px;">
            <?php if(!$this->is_ajax){ ?>
              <img id="sesevent_corresponding_photo_image" src="<?php echo $this->layout()->staticBaseUrl; ?>application/modules/Sesbasic/externals/images/loading.gif" alt="" style="margin-top:23px;" />
             <?php } ?>
            </div>
          </div>
          <a id="nextSlide" class="sesevent_photos_strip_slider_btn btn-nxt"><i class="fa fa-angle-right"></i></a>
        </div>
      </div>
    <?php } ?>
  </div>
	<div class="sesevent_photo_view_bottom_middle sesbasic_clearfix">
    <?php if( $this->photo->getTitle() ): ?>
      <div class="sesevent_view_photo_title">
        <?php echo $this->photo->getTitle(); ?>
      </div>
    <?php endif; ?>
    <div class="sesevent_view_photo_middle_box clear sesbasic_clearfix">
      <div class="sesevent_view_photo_owner_info sesbasic_clearfix">
        <div class="sesevent_view_photo_owner_photo">
          <?php $albumOwnerDetails = Engine_Api::_()->user()->getUser($this->photo->user_id); ?>
          <?php echo $this->htmlLink($albumOwnerDetails->getHref(), $this->itemPhoto($albumOwnerDetails, 'thumb.icon')); ?>  
        </div>
        <div class="sesevent_view_photo_owner_details">
          <span class="sesevent_view_photo_owner_name sesbasic_text_light">
            by <?php echo $this->htmlLink($albumOwnerDetails->getHref(), $albumOwnerDetails->getTitle()); ?>
          </span>
          <span class="sesbasic_text_light sesevent_view_photo_date">
            <?php echo $this->translate('in %1$s',$this->htmlLink( $this->album->getHref(), $this->album->getTitle())); ?>
            <?php echo $this->translate('Added %1$s', $this->timestamp($this->photo->creation_date)); ?>  
          </span>
        </div>
    	</div>
    </div>
    <div class="sesevent_view_photo_info_left">
      <?php if( $this->photo->getDescription() ): ?>
        <div class="sesevent_view_photo_des">
          <b>Description</b>
          <?php echo nl2br($this->photo->getDescription()) ?>
        </div>
      <?php endif; ?>
      <div class="sesevent_view_photo_tags" id="media_tags" style="display: none;">
        <b><?php echo $this->translate('Tagged') ?></b>
      </div>
    </div>
    <!-- comment code-->
    <div class="sesevent_photo_view_bottom_comments layout_core_comments">
      <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesadvancedcomment')){ ?>
      <?php echo $this->action("list", "comment", "sesadvancedcomment", array("type" => "sesevent_photo", "id" => $this->photo->getIdentity(),'is_ajax_load'=>true)); 
        }else{ echo $this->action("list", "comment", "core", array("type" => "sesevent_photo", "id" => $this->photo->getIdentity())); } ?> 
    </div>
  </div>
</div>
<script type="text/javascript">
var optionDataForButton;
optionDataForButton = '<div class="sesbasic_option_box"><?php if ($this->viewer()->getIdentity()):?><?php if( $this->canEdit ): ?><?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'album', 'action' => 'edit-photo', 'photo_id' => $this->photo->getIdentity(), 'format' => 'smoothbox'), $this->translate('Edit'), array('class' => 'smoothboxOpenPhoto')) ?><?php endif; ?><?php if( $this->canDelete ): ?><?php echo $this->htmlLink(array('route' => 'sesevent_extended', 'controller' => 'photo', 'action' => 'delete', 'photo_id' => $this->photo->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete'), array('class' => 'smoothboxOpenPhoto')) ?><?php endif; ?><?php if( !$this->message_view ):?><?php echo $this->htmlLink(Array('module'=>'activity', 'controller'=>'index', 'action'=>'share', 'route'=>'default', 'type'=>$this->photo->getType(), 'id'=>$this->photo->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'smoothboxOpenPhoto')); ?><?php echo $this->htmlLink(Array('module'=>'core', 'controller'=>'report', 'action'=>'create', 'route'=>'default', 'subject'=>$this->photo->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'smoothboxOpenPhoto')); ?><?php echo $this->htmlLink(array('route' => 'user_extended', 'module' => 'user', 'controller' => 'edit', 'action' => 'external-photo', 'photo' => $this->photo->getGuid(), 'format' => 'smoothbox'), $this->translate('Make Profile Photo'), array('class' => 'smoothboxOpenPhoto')) ?><?php endif;?><?php endif ?></div>';
sesJqueryObject(optionDataForButton).appendTo('body');
<?php if(!$this->is_ajax){ ?>
	sesJqueryObject(document).click(function(event){
		if(event.target.id == 'parent_container_option' || event.target.id == 'fa-ellipsis-v'){
			if(sesJqueryObject('#parent_container_option').hasClass('active')){
				sesJqueryObject('#parent_container_option').removeClass('active');
				sesJqueryObject('.sesbasic_option_box').hide();	
			}else{
				sesJqueryObject('#parent_container_option').addClass('active');
				sesJqueryObject('.sesbasic_option_box').show();	
		  }
		}else{
			sesJqueryObject('#parent_container_option').removeClass('active');
			sesJqueryObject('.sesbasic_option_box').hide();	
		}
	});
	// on window resize work
	sesJqueryObject(window).resize(function(){
			doResizeForButton();
	});
<?php } ?>
  //Set Width On Image
<?php if(!$this->is_ajax){ ?>
sesJqueryObject(document).on('click','.smoothboxOpenPhoto',function(){
	var url = sesJqueryObject(this).attr('href');
	openURLinSmoothBox(url);
	return false;
});
function sesPhotoRotate(photo_id,rotateAngle){
	var className;
	sesJqueryObject('#ses-rotate-'+rotateAngle).attr('class','icon_loading');
	if(rotateAngle == 90 || rotateAngle == 270){
		if(rotateAngle == 90)
			className = 'sesevent_icon_photos_rotate_ccw';
		else
			className = 'sesevent_icon_photos_rotate_cw';		
		rotatePhotoSes(photo_id,rotateAngle,className);
	}else{
		if(rotateAngle == 'horizontal')
			className = 'sesevent_icon_photos_flip_horizontal';
		else
			className = 'sesevent_icon_photos_flip_vertical';
		flipPhotoSes(photo_id,rotateAngle,className);
	}
		
	return false;
}
function flipPhotoSes(photo_id,rotateAngle,className){
	request = new Request.JSON({
      url : en4.core.baseUrl + 'sesevent/photo/flip',
      data : {
        format : 'json',
        photo_id : photo_id,
        direction : rotateAngle
      },
      onComplete: function(response) {
        // Check status
        if( $type(response) == 'object' &&
            $type(response.status) &&
            response.status == false ) {
						alert(en4.core.language.translate('An error has occurred processing the request. The target may no longer exist.'));
						return;
        } else if( $type(response) != 'object' ||
          !$type(response.status) ) {
         	 alert(en4.core.language.translate('An error has occurred processing the request. The target may no longer exist.'));
           return;
        }
					if(response.status){
							sesJqueryObject('#ses-rotate-'+rotateAngle).attr('class',className);
						if(sesJqueryObject('#media_photo').length>0 && (sesJqueryObject('#ses_media_lightbox_container').css('display') == 'none' || !sesJqueryObject('#ses_media_lightbox_container').length)){
							sesJqueryObject('#media_photo').attr('src',response.href);
						}else{
							sesJqueryObject('#gallery-img').attr('src',response.href);
						}
							return;
					}
      }
    });
    request.send();
		return false;
}
function rotatePhotoSes(photo_id,rotateAngle,className){
	request = new Request.JSON({
      url : en4.core.baseUrl + 'sesevent/photo/rotate',
      data : {
        format : 'json',
        photo_id : photo_id,
        angle : rotateAngle
      },
      onComplete: function(response) {
        // Check status
        if( $type(response) == 'object' &&
            $type(response.status) &&
            response.status == false ) {
 					  alert(en4.core.language.translate('An error has occurred processing the request. The target may no longer exist.'));
					  return;
        } else if( $type(response) != 'object' ||
          !$type(response.status) ) {
           alert(en4.core.language.translate('An error has occurred processing the request. The target may no longer exist.'));
           return;
        }
			 if(response.status){
							sesJqueryObject('#ses-rotate-'+rotateAngle).attr('class',className);
							if(sesJqueryObject('#media_photo').length>0 && (sesJqueryObject('#ses_media_lightbox_container').css('display') == 'none' ||  sesJqueryObject('#ses_media_lightbox_container').length == 0))
								sesJqueryObject('#media_photo').attr('src',response.href);
							else
								sesJqueryObject('#gallery-img').attr('src',response.href);
								return;
					}
      }
    });
    request.send();
		return;	
}
	/*change next previous button click event*/
		sesJqueryObject(document).on('click','#photo_prev',function(){
			changeNextPrevious(this);	
			return false;
		});
		sesJqueryObject(document).on('click','#photo_next',function(){
			changeNextPrevious(this);	
			return false;
		});
	 function changeNextPrevious(thisObject){
			history.pushState(null, null, sesJqueryObject(thisObject).attr('href'));
			var height = sesJqueryObject('#media_photo_div').height();
			var width = sesJqueryObject('#media_photo_div').width();
			sesJqueryObject('#media_photo_div').html('<div class="clear sesbasic_loading_container"></div>');
			sesJqueryObject('.sesbasic_loading_container').css('height',height) ;
			sesJqueryObject('.sesbasic_loading_container').css('width',width) ;
			var correspondingImageData = sesJqueryObject('#sesevent_corresponding_photo').html();
			var photo_id = sesJqueryObject(thisObject).attr('data-url');
			(new Request.HTML({
      method: 'post',
      'url':en4.core.baseUrl + 'widget/index/mod/sesevent/name/photo-view-page/',
      'data': {
        format: 'html',
				 photo_id : photo_id,
				params :'', 
				is_ajax : 1,
				maxHeight:maxHeight
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				if(sesJqueryObject('.sesbasic_option_box').length >0)
					sesJqueryObject('.sesbasic_option_box').remove();
				<?php if(Engine_Api::_()->user()->getViewer()->getIdentity() != ''){ ?>
					sesJqueryObject(optionDataForButton).appendTo('body');
				<?php } ?>
					sesJqueryObject('.layout_sesevent_photo_view_page').html(responseHTML);
					var width = sesJqueryObject('.sesevent_view_photo_container_wrapper').width();
					sesJqueryObject('#media_photo').css('max-width',width+'px');
					sesJqueryObject('#media_photo').css('max-height',maxHeight+'px');
					sesJqueryObject('#sesevent_corresponding_photo').html(correspondingImageData);
					sesJqueryObject('#sesevent_corresponding_photo > a').each(function(index){
					sesJqueryObject(this).removeClass('slideuptovisible');
					if(sesJqueryObject(this).attr('data-url') == photo_id)
						sesJqueryObject(this).addClass('active');
					else
						sesJqueryObject(this).removeClass('active');
						countSlide++;
					});
					sesJqueryObject('#sesevent_corresponding_photo > a').eq(3).addClass('slideuptovisible');
					sesJqueryObject('#sesevent_corresponding_photo').css('width',(countSlide*64)+'px');
      }
    })).send();
    return false;
	}
<?php } ?>
 <?php if(isset($this->criteria) && !$this->is_ajax){ ?>
sesJqueryObject(document).on('click','.sesevent_corresponding_image_album',function(e){
		e.preventDefault();
		if(!sesJqueryObject(this).hasClass('active'))
			changeNextPrevious(this);
});
var countSlide = 0;
function getCorrespondingImg(){
	(new Request.HTML({
      method: 'post',
      'url':en4.core.baseUrl + 'sesevent/photo/corresponding-image/album_id/<?php echo $this->album->album_id; ?>',
      'data': {
        format: 'html',
				is_ajax : 1,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
				if(responseHTML){
					sesJqueryObject('#sesevent_corresponding_photo').html(responseHTML);
					sesJqueryObject('#sesevent_corresponding_photo > a').each(function(index){
						sesJqueryObject(this).removeClass('slideuptovisible');
						if(sesJqueryObject(this).attr('data-url') == "<?php echo $this->photo->photo_id; ?>"){
							sesJqueryObject(this).addClass('active');	
						}
						countSlide++;	
					});
					
					sesJqueryObject('#sesevent_corresponding_photo > a').eq(3).addClass('slideuptovisible');
					sesJqueryObject('#sesevent_corresponding_photo').css('width',(countSlide*64)+'px');
				}
      }
    })).send();	
}
<?php } ?>
<?php if(!$this->is_ajax && isset($this->criteria)){ ?>
sesJqueryObject(document).on('mouseover','#prevSlide',function(e){
	var indexCurrent = 	sesJqueryObject('#sesevent_corresponding_photo > a.slideuptovisible').index();
	if(indexCurrent<4 || indexCurrent == '-1')
		sesJqueryObject('#prevSlide').css('cursor','not-allowed');
	else
		sesJqueryObject('#prevSlide').css('cursor','pointer');
});
sesJqueryObject(document).on('mouseover','#nextSlide',function(e){
	var indexCurrent = 	sesJqueryObject('#sesevent_corresponding_photo > a.slideuptovisible').index();
	if(indexCurrent == (countSlide-1) || indexCurrent == '-1')
		sesJqueryObject('#nextSlide').css('cursor','not-allowed');
	else
		sesJqueryObject('#nextSlide').css('cursor','pointer');
});
sesJqueryObject(document).on('click','#nextSlide',function(){
	var indexCurrent = 	sesJqueryObject('#sesevent_corresponding_photo > a.slideuptovisible').index();
	if((countSlide-1) == indexCurrent || indexCurrent == '-1'){
		// last slide is visible
	}else{
		var slideLeft = (countSlide-1) - indexCurrent;
		var leftAttr = sesJqueryObject('#sesevent_corresponding_photo').css('left').replace('px','');
		leftAttr = leftAttr.replace('-','');		
		if(slideLeft>3){
			leftAttr = parseInt(leftAttr,10);
			sesJqueryObject('#sesevent_corresponding_photo').css('left','-'+(leftAttr+(64*4))+'px');
			sesJqueryObject('#sesevent_corresponding_photo > a').eq(indexCurrent).removeClass('slideuptovisible');
			sesJqueryObject('#sesevent_corresponding_photo > a').eq((indexCurrent+4)).addClass('slideuptovisible');
		}else{
			leftAttr = parseInt(64*slideLeft,10)+parseInt(leftAttr,10);
			sesJqueryObject('#sesevent_corresponding_photo').css('left','-'+leftAttr+'px');
			sesJqueryObject('#sesevent_corresponding_photo > a').eq(indexCurrent).removeClass('slideuptovisible');
			sesJqueryObject('#sesevent_corresponding_photo > a').eq((indexCurrent+slideLeft)).addClass('slideuptovisible');
		}
	}
});
sesJqueryObject(document).on('click','#prevSlide',function(){
	var indexCurrent = 	sesJqueryObject('#sesevent_corresponding_photo > a.slideuptovisible').index();
	var leftAttr = sesJqueryObject('#sesevent_corresponding_photo').css('left').replace('px','');
	leftAttr = leftAttr.replace('-','');
	leftAttr = parseInt(leftAttr,10);
 if(leftAttr == 0 || countSlide < 4 || indexCurrent == '-1'){
	 //first slide
 }else{
	var type = indexCurrent - 3;
	if(typeof type == 'number' && type > 3 ){
		sesJqueryObject('#sesevent_corresponding_photo').css('left','-'+(leftAttr-(64*4))+'px');
		sesJqueryObject('#sesevent_corresponding_photo > a').eq(indexCurrent).removeClass('slideuptovisible');
		sesJqueryObject('#sesevent_corresponding_photo > a').eq((indexCurrent-4)).addClass('slideuptovisible');
	}else{
		var slideLeft = (countSlide-1)-((countSlide-1) - indexCurrent)
		leftAttr = parseInt(leftAttr,10) -  parseInt(64*type,10);
		if(countSlide-1 > 3 || countSlide-1 == 3 || indexCurrent-type < 4)
			var selectedindex = 3;
		else
			var selectedindex = indexCurrent-type;
		sesJqueryObject('#sesevent_corresponding_photo').css('left',leftAttr+'px');
		sesJqueryObject('#sesevent_corresponding_photo > a').eq(indexCurrent).removeClass('slideuptovisible');
		sesJqueryObject('#sesevent_corresponding_photo > a').eq(selectedindex).addClass('slideuptovisible');
	}
 }
	return false;
});
<?php } ?>
 <?php if(isset($this->criteria) && !$this->is_ajax){ ?>
sesJqueryObject(document).ready(function(){
	getCorrespondingImg();	
});
<?php } ?>
</script>