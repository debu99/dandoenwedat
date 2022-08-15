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
<div class='sesevent_owner_contact_details sesbasic_clearfix sesbasic_bxs'>
  <ul>
    <?php if( in_array('name',$this->info) && $this->subject->event_contact_name): ?>
      <li class="sesbasic_clearfix sesevent_owner_contact_name">
        <?php echo nl2br($this->subject->event_contact_name) ?>
      </li>
    <?php endif ?>
    <?php if( in_array('email',$this->info) && $this->subject->event_contact_email): ?>
      <li class="sesbasic_clearfix" title='<?php echo $this->translate("Contact Email"); ?>'>
        <i class="fa fa-at sesbasic_text_light"></i>  
        <span><a href='mailto:<?php echo $this->subject->event_contact_email ?>' target="_blank" class="sesbasic_linkinherit"><?php echo $this->subject->event_contact_email ?></a></span>
      </li>
    <?php endif ?>
    <?php if( in_array('phone',$this->info) && $this->subject->event_contact_phone): ?>
      <li class="sesbasic_clearfix" title='<?php echo $this->translate("Contact Phone Number"); ?>'>
        <i class="fa fa-mobile sesbasic_text_light"></i>
        <span><?php echo ($this->subject->event_contact_phone) ?></span>
      </li>
    <?php endif ?>
    <?php if( in_array('facebook',$this->info) && $this->subject->event_contact_facebook): ?>
      <li class="sesbasic_clearfix">
        <i class="fab fa-facebook-f"></i>
        <span><a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($this->subject->event_contact_facebook, PHP_URL_SCHEME) === null ? 'https://' . $this->subject->event_contact_facebook : $this->subject->event_contact_facebook; ?>'><?php echo parse_url($this->subject->event_contact_facebook, PHP_URL_SCHEME) === null ? '' . $this->subject->event_contact_facebook : $this->subject->event_contact_facebook; ?>'</a></span>
      </li>
    <?php endif ?>
    <?php if( in_array('linkedin',$this->info) && $this->subject->event_contact_linkedin): ?>
      <li class="sesbasic_clearfix">
        <i class="fab fa-linkedin"></i>
        <span><a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($this->subject->event_contact_linkedin, PHP_URL_SCHEME) === null ? 'https://' . $this->subject->event_contact_linkedin : $this->subject->event_contact_linkedin; ?>'><?php echo parse_url($this->subject->event_contact_linkedin, PHP_URL_SCHEME) === null ? '' . $this->subject->event_contact_linkedin : $this->subject->event_contact_linkedin; ?></a></span>
      </li>
    <?php endif ?>
    <?php if( in_array('twitter',$this->info) && $this->subject->event_contact_twitter): ?>
      <li class="sesbasic_clearfix">
        <i class="fab fa-twitter"></i>
        <span><a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($this->subject->event_contact_twitter, PHP_URL_SCHEME) === null ? 'https://' . $this->subject->event_contact_twitter : $this->subject->event_contact_twitter; ?>'><?php echo parse_url($this->subject->event_contact_twitter, PHP_URL_SCHEME) === null ? '' . $this->subject->event_contact_twitter : $this->subject->event_contact_twitter; ?></a></span>
      </li>
    <?php endif ?>
    <?php if( in_array('website',$this->info) && $this->subject->event_contact_website): ?>
      <li class="sesbasic_clearfix">
        <i class="fa fa-globe"></i>
        <span><a class="sesbasic_linkinherit" target="_blank" href='<?php echo parse_url($this->subject->event_contact_website, PHP_URL_SCHEME) === null ? 'http://' . $this->subject->event_contact_website : $this->subject->event_contact_website; ?>' title='<?php echo $this->translate("Website URL"); ?>'><?php echo parse_url($this->subject->event_contact_website, PHP_URL_SCHEME) === null ? '' . $this->subject->event_contact_website : $this->subject->event_contact_website; ?></a></span>
      </li>
    <?php endif ?>
    <li class="sesevent_owner_contact_social" style="display: none;">
      <?php if( in_array('facebook',$this->info) && $this->subject->event_contact_facebook): ?>
        <a target="_blank" href='<?php echo parse_url($this->subject->event_contact_facebook, PHP_URL_SCHEME) === null ? 'https://' . $this->subject->event_contact_facebook : $this->subject->event_contact_facebook; ?>' title='<?php echo $this->translate("Facebook URL"); ?>'><i class="fab fa-facebook-square"></i></a>
      <?php endif ?>

      <?php if( in_array('linkedin',$this->info) && $this->subject->event_contact_linkedin): ?>
        <a target="_blank" href='<?php echo parse_url($this->subject->event_contact_linkedin, PHP_URL_SCHEME) === null ? 'https://' . $this->subject->event_contact_linkedin : $this->subject->event_contact_linkedin; ?>' title='<?php echo $this->translate("Linkedin URL"); ?>'><i class="fab fa-linkedin"></i></a>
      <?php endif ?>
      <?php if( in_array('twitter',$this->info) && $this->subject->event_contact_twitter): ?>
        <a target="_blank" href='<?php echo parse_url($this->subject->event_contact_twitter, PHP_URL_SCHEME) === null ? 'https://' . $this->subject->event_contact_twitter : $this->subject->event_contact_twitter; ?>' title='<?php echo $this->translate("Twitter URL"); ?>'><i class="fab fa-twitter"></i></a>
      <?php endif ?>
      <?php if( in_array('website',$this->info) && $this->subject->event_contact_website): ?>
        <a target="_blank" href='<?php echo parse_url($this->subject->event_contact_website, PHP_URL_SCHEME) === null ? 'http://' . $this->subject->event_contact_website : $this->subject->event_contact_website; ?>' title='<?php echo $this->translate("Website URL"); ?>'><i class="fa fa-globe"></i></a>
      <?php endif ?>
    </li>
  </ul>
</div>
<script type="application/javascript">
var tabId_contactinfo = <?php echo $this->identity; ?>;
window.addEvent('domready', function() {
	tabContainerHrefSesbasic(tabId_contactinfo);	
});
</script>