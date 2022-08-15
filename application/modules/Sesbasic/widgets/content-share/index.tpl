<link href="" type="text/css" />
<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
if($this->codeEnable == 'socialeShare') {
	$baseUrl = $this->layout()->staticBaseUrl;
	$this->headLink()->appendStylesheet($baseUrl . 'application/modules/Sesbasic/externals/styles/social.css');
  $this->headScript()->appendFile($baseUrl . 'application/modules/Sesbasic/externals/scripts/jquery.min.js');
  $this->headScript()->appendFile($baseUrl . 'application/modules/Sesbasic/externals/scripts/jquery-ui.min.js');
  $this->headScript()->appendFile($baseUrl . 'application/modules/Sesbasic/externals/scripts/socialbars.js');
}else { ?>
	<!-- JiaThis Button BEGIN -->
	<script type="text/javascript" src="http://v3.jiathis.com/code/jiathis_r.js?type=left&amp;move=0&amp;btn=l3.gif" charset="utf-8"></script>
	<!-- JiaThis Button END -->
<?php } ?> 
<?php if($this->codeEnable == 'socialeShare') { ?>
	<div id="sesbasic_socialside"></div>
<?php } ?>