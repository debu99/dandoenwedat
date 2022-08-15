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
<div class="sesbasic_breadcrumb">
  <a href="<?php echo $this->url(array('action' => 'home'), 'sesevent_general'); ?>"><?php echo $this->translate("Events Home"); ?></a>&nbsp;&raquo;
  <a href="<?php echo $this->url(array('action' => 'browse'), 'sesevent_general'); ?>"><?php echo $this->translate("Browse Events"); ?></a>&nbsp;&raquo;
<?php if($this->subject->getType() == 'sesevent_album' || $this->subject->getType() == 'sesevent_photo'){ 
	$event =  Engine_Api::_()->getItem('sesevent_event', $this->subject->event_id);	
?>
	<a href="<?php echo $event->getHref(); ?>"><?php echo $event->getTitle(); ?></a>&nbsp;&raquo;
  <?php if($this->subject->getType() == 'sesevent_photo'){ 
  	$album =  Engine_Api::_()->getItem('sesevent_album', $this->subject->album_id);	
  ?>
  	<a href="<?php echo $album->getHref(); ?>"><?php echo $album->getTitle(); ?></a>&nbsp;&raquo;
  <?php } ?>
<?php } ?>
<?php if($this->subject->getType() == 'sesevent_list'){ ?>
<a href="<?php echo $this->url(array('action' => 'browse'), 'sesevent_list'); ?>"><?php echo $this->translate("Browse Lists"); ?></a>&nbsp;&raquo;
<?php } ?>
<?php if($this->subject->getType() == 'sesevent_host'){ ?>
<a href="<?php echo $this->url(array('action' => 'browse-host'), 'sesevent_general'); ?>"><?php echo $this->translate("Browse Hosts"); ?></a>&nbsp;&raquo;
<?php } ?>
  <?php echo !$this->subject->getTitle() ? $this->translate("Untitled"): $this->subject->getTitle(); ?>
</div>
