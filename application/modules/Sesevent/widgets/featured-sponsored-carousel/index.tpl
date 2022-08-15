
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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js')
//
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/mootools/mootools-more-1.4.0.1-full-compat-' . (APPLICATION_ENV == 'development' ? 'nc' : 'yc') . '.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/PeriodicalExecuter.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/Carousel.js')
  ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/Carousel.Extra.js');

$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/carousel.css');
$this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css');
?>
<style>
  #eventslide_<?php echo $this->identity; ?> {
    position: relative;
    height: <?php echo $this->height ?>px;
    overflow: hidden;
  }

  #eventslide_<?php echo $this->identity; ?>.sesevent_grid_out {
    height: <?php echo $this->height ?>px;
  }
</style>

<div class="slide sesbasic_carousel_wrapper sesbm clearfix sesbasic_bxs <?php if ($this->viewType == 'horizontal') : ?>sesbasic_carousel_h_wrapper<?php else : ?>sesbasic_carousel_v_wrapper <?php endif; ?>">
  <div id="eventslide_<?php echo $this->identity; ?>">
    <?php foreach ($this->paginator as $item) : //joinedmember 
    ?>
      <div class="sesevent_grid_<?php echo $this->gridInsideOutside; ?> sesbasic_clearfix sesbasic_bxs sesevent_grid_btns_wrap sesae-i-<?php echo $this->mouseOver; ?>" style="width:<?php echo $this->width ?>px;">
        <div class="sesevent_list_thumb" style="height:<?php echo $this->imageheight ?>px;">
          <?php //echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.normal')) 
          ?>
          <?php
          $href = $item->getHref();
          $imageURL = $item->getPhotoUrl('thumb.profile');
          ?>
          <a href="<?php echo $href; ?>" class="sesevent_list_thumb_img">
            <span style="background-image:url(<?php echo $imageURL; ?>);"></span>
          </a>
          <?php if (isset($this->featuredLabelActive) || isset($this->sponsoredLabelActive)) { ?>
            <p class="sesevent_labels">
              <?php if (isset($this->featuredLabelActive) && $item->featured) { ?>
                <span class="sesevent_label_featured">FEATURED</span>
              <?php } ?>
              <?php if (isset($this->sponsoredLabelActive) && $item->sponsored) { ?>
                <span class="sesevent_label_sponsored">SPONSORED</span>
              <?php } ?>
            </p>
          <?php } ?>
          <?php if (isset($this->socialSharingActive) || isset($this->likeButtonActive) || isset($this->favouriteButtonActive) || isset($this->listButtonActive)) {
            $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref()); ?>
            <div class="sesevent_grid_btns">
              <?php if (isset($this->socialSharingActive)) { ?>
                <?php echo $this->partial('_socialShareIcons.tpl', 'sesbasic', array('resource' => $item, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

              <?php }
              $itemtype = 'sesevent_event';
              $getId = 'event_id';
              $canComment =  $item->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'comment');
              if (isset($this->likeButtonActive) && $canComment) {
              ?>
                <!--Like Button-->
                <?php $LikeStatus = Engine_Api::_()->sesevent()->getLikeStatusEvent($item->$getId, $item->getType()); ?>
                <a href="javascript:;" data-url="<?php echo $item->$getId; ?>" class="sesbasic_icon_btn sesbasic_icon_btn_count sesbasic_icon_like_btn sesevent_like_<?php echo $itemtype; ?> <?php echo ($LikeStatus) ? 'button_active' : ''; ?>"> <i class="fa fa-thumbs-up"></i><span><?php echo $item->like_count; ?></span></a>
              <?php }
              if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity() != 0 &&  isset($item->favourite_count)) {
                $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type' => 'sesevent_event', 'resource_id' => $item->event_id));
                $favClass = ($favStatus)  ? 'button_active' : '';
                echo "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_event_" . $item->event_id . " sesbasic_icon_fav_btn sesevent_favourite_sesevent_event " . $favClass . "' data-url=\"$item->event_id\"><i class='fa fa-heart'></i><span>$item->favourite_count</span></a>";
              }
              if (isset($this->listButtonActive) && Engine_Api::_()->user()->getViewer()->getIdentity()) {
                echo '<a href="javascript:;" onclick="opensmoothboxurl(' . "'" . $this->url(array('action' => 'add', 'module' => 'sesevent', 'controller' => 'list', 'event_id' => $item->event_id), 'default', true) . "'" . ');return false;"	class="sesbasic_icon_btn  sesevent_add_list"  title="' . $this->translate('Add To List') . '" data-url="' . $item->event_id . '"><i class="fa fa-plus"></i></a>';
              } ?>
            </div>
          <?php } ?>
        </div>

        <?php if (isset($this->titleActive)) { ?>
          <div class="sesevent_grid_in_title_show sesevent_animation">
            <?php if (strlen($item->getTitle()) > $this->title_truncation_grid) {
              $title = mb_substr($item->getTitle(), 0, ($this->title_truncation_grid - 3)) . '...';
              echo $this->htmlLink($item->getHref(), $title, array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
            <?php } else { ?>
              <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
            <?php } ?>
            <?php if (isset($this->verifiedLabelActive) && $item->verified == 1) { ?>
              <i class="sesevent_verified fa fa-check-square" title="Verified"></i>
            <?php } ?>
          </div>
        <?php } ?>

        <div class="sesevent_list_info sesbasic_clearfix">
          <?php if (isset($this->titleActive)) { ?>
            <div class="sesevent_list_title">
              <?php if (strlen($item->getTitle()) > $this->title_truncation_grid) {
                $title = mb_substr($item->getTitle(), 0, ($this->title_truncation_grid - 3)) . '...';
                echo $this->htmlLink($item->getHref(), $title, array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
              <?php } else { ?>
                <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'ses_tooltip', 'data-src' => $item->getGuid())) ?>
              <?php } ?>
              <?php if (isset($this->verifiedLabelActive) && $item->verified == 1) { ?>
                <i class="sesevent_verified fa fa-check-square" title="Verified"></i>
              <?php } ?>
            </div>
          <?php } ?>
          <?php
          if (isset($this->hostActive)) {
            $host = Engine_Api::_()->getItem('sesevent_host', $item->host);
            echo '<div class="sesevent_list_stats"><span><i class="fa fa-male sesbasic_text_light"></i>' . $this->translate("Hosted By ") . $this->htmlLink($host->getHref(), $host->getTitle()) . '</span></div>';
          }
          ?>
          <?php if (isset($this->byActive)) { ?>
            <?php $owner = $item->getOwner(); ?>
            <div class="sesevent_list_stats">
              <span>
                <i class="fa fa-user sesbasic_text_light" title="<?php echo $this->translate('By:'); ?>"></i>
                <?php echo $this->htmlLink($owner->getHref(), $owner->getTitle()) ?>
              </span>
            </div>
          <?php } ?>
          <div class="sesevent_list_stats sesevent_list_time">
            <span class="widthfull">
              <i class="far fa-calendar-alt sesbasic_text_light" title="<?php echo $this->translate('Start & End Date'); ?>"></i>
              <?php echo $this->eventStartEndDates($item) ?>
            </span>
          </div>
          <?php if ($item->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.enable.location', 1)) : ?>
            <div class="sesevent_list_stats sesevent_list_location">
              <span class="widthfull">
                <i class="fas fa-map-marker-alt sesbasic_text_light" title="<?php echo $this->translate('Location'); ?>"></i>
                <span title="<?php echo $item->location; ?>">
                  <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 1)) { ?>
                    <a href="<?php echo $this->url(array('resource_id' => $item->event_id, 'resource_type' => 'sesevent_event', 'action' => 'get-direction'), 'sesbasic_get_direction', true); ?>" class="openSmoothbox"><?php echo $item->location ?></a>
                  <?php } else { ?>
                    <?php echo $item->location; ?>
                  <?php } ?>
                </span>
              </span>
            </div>
          <?php endif; ?>

          <?php if (isset($this->categoryActive)) { ?>
            <?php if ($item->category_id != '' && intval($item->category_id) && !is_null($item->category_id)) {
              $categoryItem = Engine_Api::_()->getItem('sesevent_category', $item->category_id);
            ?>
              <div class="sesevent_list_stats">
                <span>
                  <i class="fa fa-folder-open"></i>
                  <a href="<?php echo $categoryItem->getHref(); ?>"><?php echo $categoryItem->category_name; ?></a>
                  <?php $subcategory = Engine_Api::_()->getItem('sesevent_category', $item->subcat_id); ?>
                  <?php if ($subcategory && $item->subcat_id) { ?>
                    &nbsp;&raquo;&nbsp;<a href="<?php echo $subcategory->getHref(); ?>"><?php echo $subcategory->category_name; ?></a>
                  <?php } ?>
                  <?php $subsubcategory = Engine_Api::_()->getItem('sesevent_category', $item->subsubcat_id); ?>
                  <?php if ($subsubcategory && $item->subsubcat_id) { ?>
                    &nbsp;&raquo;&nbsp;<a href="<?php echo $subsubcategory->getHref(); ?>"><?php echo $subsubcategory->category_name; ?></a>
                  <?php } ?>
                </span>
              </div>
            <?php } ?>
          <?php }
          if (isset($this->joinedcountActive)) {
            $guestCountStats = $item->joinedmember ? $item->joinedmember : 0;
            $guestCount = $this->translate(array('%s guest', '%s guests', $guestCountStats), $this->locale()->toNumber($guestCountStats));
            echo  "<div title=\"$guestCount\" class=\"sesevent_list_stats\"><span><i class=\"fas fa-users sesbasic_text_light\"></i>$guestCount</span></div>";
          }
          ?>
          <div class="sesevent_list_stats">
            <?php if (isset($this->likeActive) && isset($item->like_count)) { ?>
              <span title="<?php echo $this->translate(array('%s like', '%s likes', $item->like_count), $this->locale()->toNumber($item->like_count)); ?>"><i class="fa fa-thumbs-up"></i><?php echo $item->like_count; ?></span>
            <?php } ?>
            <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.allowfavourite', 1) && isset($this->favouriteActive) && isset($item->favourite_count)) { ?>
              <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $item->favourite_count), $this->locale()->toNumber($item->favourite_count)); ?>"><i class="fa fa-heart"></i><?php echo $item->favourite_count; ?></span>
            <?php } ?>
            <?php if (isset($this->commentActive) && isset($item->comment_count)) { ?>
              <span title="<?php echo $this->translate(array('%s comment', '%s comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?>"><i class="fa fa-comment"></i><?php echo $item->comment_count; ?></span>
            <?php } ?>
            <?php if (isset($this->viewActive) && isset($item->view_count)) { ?>
              <span title="<?php echo $this->translate(array('%s view', '%s views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?>"><i class="fa fa-eye"></i><?php echo $item->view_count; ?></span>
            <?php } ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if ($this->viewType == 'horizontal') : ?>
    <div class="tabs_<?php echo $this->identity; ?> sesbasic_carousel_nav">
      <a class="sesbasic_carousel_nav_pre" href="#page-p"><i class="fa fa-caret-left"></i></a>
      <a class="sesbasic_carousel_nav_nxt" href="#page-p"><i class="fa fa-caret-right"></i></a>
    </div>
  <?php else : ?>
    <div class="tabs_<?php echo $this->identity; ?> sesbasic_carousel_nav">
      <a class="sesbasic_carousel_nav_pre" href="#page-p"><i class="fa fa-caret-up"></i></a>
      <a class="sesbasic_carousel_nav_nxt" href="#page-p"><i class="fa fa-caret-down"></i></a>
    </div>
  <?php endif; ?>

</div>
<script type="text/javascript">
  window.addEvent('domready', function() {
    var duration = 150,
      div = document.getElement('div.tabs_<?php echo $this->identity; ?>');
    links = div.getElements('a'),
      carousel = new Carousel.Extra({
        activeClass: 'selected',
        container: 'eventslide_<?php echo $this->identity; ?>',
        circular: true,
        current: 1,
        previous: links.shift(),
        next: links.pop(),
        tabs: links,
        mode: '<?php echo $this->viewType; ?>',
        fx: {
          duration: duration
        }
      })
  });
</script>