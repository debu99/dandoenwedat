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
<?php if($this->status == 'notStarted'){ ?>
  <div class="sesevent_event_status sesbasic_clearfix open">
    <span class="sesevent_event_status_txt"><?php echo $this->translate('Event not started');?></span>
  </div>
<?php }else if($this->status == 'expire'){ ?>
  <div class="sesevent_event_status sesbasic_clearfix close">
    <span class="sesevent_event_status_txt"><?php echo $this->translate('Event Expired.');?></span>
  </div>
<?php }else{ ?>
  <div class="sesevent_event_status sesbasic_clearfix open">
    <span class="sesevent_event_status_txt"><?php echo $this->translate('Event ongoing.');?></span>
  </div>
<?php } ?>
