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

<div id='link-blog'>
  <h2>
    <a href="<?php echo $this->url(array('action' => 'link-blog', 'event_id' => $this->event->event_id ), 'sesevent_general', 'true');?>" class="smoothbox"><?php echo $this->translate('Link to Blog');?></a>
  </h2>
</div>