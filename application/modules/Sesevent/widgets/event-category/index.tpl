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
<div class="sesbasic_clearfix">
  <ul class="sesevent_category_grid_listing sesbasic_clearfix clear sesbasic_bxs <?php echo ($this->alignContent == 'left' ? 'gridleft' : ($this->alignContent == 'right' ? 'gridright' : '')) ?>">	
    <li class="sesevent_catbase_list_head sesbm" style="display:none;"><?php echo $this->translate("All Categories"); ?></li>
    <?php foreach( $this->paginator as $item ): ?>
    <li class="sesevent_category_grid sesbm" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">
      <a href="<?php echo $item->getHref(); ?>">
        <div class="sesevent_category_grid_img">
          <?php if($item->thumbnail != '' && !is_null($item->thumbnail) && intval($item->thumbnail)){ ?>
          <span class="sesevent_animation" style="background-image:url(<?php echo  Engine_Api::_()->storage()->get($item->thumbnail)->getPhotoUrl('thumb.thumb'); ?>);"></span>
          <?php } ?>
        </div>
        <div class="sesevent_category_grid_overlay sesevent_animation"></div>
        <div class="sesevent_category_grid_info">
          <div>
            <div class="sesevent_category_grid_details">
              <?php if(isset($this->icon) && $item->cat_icon != '' && !is_null($item->cat_icon) && intval($item->cat_icon)){ ?>
              <img src="<?php echo  Engine_Api::_()->storage()->get($item->cat_icon)->getPhotoUrl('thumb.icon'); ?>" />
              <?php } ?>
              <?php if(isset($this->title)){ ?>
              <span><?php echo $this->translate($item->category_name); ?></span>
              <?php } ?>
              <?php if(isset($this->countEvents)){ ?>
              <span class="sesevent_category_grid_stats"><?php echo $this->translate(array('%s event', '%s events', $item->total_event_categories), $this->locale()->toNumber($item->total_event_categories))?></span>
              <?php } ?>
            </div>
          </div>
        </div>
      </a>
    </li>
    <?php endforeach; ?>
    <?php  if(  count($this->paginator) == 0){  ?>
    <div class="tip">
      <span>
        <?php echo $this->translate('No categories found.');?>
      </span>
    </div>
    <?php } ?>
  </ul>
</div>