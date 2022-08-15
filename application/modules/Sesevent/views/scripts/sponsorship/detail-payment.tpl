<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: detail-payment.tplp 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<div class="sesevent_view_detail_popup">
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
  <h3> Payment Statistics </h3>
  <table class="sesbm">   
  	<tr>
      <td><?php echo $this->translate('Event Name') ?>:</td>
      <td><a href="<?php echo $this->event->getHref(); ?>" target="_blank"><?php echo $this->event->title; ?></a>
     </td>
    </tr>
    <tr>
    	<?php $user = Engine_Api::_()->getItem('user', $this->item->owner_id); ?>
      <td><?php echo $this->translate('Owner') ?>:</td>
      <td><?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('target'=>'_blank')); ?></td>
    </tr>
     <tr>
      <td><?php echo $this->translate('Request Id') ?>:</td>
      <td><?php echo $this->item->usersponsorshippayrequest_id ; ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Requested Amount') ?>:</td>
      <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->item->requested_amount,$defaultCurrency); ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Requested Date') ?>:</td>
      <td> <?php echo Engine_Api::_()->sesevent()->dateFormat($this->item->creation_date	); ?></td>
    </tr>
   <tr>
      <td><?php echo $this->translate('Requested Message') ?>:</td>
      <td> <?php echo $this->item->user_message ? $this->viewMore($this->item->user_message) : '-'; ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Release Amount') ?>:</td>
      <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($this->item->release_amount,$defaultCurrency); ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Release Date') ?>:</td>
      <td> <?php echo $this->item->release_date && (bool)strtotime($this->item->release_date) ? Engine_Api::_()->sesevent()->dateFormat($this->item->release_date) : '-'; ?></td>
    </tr>
   <tr>
      <td><?php echo $this->translate('Response Message') ?>:</td>
      <td> <?php echo $this->item->admin_message ? $this->viewMore($this->item->admin_message) : '-'; ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Status') ?>:</td>
      <td><?php echo $this->item->state; ?></td>
     </td>
    </tr>
  </table>
  <br />
  <button onclick='javascript:parent.Smoothbox.close()'>
    <?php echo $this->translate("Close") ?>
  </button>
</div>
<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>
