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
<?php if(!$this->is_ajax){ 
if(isset($this->docActive)){
	$imageURL = $this->lists->getPhotoUrl();
	if(strpos($this->lists->getPhotoUrl(),'http') === false)
          	$imageURL = (!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"] == 'on')) ? "https://" : "http://". $_SERVER['HTTP_HOST'].$this->lists->getPhotoUrl();
  $this->doctype('XHTML1_RDFA');
  $this->headMeta()->setProperty('og:title', strip_tags($this->lists->getTitle()));
  $this->headMeta()->setProperty('og:description', strip_tags($this->lists->getDescription()));
  $this->headMeta()->setProperty('og:image',$imageURL);
  $this->headMeta()->setProperty('twitter:title', strip_tags($this->lists->getTitle()));
  $this->headMeta()->setProperty('twitter:description', strip_tags($this->lists->getDescription()));
}
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="clear">
<?php $list = $this->lists; ?>
  <div class="sesevent_clist_view sesbasic_clearfix sesbasic_bxs sesbm">
    <div class="sesevent_clist_view_thumb sesevent_grid_btns_wrap">
    	<?php echo $this->itemPhoto($list, 'thumb.profile'); ?>
      <div class="sesevent_grid_btns">
       <?php
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $this->lists->getHref()); ?>
					<?php if(!empty($this->informationList) && in_array('socialSharingList', $this->informationList)){ ?>
            
            <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $this->lists, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

            <?php } 
            if(Engine_Api::_()->user()->getViewer()->getIdentity() != 0 ){
              $this->liststype = 'sesevent_list';
              $getId = 'list_id';                                
              $canComment =  true;
              if(!empty($this->informationList) && in_array('likeButtonList', $this->informationList) && $canComment){
            ?>
          <!--Like Button-->
          <?php $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($this->lists->$getId,$this->lists->getType()); ?>
            <a href="javascript:;" data-url="<?php echo $this->lists->$getId ; ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_<?php echo $this->liststype; ?> <?php echo ($LikeStatus) ? 'button_active' : '' ; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $this->lists->like_count; ?></span></a>
            <?php } ?>
             <?php if(!empty($this->informationList) && in_array('favouriteButtonList', $this->informationList) && isset($this->lists->favourite_count)){ ?>
            
            <?php $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>$this->liststype,'resource_id'=>$this->lists->$getId)); ?>
            <a href="javascript:;" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_fav_btn sesevent_favourite_<?php echo $this->liststype; ?> <?php echo ($favStatus)  ? 'button_active' : '' ?>"  data-url="<?php echo $this->lists->$getId ; ?>"><i class="fa fa-heart"></i><span><?php echo $this->lists->favourite_count; ?></span></a>
          <?php } ?>
        <?php  } ?>
      </div>
      
    </div>
    <div class="sesevent_clist_view_info">
      <div class="sesevent_clist_view_title">
        <?php echo $list->getTitle() ?>
      </div>
      <?php if(!empty($this->informationList) && in_array('postedby',  $this->informationList)): ?>
      	<div class="sesevent_clist_view_stats sesbasic_text_light">
          <?php echo $this->translate('Created %s by ', $this->timestamp($this->lists->creation_date)) ?>
          <?php echo $this->htmlLink($list->getOwner(), $list->getOwner()->getTitle()) ?>
      	</div>
       <?php endif; ?>   
        <div class="sesevent_clist_view_stats sesevent_list_stats sesbasic_text_light sesbasic_clearfix"> 
        	<?php if(!empty($this->informationList) && in_array('viewCountList',  $this->informationList)): ?>
          	<span><i class="fa fa-eye"></i><?php echo $this->translate(array('%s view', '%s views', $this->lists->view_count), $this->locale()->toNumber($this->lists->view_count)) ?></span>
        	<?php endif; ?>
          <?php if(!empty($this->informationList) && in_array('favouriteCountList', $this->informationList)): ?>
          	<span><i class="fa fa-heart"></i><?php echo $this->translate(array('%s favourite', '%s favourites', $this->lists->favourite_count), $this->locale()->toNumber($this->lists->favourite_count)) ?></span>
      		<?php endif; ?>
      		<?php if(!empty($this->informationList) && in_array('likeCountList', $this->informationList)): ?>    
	          <span><i class="fa fa-thumbs-up"></i><?php echo $this->translate(array('%s like', '%s likes', $this->lists->like_count), $this->locale()->toNumber($this->lists->like_count)) ?></span>  
      		<?php endif; ?>
      		<?php if(!empty($this->informationList) && in_array('eventCountList', $this->informationList)): ?>    
	          <span><i class="far fa-calendar-alt"></i><?php echo $this->translate(array('%s event', '%s events', $this->lists->event_count), $this->locale()->toNumber($this->lists->event_count)) ?></span>  
      		<?php endif; ?>
        </div>
      <?php if(!empty($this->informationList) && in_array('descriptionList',  $this->informationList) && $list->description): ?>
        <div class="sesevent_clist_view_des clear floatL">
          <?php echo (nl2br($list->description)); ?>
        </div>
      <?php endif; ?>
     	<div class="sesevent_list_btns sesevent_clist_view_options floatL clear"> 
        <?php $viewer = Engine_Api::_()->user()->getViewer(); ?>
          <?php if($this->viewer_id): ?>
            <?php if(!empty($this->informationList) && in_array('shareList', $this->informationList)): ?>
              <a href="<?php echo $this->url(array('module'=>'activity', 'controller'=>'index', 'action'=>'share', 'route'=>'default', 'type'=>'sesevent_list', 'id' => $this->lists->getIdentity(), 'format' => 'smoothbox'),'default',true) ?>" class="smoothbox sesbasic_button" title="<?php echo $this->translate("Share") ?>">
              <i class="fa fa-share"></i>
              <span><?php echo $this->translate("Share") ?></span>
              </a>
            <?php endif; ?>
            
          <?php if(!empty($this->informationList) && in_array('reportList',  $this->informationList)): ?>
            <a href="<?php echo $this->url(array('module'=>'core', 'controller'=>'report', 'action'=>'create', 'route'=>'default', 'subject'=> $this->lists->getGuid(), 'format' => 'smoothbox'),'default',true) ?>" class="smoothbox sesbasic_button" title="<?php echo $this->translate("Report") ?>">
            <i class="fa fa-flag"></i>
            <span><?php echo $this->translate("Report") ?></span>
            </a>
          <?php endif; ?>
            <?php if($viewer->getIdentity() == $this->lists->owner_id || $viewer->level_id == 1 ): ?>
            <a href="<?php echo $this->url(array('action'=>'edit', 'list_id'=>$this->lists->getIdentity(),'slug'=>$this->lists->getSlug()),'sesevent_list_view',true) ?>" class="sesbasic_button" title="<?php echo $this->translate("Edit List") ?>">
            <i class="fa fa-edit"></i>
            <span><?php echo $this->translate("Edit List") ?></span>
            </a>
               <a href="<?php echo $this->url(array('action'=>'delete', 'list_id'=>$this->lists->getIdentity(),'slug'=>$this->lists->getSlug(),  'format' => 'smoothbox'),'sesevent_list_view',true) ?>" class="sesbasic_button smoothbox" title="<?php echo $this->translate("Delete List") ?>">
            <i class="fa fa-trash"></i>
            <span><?php echo $this->translate("Delete List") ?></span>
            </a>
         <?php endif; ?>
       <?php endif; ?>
    	</div>
    </div>
  </div>
<?php } ?>
</div>