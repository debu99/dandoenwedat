<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: send-points.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function (order, default_direction) {
    // Just change direction
    if (order == currentOrder) {
      $('order_direction').value = (currentOrderDirection == 'ASC' ? 'DESC' : 'ASC');
    } else {
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }
</script>
<?php $this->headTranslate(array('SesSun','SesMon','SesTue','SesWed','SesThu','SesFri','SesSat',"SesJan", "SesFeb", "SesMar", "SesApr", "SesMay", "SesJun", "SesJul", "SesAug", "SesSep", "SesOct", "SesNov", "SesDec"));?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<h3><?php echo $this->translate("Send Credit Points") ?></h3>
<p><?php echo $this->translate('This page lists all the members of your website to whom you have sent the credit points directly from admin panel. From here, you can also send the credit points to members using the “Send Points” link below based on their Member Levels, all members or specific member.'); ?></p>
<br />
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Attach.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.Range.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/picker-style.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/datepicker.css'); ?>
<div>
  <?php echo $this->htmlLink(array('module' => 'sescredit','controller' => 'credits','action' => 'send-point'), $this->translate("Send Points"),array('class' => 'buttonlink sesbasic_icon_add')) ?>
</div>
<br />
<div class="admin_search sescredit_memberspoints_search sesbasic_bxs">
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<?php $counter = $this->paginator->getTotalItemCount(); ?>
<?php if($this->paginator->getTotalItemCount() > 0):?>
<div class="sesbasic_search_reasult">
  <?php echo $this->translate(array('%s member found.', '%s members found.', $counter), $this->locale()->toNumber($counter)) ?>
</div>
<div class="sescredit_mytransactions ">
  <div class="transactions_table" id='sescredit_table_contaner'>
    <table class="admin_table">
      <thead class="sesbasic_lbg">
        <tr>
          <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('rewardpoint_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate("Member Name") ?></a></th>
          <th align="class"><a href="javascript:void(0);" onclick="javascript:changeOrder('point', 'ASC');"><?php echo $this->translate("Credit Point") ?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('title', 'ASC');"><?php echo $this->translate("Member Level") ?></a></th>
          <th><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');"><?php echo $this->translate("Date") ?></a></th>
          <th class="_options" rowspan="2">Options</th>
        </tr>
      </thead>
      <tbody class="sescredit_transactions" id="activity-transaction">
        <?php foreach($this->paginator as $member):?>
          <tr>
            <td><?php echo $member->user_id;?></td>
            <td><a href="<?php echo $member->getHref();?>" ><?php echo $member->displayname;?></a></td>
            <td class="admin_table_cantered"><?php echo $member->point;?></td>
            <td class="admin_table_cantered"><?php echo $member->title;?></td>
            <td><?php echo $member->creation_date;?></td>
            <td class="_options"><a class="smoothbox" href="<?php echo $this->url(array('module' => 'sescredit','controller' => 'credits','action' => 'show-send-point-detail','id' => $member->rewardpoint_id),'admin_default',true);?>"><?php echo $this->translate("View Details");?> &raquo;</a></td>
          </tr>
        <?php endforeach;?>
      </tbody>
    </table>
  </div>
</div>
<br/>
<div>
  <?php echo $this->paginationControl($this->paginator,null,null,$this->urlParams); ?>
</div>
<?php else:?>
<div class="tip">
  <span class="sesbasic_text_light">
    <?php echo $this->translate('You have not sent credit points to any member yet.') ?>
  </span>
</div>
<?php endif;?>
<script type='text/javascript'>
  var inputwidth = sesJqueryObject('#show_date_field').width();
  var pickerposition = (400 - inputwidth);
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