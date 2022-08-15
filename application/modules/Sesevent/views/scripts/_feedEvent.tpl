<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _feedEvent.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<span class="feed_attachment_sesevent_event">
<div> 
	<a href="<?php echo $this->event->getHref(); ?>">
  	<img src="<?php echo $this->event->getPhotoUrl('thumb.main'); ?>" alt="<?php echo $this->event->getTitle(); ?>" class="thumb_normal item_photo_sesevent_event  thumb_normal"></a>
  <div>
    <div class="feed_item_link_title"> <a href="<?php echo $this->event->getHref(); ?>" class="ses_tooltip" data-src="<?php echo $this->event->getGuid(); ?>"><?php echo $this->event->getTitle(); ?></a> </div>
    <div class="feed_item_link_desc"> <?php echo Engine_String::strlen(strip_tags($this->event->description)) > 255 ? Engine_String::substr(strip_tags($this->event->description), 0, 255) . '...' : strip_tags($this->event->description); ?> </div>
</div>
</div>
</span>