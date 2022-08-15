<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: payment-request.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/styles.css'); ?>
<div class="sesbasic_form_popup">
  <?php if(!$this->errorMessage){ ?>
  	<?php echo $this->form->render() ?>
  <?php }else{ ?>
  	<div class="tip">
      <span>
        <?php echo $this->translate($this->message) ?>
      </span>
  	</div>
  <?php } ?>
</div>