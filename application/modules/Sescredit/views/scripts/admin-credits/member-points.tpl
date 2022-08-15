<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: member-points.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
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
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<h3><?php echo $this->translate("Earned Credits") ?></h3>
<p><?php echo $this->translate('This page lists all the members of your website who have earned credit points on your site with their total credits earned and their validity duration.'); ?></p>
<br />
<div class='admin_search sescredit_memberspoints_search sesbasic_bxs'>
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
            <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('user_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
            <th><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate("Owner") ?></a></th>
            <th align="class"><a href="javascript:void(0);" onclick="javascript:changeOrder('total_credit', 'ASC');"><?php echo $this->translate("Total Point") ?></a></th>
            <th><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');"><?php echo $this->translate("Vailidity Date") ?></a></th>
            <th class="_options" rowspan="2">Options</th>
          </tr>
        </thead>
        <tbody class="sescredit_transactions" id="activity-transaction">
        <?php foreach($this->paginator as $transaction):?>
          <tr>
            <td><?php echo $transaction->user_id;?></td>
            <td><a href="<?php echo $transaction->getHref();?>" ><?php echo $transaction->displayname;?></a></td>
            <td class="admin_table_cantered"><?php echo ($transaction->total_credit) ? $transaction->total_credit : 0;?></td>
            <td><?php echo ($transaction->expiry_date) ? $transaction->expiry_date : 'N/A';?></td>
            <td class="_options"><?php if($transaction->total_credit):?><a class="smoothbox" href="<?php echo $this->url(array('module' => 'sescredit','controller' => 'credits','action' => 'show-member-point-detail','id' => $transaction->detail_id),'admin_default',true);?>"><?php echo $this->translate("View Details");?> &raquo;</a><?php else:?>N/A<?php endif;?></td>
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
      <?php echo $this->translate('There are no member found with such criteria.') ?>
    </span>
  </div>
<?php endif;?>

