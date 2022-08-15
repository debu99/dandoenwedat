<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div onclick="javascript:processClick(<?php echo $this->campaign->adcampaign_id.", ".$this->ad->ad_id?>)" style="text-align:center;">
  <?php echo $this->ad->html_code; ?>
</div>
