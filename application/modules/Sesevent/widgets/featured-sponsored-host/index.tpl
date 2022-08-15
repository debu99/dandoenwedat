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
<?php if($this->view_type == 'list') : ?>
	<ul class="sesbasic_sidebar_block sesbasic_bxs sesbasic_clearfix">
<?php else : ?>
	<ul class="sesbasic_bxs sesbasic_clearfix sesevent_host_list_container">
<?php endif;?>
  <?php include APPLICATION_PATH . '/application/modules/Sesevent/views/scripts/_sidebarWidgetHostData.tpl'; ?>
</ul>