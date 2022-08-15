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
<?php 
$oldTimeZone = date_default_timezone_get();
date_default_timezone_set($this->viewer()->timezone);
?>
<div class="sesact_tip_box sesact_onthisday_banner sesbasic_clearfix sesbasic_bxs">
	<div class="sesact_onthisday_banner_date">
  	<span class="sesact_onthisday_banner_date_month"><?php echo date('M') ?></span>
  	<span class="sesact_onthisday_banner_date_day"><?php echo date('d'); ?></span>
  </div>  
</div>
<?php date_default_timezone_set($oldTimeZone); ?>