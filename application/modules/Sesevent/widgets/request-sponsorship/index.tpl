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
<div class="sesevent_request_button">
	<a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'sponsorship-request'), 'sesevent_sponsorship', true); ?>" id="sesevent_request_sponsorship"  class="openSmoothbox sesbasic_link_btn "><?php echo $this->translate("Request Sponsorship"); ?></a>
</div>