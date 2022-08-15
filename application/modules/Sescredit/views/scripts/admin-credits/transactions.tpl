<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: transactions.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }
</script>
<?php $this->headTranslate(array('SesSun','SesMon','SesTue','SesWed','SesThu','SesFri','SesSat',"SesJan", "SesFeb", "SesMar", "SesApr", "SesMay", "SesJun", "SesJul", "SesAug", "SesSep", "SesOct", "SesNov", "SesDec"));?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Attach.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.Range.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/picker-style.css'); ?>
    <?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/datepicker.css'); ?>
<h3><?php echo $this->translate("Transactions") ?></h3>
<p><?php echo $this->translate('This page lists all the transactions of activity points on your website with date and the activity type. You can use the filtering to find the activities and points as per your requirement and also view the details for more information.'); ?></p>
<br />
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<?php $counter = $this->paginator->getTotalItemCount(); ?>
<?php if($this->paginator->getTotalItemCount() > 0):?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s transaction found.', '%s transactions found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <div class="sescredit_mytransactions sesbasic_bxs">
    <div class="sescredit_transactions_container" id='sescredit_table_contaner'>
      <div class="sescredit_transactions_table">
        <div class="_transactionstable_header">
          <div class='_id _label'><a href="javascript:void(0);" onclick="javascript:changeOrder('credit_id', 'DESC');"><?php echo $this->translate("ID") ?></a></div>
          <div class="_owner _label"><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate("Activity Owner") ?></a></div>
          <div class="_activitytype _label">
            <a href="javascript:void(0);" onclick="javascript:changeOrder('point_type', 'ASC');"><?php echo $this->translate("Credit Points Type") ?></a>
          </div>
          <div class="_points">
            <div class="_label">
              <a href="javascript:void(0);" onclick="javascript:changeOrder('credit', 'ASC');"><?php echo $this->translate("Points Count") ?></a>
            </div>
            <div><i class="sescredit_icon_add fa fa-plus"></i> ve</div>
            <div><i class="sescredit_icon_minus fa fa-minus"></i> ve</div>
          </div>
          <div class="_date _label"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');"><?php echo $this->translate("Date") ?></a></div>
          <div class="_options _label" class="_options" rowspan="2">Options</div>
        </div>
        <div class="_transactionstable_content" id="activity-transaction">
        <?php foreach($this->paginator as $transaction):?>
          <div class="_transactionstable_item">
            <div class="_id"><?php echo $transaction->credit_id;?></div>
            <div class="_owner"><a href="<?php echo $transaction->getHref();?>" ><?php echo $transaction->displayname;?></a></div>
            <?php if($transaction->point_type == 'affiliate'):?>
              <div class="_activitytype"><?php echo "Inviter Affiliation";?></div>
            <?php elseif($transaction->point_type == 'receive_friend'):?>
              <div class="_activitytype"><?php echo "Received from Friend";?></div>
            <?php elseif($transaction->point_type == 'transfer_friend'):?>
              <div class="_activitytype"><?php echo "Transferred to Friend";?></div>
            <?php elseif($transaction->point_type == 'sesproduct_order'):?>
            <div class="_activitytype"><?php echo "Product Purchased";?></div>


            <?php elseif($transaction->point_type == 'deduction'):?>
              <div class="_activitytype"><?php echo "On Activity Deletion";?></div>
            <?php elseif($transaction->point_type == 'reward'):?>
              <div class="_activitytype"><?php echo "On Receiving Point from Site Admin";?></div>
            <?php else:?>
              <div class="_activitytype"><?php echo "For New Activity";?></div>
            <?php endif;?>
            
            <?php if($transaction->point_type == 'credit'):?>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <div class="_activitypoint">-</div>
            <?php elseif($transaction->point_type == 'deduction'):?>
              <div class="_activitypoint">-</div>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
            <?php elseif($transaction->point_type == 'affiliate'):?>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <div class="_activitypoint">-</div>
              <?php elseif($transaction->point_type == 'receive_friend'):?>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <div class="_activitypoint">-</div>
            <?php elseif($transaction->point_type == 'transfer_friend'):?>
              <div class="_activitypoint">-</div>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
            <?php elseif($transaction->point_type == 'sesproduct_order'):?>
            <div class="_activitypoint">-</div>
            <div class="_activitypoint"><?php echo $transaction->credit;?></div>
            <?php elseif($transaction->point_type == 'purchase'):?>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <div class="_activitypoint">-</div>
            <?php elseif($transaction->point_type == 'upgrade_level'):?>
              <div class="_activitypoint">-</div>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
            <?php elseif($transaction->point_type == 'reward'):?>
              <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <div class="_activitypoint">-</div>
            <?php endif;?>
            <?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/_date.tpl';?>
            <div class="_options"><a class="smoothbox" href="<?php echo $this->url(array('module' => 'sescredit','controller' => 'credits','action' => 'show-detail','id' => $transaction->credit_id),'admin_default',true);?>"><?php echo $this->translate("View Details");?> &raquo;</a></div>
          </div>
        <?php endforeach;?>
        </div>
      </div>
    </div>
  </div>
  <br/>
  <div>
    <?php echo $this->paginationControl($this->paginator,null,null,$this->urlParams); ?>
  </div>
<?php else:?>
  <div class="activity_transaction_noresult" style="width:100%;">
    <div id="error-message">
      <div class="sesbasic_tip clearfix">
        <img src="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit_contest_no_photo', 'application/modules/Sescredit/externals/images/no-credit.png'); ?>" alt="" />
        <span class="sesbasic_text_light">
          <?php echo $this->translate('There are no credit points found.') ?>
        </span>
      </div>
    </div>
  </div>
<?php endif;?>

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

