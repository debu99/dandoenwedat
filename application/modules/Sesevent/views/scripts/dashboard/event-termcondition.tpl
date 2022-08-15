<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: event-termcondition.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="sesevent_event_terms_container">
  <div class="sesevent_event_terms_head"><?php echo $this->translate("Terms and Conditions");?></div>
  <div class="sesbasic_html_block sesevent_event_terms clearfix">
    <?php
    echo $this->event->custom_term_condition;
    ?>
  </div>
</div>