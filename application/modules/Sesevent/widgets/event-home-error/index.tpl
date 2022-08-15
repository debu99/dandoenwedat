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
<?php if($this->paginator->getTotalItemCount() <= 0): ?>
<div class="tip">
  <span>
    <?php echo $this->translate('Nobody has created a event with that criteria.') ?>
    <?php if($this->canCreate): ?>
    <?php echo $this->htmlLink(array('route' => 'sesevent_general', 'action' => 'create'), $this->translate('Why don\'t you add some?')) ?>
    <?php endif; ?>
  </span>
</div>
<?php endif; ?>