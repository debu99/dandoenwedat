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
<?php $host = $this->results; 
$sitehostredirect = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1); 
if($sitehostredirect && $host->user_id) {
  $user = Engine_Api::_()->getItem('user', $host->user_id);
  $href = $user->getHref();
} else {
  $href = $host->getHref();
}
$followCount = Engine_Api::_()->getDbtable('follows', 'sesevent')->getFollowCount(array('host_id' => $host->host_id, 'type' => $host->type));
$hostEventCount = Engine_Api::_()->getDbtable('events', 'sesevent')->getHostEventCounts(array('host_id' => $host->host_id, 'type' => $host->type));
?>
<ul class="sesbasic_bxs sesbasic_clearfix sesevent_host_list_container">
	<li class="sesevent_host_list sesevent_grid_btns_wrap sesbasic_clearfix <?php if($this->contentInsideOutside == 'in'): ?> sesevent_host_list_in <?php else: ?> sesevent_host_list_out <?php endif; ?> <?php if($this->mouseOver): ?> sesae-i-over <?php endif; ?>" style="width:<?php echo is_numeric($this->width) ? $this->width.'px' : $this->width ?>;">
  	<div class="sesevent_host_list_thumb" style="height:<?php echo is_numeric($this->height) ? $this->height.'px' : $this->height ?>;">
      <?php
      $href = $href;
      $imageURL = $host->getPhotoUrl('thumb.main');
      ?>
      <a href="<?php echo $href; ?>" class="sesevent_host_list_thumb_img">
        <span style="background-image:url(<?php echo $imageURL; ?>);"></span>
      </a>
      <a href="<?php echo $href; ?>" class="sesevent_host_list_overlay"></a>
      <?php  if($this->content_show):   ?>
        <?php if(in_array('featured', $this->content_show) || in_array('sponsored', $this->content_show)):   ?>
          <p class="sesevent_labels">
            <?php if($host->featured && in_array('featured', $this->content_show)): ?>
              <span class="sesevent_label sesevent_label_featured"><?php echo $this->translate("FEATURED"); ?></span>
            <?php endif; ?>
            <?php if($host->sponsored && in_array('sponsored', $this->content_show)): ?>
              <span class="sesevent_label sesevent_label_sponsored"><?php echo $this->translate("SPONSORED"); ?></span>
            <?php endif; ?>
          </p>
          <?php if($host->verified && in_array('verified', $this->content_show)): ?>
            <div class="sesevent_verified_label" title="<?php echo $this->translate('VERIFIED'); ?>"><i class="fa fa-check"></i></div>
          <?php endif; ?>
        <?php endif; ?>
      <?php endif; ?>
      <?php if(in_array('socialSharing', $this->content_show) || in_array('favouriteButton', $this->content_show)) {
      $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) &&  strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $href); ?>
        <div class="sesevent_grid_btns"> 
          <?php if(in_array('socialSharing', $this->content_show)){ ?>
          
          <?php  echo $this->partial('_socialShareIcons.tpl','sesbasic',array('resource' => $host, 'socialshare_enable_plusicon' => $this->socialshare_enable_plusicon, 'socialshare_icon_limit' => $this->socialshare_icon_limit)); ?>

          <?php } 
          $itemtype = 'sesevent_host';
          $getId = 'host_id';
          ?>
          <?php
            if(in_array('favouriteButton', $this->content_show)) {
              $favStatus = Engine_Api::_()->getDbtable('favourites', 'sesevent')->isFavourite(array('resource_type'=>'sesevent_host','resource_id'=>$host->host_id));
              $favClass = ($favStatus)  ? 'button_active' : '';
              $shareOptions = "<a href='javascript:;' class='sesbasic_icon_btn sesbasic_icon_btn_count sesevent_favourite_sesevent_host_". $host->host_id." sesbasic_icon_fav_btn sesevent_favourite_sesevent_host ".$favClass ."' data-url=\"$host->host_id\"><i class='fa fa-heart'></i><span>$host->favourite_count</span></a>";
              echo $shareOptions;
            }
          ?>
        </div>
      <?php } ?>
    </div>
    <?php //if(!empty($this->content_show) && in_array('displayname', $this->content_show)): ?>
      <div class="sesevent_host_list_in_show_title sesevent_animation">
        <?php echo $this->htmlLink($href, $host->host_name, array('title' => $host->host_name)) ?>
      </div>
    <?php //endif; ?>
    <?php if($this->content_show): ?>
      <div class="sesevent_host_list_info sesbasic_clearfix">
        <?php //if(!empty($this->content_show) && in_array('displayname', $this->content_show)): ?>
          <div class='sesevent_host_list_name'>
           	<?php echo $this->htmlLink($href, $host->host_name, array('title' => $host->host_name)) ?>
          </div>
        <?php //endif; ?>
	      <div class="sesevent_host_list_stats sesevent_list_stats">
	        <?php if(in_array('hostEventCount', $this->content_show) && isset($hostEventCount)) { ?>
		        <span title="<?php echo $this->translate(array('%s event host', '%s event hosted', $hostEventCount), $this->locale()->toNumber($hostEventCount))?>"><i class="far fa-calendar-alt sesbasic_text_light"></i><?php echo $hostEventCount; ?></span>
	        <?php } ?>
	        <?php if(in_array('follow', $this->content_show) && isset($followCount)) { ?>
		        <span title="<?php echo $this->translate(array('%s follow', '%s followed', $followCount), $this->locale()->toNumber($followCount))?>"><i class="fas fa-users sesbasic_text_light"></i><?php echo $followCount; ?></span>
	        <?php } ?>
	        <?php if(in_array('view', $this->content_show) && isset($host->view_count)) { ?>
		        <span title="<?php echo $this->translate(array('%s view', '%s views', $host->view_count), $this->locale()->toNumber($host->view_count))?>"><i class="fa fa-eye sesbasic_text_light"></i><?php echo $host->view_count; ?></span>
	        <?php } ?>
	        <?php if(in_array('favourite', $this->content_show) && isset($host->favourite_count)) { ?>
		        <span title="<?php echo $this->translate(array('%s favourite', '%s favourites', $host->favourite_count), $this->locale()->toNumber($host->favourite_count))?>"><i class="fa fa-heart sesbasic_text_light"></i><?php echo $host->favourite_count; ?></span>
	        <?php } ?>
	      </div>
      </div>
    <?php endif; ?>
  </li>
</ul>