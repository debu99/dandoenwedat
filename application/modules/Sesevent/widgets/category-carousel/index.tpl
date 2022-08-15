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
<div class="slide sesbasic_clearfix sesbasic_bxs sesevent_slick_slider_container <?php echo $this->isfullwidth ? 'isfull_width' : '' ; ?>" style="height:<?php echo $this->height ?>px;display:none;">
  <div class="eventslide_<?php echo $this->identity; ?>">
    <?php foreach( $this->paginator as $item): ?>
    <div class="sesevent_category_carousel_item sesbasic_clearfix sesevent_grid_btns_wrap" style="height:<?php echo $this->height ?>px;width:<?php echo $this->width ?>px;">
      <div class="sesevent_category_carousel_item_thumb" style="height:<?php echo $this->height ?>px;width:<?php echo $this->width ?>px;">       
        <?php
        $href = $item->getHref();
        $imageURL = $item->getPhotoUrl();
        ?>
        <a href="<?php echo $href; ?>" class="sesevent_list_thumb_img">
          <span style="background-image:url(<?php echo $imageURL; ?>);"></span>
        </a>
          <?php if(isset($this->socialshareActive)) {
          $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); ?>
          <div class="sesevent_grid_btns"> 
            <?php if(isset($this->socialshareActive)){ ?>
            
            <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $item, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

            <?php } ?>
          </div>
          <?php } ?>
        </div>
        <div class="sesevent_category_carousel_item_info sesbasic_clearfix centerT">
          <?php if(isset($this->titleActive) ){ ?>
            <span class="sesevent_category_carousel_item_info_title">
              <?php if(strlen($item->getTitle()) > $this->title_truncation_grid){ 
                $title = mb_substr($item->getTitle(),0,($this->title_truncation_grid - 3 )).'...';
                echo $this->htmlLink($item->getHref(),$this->translate($title)) ?>
              <?php }else{ ?>
              	<?php 
              	echo $this->htmlLink($item->getHref(),$this->translate($item->getTitle()) )?>
              <?php } ?>
            </span>
          <?php } ?>
           <?php if(isset($this->descriptionActive) ){ ?>
           <span class="sesevent_category_carousel_item_info_des">
              <?php if(strlen($item->description) > $this->description_truncation_grid){ 
                      $description = mb_substr($item->description,0,($this->description_truncation_grid - 3)).'...';
                      echo $description; ?>
              <?php }else{ ?>
              	<?php echo $item->description ?>
              <?php } ?>
            </span>
          <?php } ?>
          <?php if(isset($this->countEventsActive) ){ ?>
            <span class="sesevent_category_carousel_item_info_stat">
              <?php echo $this->translate(array('%s EVENT', '%s EVENTS',$item->total_event_categories), $this->locale()->toNumber($item->total_event_categories)); ?>
            </span>
          <?php } ?>
        </div>
    	</div>
    <?php endforeach; ?>
  </div>
</div>
<?php
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js');
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/slick/slick.js') ?>
<script type="text/javascript">
  window.addEvent('domready', function () {
		<?php if($this->isfullwidth){ ?>
			var htmlElement = document.getElementsByTagName("body")[0];
			htmlElement.addClass('sesevent_category_carousel');
		<?php } ?>
		<?php if($this->autoplay){ ?> var playAu = true; <?php }else{ ?> var playAu  = false; <?php } ?>
		sesBasicAutoScroll('.eventslide_<?php echo $this->identity; ?>').slick({
			dots: false,
			infinite: true,
			speed: <?php echo $this->speed ?>,
			slidesToShow: 1,
			centerMode: true,
			variableWidth: true,
			autoplay:playAu,
		});
  });
	sesBasicAutoScroll('.eventslide_<?php echo $this->identity; ?>').on('init', function(event, slick, currentSlide, nextSlide){
		sesBasicAutoScroll('.eventslide_<?php echo $this->identity; ?>').parent().show();
		console.log('done');
	});
</script>
