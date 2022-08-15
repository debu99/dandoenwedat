<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesfeedgif
 * @package    Sesfeedgif
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: dismiss_message.tpl  2017-12-06 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<script type="text/javascript">
function dismiss(coockiesValue) {
  var d = new Date();
  d.setTime(d.getTime()+(365*24*60*60*1000));
  var expires = "expires="+d.toGMTString();
  document.cookie = coockiesValue + "=" + 1 + "; " + expires;
    $(coockiesValue).style.display = 'none';
}
</script>
<?php if( !isset($_COOKIE["dismiss_developer"])): ?>
  <div id="dismiss_developer" class="tip">
    <span>
      <?php echo "Are you happy with our services, products and work? If Yes, then please review our Expert profile at <a href='https://www.socialengine.com/experts/profile/socialenginesolutions' target='_blank'>SocialEngine here</a> and contact our support team for discounts.<br /> <a class='sesbasic_notice_btn' href='https://www.socialengine.com/experts/profile/socialenginesolutions' target='_blank'>Review Now</a> or <a href='javascript:void(0);' onclick='dismiss(\"dismiss_developer\")'>I will review later</a>.";
    ?>
    </span>
  </div>
<?php endif; ?>

<h2><?php echo $this->translate("GIF Images & Giphy Integration with GIF Player Plugin") ?></h2>
<div class="sesbasic_nav_btns">
  <a href="<?php echo $this->url(array('module' => 'sesbasic', 'controller' => 'settings', 'action' => 'contact-us'),'admin_default',true); ?>" target = "_blank" class="request-btn">Feature Request</a>
</div>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>