<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedcomment
 * @package    Sesadvancedcomment
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: delete-item.tpl 2017-01-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<script type="text/javascript">
  parent.$('activity-item-<?php echo $this->action_id ?>').destroy();
  setTimeout(function()
  {
    parent.Smoothbox.close();
  }, <?php echo ( $this->smoothboxClose === true ? 1000 : $this->smoothboxClose ); ?>);
</script>


  <div class="global_form_popup_message">
    <?php echo $this->message ?>
  </div>