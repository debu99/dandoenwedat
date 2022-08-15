<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/style_discussion.css'); ?>
<div class="layout_middle">
	<div class="generic_layout_container layout_core_content">
    <h2>
      <?php echo $this->event->__toString()." ".$this->translate("&#187; Discussions") ?>
    </h2>
    <?php echo $this->form->render($this) ?>
  </div>
</div>  