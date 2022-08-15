<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: contact-us.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
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
        <h3><?php echo $this->translate('Feature Request'); ?></h3>
        <p>
          <?php echo "If you have any question, query, doubt, or if you are facing problems in installing or setting up this plugins, then Don’t hesitate, just drop us a line from the Support Ticket section on SocialEngineSolutions website. Here, you can also share your ideas or request any extra feature, widgets, admin options." ?>
        </p>
        <div class="sesbasic_site_view">
          <iframe src="https://www.socialenginesolutions.com/contact-us?show=no" style="width:100%; height:500px; overflow:scroll;"></iframe>
        </div>
      </div>
    </div>
  </form>
</div>
