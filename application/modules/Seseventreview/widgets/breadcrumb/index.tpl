<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<div class="sesbasic_breadcrumb">
  <a href="<?php echo $this->content_item->getHref(); ?>"><?php echo $this->content_item->getTitle(); ?></a>&nbsp;&raquo;
  <?php echo $this->review->getTitle(); ?>
</div>
