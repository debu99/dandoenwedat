<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
<?php $this->headTranslate(array('SesSun','SesMon','SesTue','SesWed','SesThu','SesFri','SesSat',"SesJan", "SesFeb", "SesMar", "SesApr", "SesMay", "SesJun", "SesJul", "SesAug", "SesSep", "SesOct", "SesNov", "SesDec"));?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<h3><?php echo $this->translate("Credit Sale Offers") ?></h3>
<p><?php echo $this->translate('This page lists all the offers you have created to sell credit points on your website. You can create new offer by using the "Create New Offer" link below.'); ?></p>
<br />
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Attach.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.Range.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/picker-style.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/datepicker.css'); ?>
<h3><?php echo $this->translate("") ?></h3>
<p><?php echo $this->translate(''); ?></p>
<br />
<div style="overflow: hidden;">
  <?php echo $this->formFilter->render($this) ?>
</div>
<br>
<div class='sesbasic_search_reasult clear'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s offer found.", "%s offers found.", $count),
        $this->locale()->toNumber($count)) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
    )); ?>
  </div>
</div>
<br />
<div>
  <?php echo $this->htmlLink(array('module' => 'sescredit','controller' => 'offers','action' => 'create'), $this->translate("Create New Offer"),array('class' => 'buttonlink sesbasic_icon_add')) ?>
</div>
<br />
<?php $price = Engine_Api::_()->sescredit()->getCurrencySymbol();?>
<?php if(count($this->paginator) > 0):?>
<div class="admin_table_form">
  <form>
    <table class='admin_table'>
      <thead>
        <tr>
          <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
          <th><?php echo $this->translate("Point Value ($price)") ?></th>
          <th><?php echo $this->translate("Point") ?></th>
          <th><?php echo $this->translate("Number of Offer") ?></th>
          <th><?php echo $this->translate("User Avail Limit") ?></th>
          <th><?php echo $this->translate("Start Time") ?></th>
          <th><?php echo $this->translate("End Time") ?></th>
          <th align="center"><?php echo $this->translate("Status") ?></th>
          <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody id='menu_list'>
        <?php if( count($this->paginator) ): ?>
          <?php foreach( $this->paginator as $item ): ?>
            <tr>
              <td><?php echo $item->offer_id ?></td>
              <td><?php echo $item->point_value; ?></td>
              <td><?php echo $item->point; ?></td>
              <?php if($item->limit_offer):?>
                <td><?php echo $item->limit_offer; ?></td>
              <?php else:?>
                <td><?php echo "No Limit"; ?></td>
              <?php endif;?>
              <?php if($item->user_avail):?>
                <td><?php echo $item->user_avail; ?></td>
              <?php else:?>
                <td><?php echo "Unlimited"; ?></td>
              <?php endif;?>
              <?php if($item->starttime != '0000-00-00 00:00:00'):?>
                <td><?php echo $item->starttime; ?></td>
                <td><?php echo $item->endtime; ?></td>
              <?php else:?>
                <td><?php echo "No Start Date"; ?></td>
                <td><?php echo "No End Date"; ?></td>
              <?php endif;?>
              <td class="admin_table_centered">
                <?php if($item->enable == 1):?>
                  <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sescredit', 'controller' => 'admin-offers', 'action' => 'enable', 'id' => $item->offer_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Disabled')))) ?>
                <?php else: ?>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sescredit', 'controller' => 'admin-offers', 'action' => 'enable', 'id' => $item->offer_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Enable')))) ?>
                <?php endif; ?>
              </td>
              <td class='admin_table_options'>
                <a class='' href='<?php echo $this->url(array('action' => 'edit', 'id' => $item->offer_id));?>'><?php echo $this->translate("Edit") ?></a>
                |
                <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->offer_id));?>'><?php echo $this->translate("Delete") ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <br />
  </form>
</div>
<?php else:?>
<div class="tip">
  <span>
    <?php echo "There are no offers in your search criteria.";?>
  </span>
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