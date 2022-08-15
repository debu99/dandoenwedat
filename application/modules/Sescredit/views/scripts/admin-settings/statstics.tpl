<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: statistics.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<?php $this->headTranslate(array('SesSun','SesMon','SesTue','SesWed','SesThu','SesFri','SesSat',"SesJan", "SesFeb", "SesMar", "SesApr", "SesMay", "SesJun", "SesJul", "SesAug", "SesSep", "SesOct", "SesNov", "SesDec"));?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Attach.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.Range.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/picker-style.css'); ?>
    <?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/datepicker.css'); ?>
<br />
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<div class='settings'>
  <form class="global_form">
    <div>
      <h3><?php echo $this->translate("Statistics") ?> </h3>
      <p class="description">
        <?php echo $this->translate("Below are some valuable statistics for the credit created on this site:"); ?>
      </p>
      <table class='admin_table' style="width: 50%;">
        <tbody>
          <tr>
            <td><strong class="bold"><?php echo "Total Earned Point:" ?></strong></td>
            <td><?php echo $this->stats->totalCredit ? $this->stats->totalCredit : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Deducted Point:" ?></strong></td>
            <td><?php echo $this->stats->totalDeduct ? $this->stats->totalDeduct : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Purchased Point:" ?></strong></td>
            <td><?php echo $this->stats->totalPurchase ? $this->stats->totalPurchase : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Signup Referral Point:" ?></strong></td>
            <td><?php echo $this->stats->totalReferral ? $this->stats->totalReferral : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Member Level Upgrade Point:" ?></strong></td>
            <td><?php echo $this->stats->totalLevelUpgrade ? $this->stats->totalLevelUpgrade : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Received From Friend Point:" ?></strong></td>
            <td><?php echo $this->stats->totalReceiveFriend ? $this->stats->totalReceiveFriend : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Total Transfered to Friend Point:" ?></strong></td>
            <td><?php echo $this->stats->totalTransferFriend ? $this->stats->totalTransferFriend : 0; ?></td>
          </tr>
          <tr>
            <td><strong class="bold"><?php echo "Product Purchased:" ?></strong></td>
            <td><?php echo $this->stats->totalProductPurchased ? $this->stats->totalProductPurchased : 0; ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </form>
</div>
<script type='text/javascript'>
  var inputwidth =sesJqueryObject('#show_date_field').width();
  var pickerposition =(400 - inputwidth);
  en4.core.runonce.add(function () {
    var picker = new Picker.Date.Range($('show_date_field'), {
      timePicker: false,
      columns: 2,
      positionOffset: {x: -pickerposition, y: 0}
    });
    var picker2 = new Picker.Date.Range('range_hidden', {
      toggle: $$('#range_select'),
      columns: 2,
      onSelect: function () {
        $('range_text').set('text', Array.map(arguments, function (date) {
            return date.format('%e %B %Y');
        }).join(' - '))
      }
    });
  });
</script>
<style>
  .datepicker .footer button.apply:before{content:"Select";}
  .datepicker .footer button.cancel:before{content:"Cancel";}
</style>
