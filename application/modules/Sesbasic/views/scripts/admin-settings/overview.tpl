<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: overview.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<h2><?php echo $this->translate('SocialEngineSolutions Basic Required Plugin'); ?></h2>
<?php if (count($this->navigation)): ?>
  <div class='tabs'>
    <?php
    // Render the menu
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<div class="settings">
  <form method="post">
    <div>
      <div>
        <h3><?php echo $this->translate('SocialEngineSolutions Overview'); ?></h3>
        <p>
          <?php $siteTitle = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.title'); ?>
          <?php echo $this->translate("Hi %s, Thanks a lot for purchasing from us! You are our very valuable customer and your business is very important to us. With customers like you SocialEngineSolutions can be the best 3rd party developer in SocialEngine community. If you haven't had any chance to <a href='https://www.socialengine.com/experts/profile/socialenginesolutions' target='_blank'>write a review</a> for us on SocialEngine, then please take out a moment form your busy schedule to do so. It might take only a few minutes of your time, but it would mean a world to us! After writing a review for us, please also leave us an email, so that to show our gratitude for the same, we can offer you some great discounts on your next purchase or we can give our 1 hour customization service Free on your site to help you out from any minor bug fix.", $siteTitle); ?>
        </p>
        </br>
        <a class="button" href='https://www.socialengine.com/experts/profile/socialenginesolutions' target='_blank'><?php echo "Write a Review"; ?></a>
      </div>
    </div>
  </form>
</div>

<style>
  .button {
    background-color: #619dbe;
    border: 1px solid #50809b;
    border-radius: 3px;
    color: #fff;
    font-family: arial,sans-serif;
    font-weight: bold;
    padding: 0.5em 1em;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.3);
  }
</style>
