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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/customscrollbar.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/customscrollbar.concat.min.js'); ?>

<?php $baseUrl = $this->layout()->staticBaseUrl; ?>
<?php if(isset($this->bannerImage) && !empty($this->bannerImage)){ ?>
<div class="sesevent_category_cover sesbasic_bxs sesbm">
  <div class="sesevent_category_cover_inner" style="background-image:url(<?php echo Engine_Api::_()->sesevent()->getFileUrl($this->bannerImage); ?>);">
    <div class="sesevent_category_cover_content">
      <div class="sesevent_category_cover_blocks">
        <div class="sesevent_category_cover_block_img">
          <span style="background-image:url(<?php echo Engine_Api::_()->sesevent()->getFileUrl($this->bannerImage); ?>);"></span>
        </div>
        <div class="sesevent_category_cover_block_info">
          <?php if(isset($this->title) && !empty($this->title)): ?>
          <div class="sesevent_category_cover_title"> 
            <?php echo $this->translate($this->title); ?>
          </div>
          <?php endif; ?>
          
          <?php if(isset($this->description) && !empty($this->description)): ?>
            <div class="sesevent_category_cover_des clear sesbasic_custom_scroll">
          <p><?php echo nl2br($this->description);?></p>
            </div>
          <?php endif; ?>
            
          <?php if(count($this->paginator)){ ?>
           <div class="sesevent_category_cover_events">
             <div class="sesevent_category_cover_events_head"><?php echo $this->translate($this->title_pop); ?></div>
         <?php foreach($this->paginator as $eventsCri){ ?>
              <div class="sesevent_thumb sesbasic_animation">
                <a href="<?php echo $eventsCri->getHref(); ?>" data-src="<?php echo $eventsCri->getGuid(); ?>" class="sesevent_thumb_img ses_tooltip">
                  <span class="sesevent_animation" style="background-image:url(<?php echo $eventsCri->getPhotoUrl('thumb.normal'); ?>);"></span>
                  <div class="sesevent_category_cover_events_item_info sesevent_animation">
                    <div class="sesevent_list_title"><?php echo $eventsCri->getTitle(); ?> </div>
                  </div>
                </a>
              </div>
         <?php }  ?>
         </div>
      <?php	}  ?>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php }else{ ?>
<div class="sesevent_browse_cat_top sesbm">
  <?php if(isset($this->title) && !empty($this->title)): ?>
  <div class="sesevent_catview_title"> 
    <?php echo $this->title; ?>
  </div>
  <?php endif; ?>
  <?php if(isset($this->description) && !empty($this->description)): ?>
  <div class="sesevent_catview_des">
    <?php echo $this->description;?>
  </div>
  <?php endif; ?>
</div>
  <?php if(count($this->paginator)){ ?>
    <div class="sesevent_category_cover_events clearfix sesevent_category_top_events sesbasic_bxs">
     <div class="sesevent_categories_events_listing_title clear sesbasic_clearfix">
       <span class="sesevent_category_title"><?php echo $this->title_pop; ?></span>
     </div>
  <?php foreach($this->paginator as $eventsCri){ ?>
      <div class="sesevent_thumb sesbasic_animation">
        <a href="<?php echo $eventsCri->getHref(); ?>" data-src="<?php echo $eventsCri->getGuid(); ?>" class="sesevent_thumb_img ses_tooltip">
          <span class="sesevent_animation" style="background-image:url(<?php echo $eventsCri->getPhotoUrl('thumb.profile'); ?>);"></span>
          <div class="sesevent_category_cover_events_item_info sesevent_animation">
            <div class="sesevent_list_title"><?php echo $eventsCri->getTitle(); ?> </div>
          </div>
        </a>
      </div>
  <?php }  ?>
  </div>
  <?php	}  ?>
<?php } ?>
