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

<ul class="sesevent_cat_iconlist_container sesbasic_clearfix clear sesbasic_bxs <?php echo ($this->alignContent == 'left' ? 'gridleft' : ($this->alignContent == 'right' ? 'gridright' : '')) ?>">	
  <?php foreach( $this->paginator as $item ): ?>
    <li class="sesevent_cat_iconlist" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">
      <a href="<?php echo $item->getHref(); ?>">
        <span class="sesevent_cat_iconlist_icon" style="background-color:<?php echo $item->color ? '#'.$item->color : '#999'; ?>">
        <?php if($item->colored_icon != '' && !is_null($item->colored_icon) && intval($item->colored_icon)){ ?>
          <img src="<?php echo  Engine_Api::_()->storage()->get($item->colored_icon)->getPhotoUrl(); ?>" />
        <?php }else{ 
          //default image
        ?>
        <?php } ?>
        </span>
        <?php if(isset($this->title)){ ?>
        <span class="sesevent_cat_iconlist_title"><?php echo $this->translate($item->category_name); ?></span>
        <?php } ?>
        <?php if(isset($this->countEvents)){ ?>
          <span class="sesevent_cat_iconlist_count"><?php echo $this->translate(array('%s event', '%s events', $item->total_event_categories), $this->locale()->toNumber($item->total_event_categories))?></span>
        <?php } ?>
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