<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: invite.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php if( $this->count > 0 ): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function(){
      $('selectall').addEvent('click', function(event) {
        var el = $(event.target);
        $$('input[type=checkbox]').set('checked', el.get('checked'));
      });
    });
  </script>

  <?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>
<?php else: ?>
  <div>
    <?php echo $this->translate('You have no friends you can invite.');?>
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Close'), array('onclick' => 'parent.Smoothbox.close();')) ?>
  </div>
<?php endif; ?>