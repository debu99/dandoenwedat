<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: sales-reports.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
if(!$this->is_ajax){
	echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array('event' => $this->event));?>
<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
	<div class="sesbasic_dashboard_content_header sesbasic_clearfix">
		<h3>Sponsorship Sales Reports</h3>
  </div>
  <div class="sesbasic_browse_search sesbasic_browse_search_horizontal sesbasic_dashboard_search_form">
  	<?php echo $this->form->render($this); ?>
	</div>
<?php if( isset($this->eventSponsorshipSaleData) && count($this->eventSponsorshipSaleData) > 0): ?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
<div class="sesbasic_dashboard_table sesbasic_bxs">
  <form method="post" >
    <table>
      <thead>
        <tr>
          <th class="centerT"><?php echo $this->translate("S.No"); ?></th>
           <th><?php echo $this->translate("Sponsorship Name") ?></th>
          <th><?php echo $this->translate("Date") ?></th>
          <th><?php echo $this->translate("Quatity") ?></th>
          <th><?php echo $this->translate("Commission Amount") ?></th>
          <th><?php echo $this->translate("Total Amount") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php 
        	$counter = 1;
          foreach ($this->eventSponsorshipSaleData as $item): ?>
        <tr>
          <td class="centerT"><?php echo $counter; ?></td>
          <td class="centerT"><?php echo $item->title; ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->dateFormat($item->creation_date); ?></td> 
          <td class="centerT"><?php echo $item->total_sponsorship; ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->commission_amount,$defaultCurrency); ?></td>
          <td><?php echo $item->totalAmountSale <= 0 ? 'FREE' : Engine_Api::_()->sesevent()->getCurrencyPrice($item->totalAmountSale,$defaultCurrency); ?></td>
        </tr>
        <?php $counter++;
        			endforeach; ?>
      </tbody>
    </table>
   </form>
</div>
<?php else: ?>
<div class="tip">
  <span>
    <?php echo $this->translate("No sponsorship sold yet.") ?>
  </span>
</div>
<?php endif; ?>
<?php if(!$this->is_ajax){ ?>
    </div>
</div>
<?php  } ?>
<style>
#startdate,
#enddate{ display:block !important;}
</style>
<script type="application/javascript">
if(sesBasicAutoScroll('#startdate')){
	var FromEndDateSales;
	var selectedDateSales =  new Date(sesBasicAutoScroll('#startdate').val());
	sesBasicAutoScroll('#startdate').datepicker({
			format: 'yyyy-m-d',
			weekStart: 1,
			autoclose: true,
			endDate: FromEndDateSales, 
	}).on('changeDate', function(ev){
		selectedDateSales = ev.date;	
		sesBasicAutoScroll('#enddate').datepicker('setStartDate', selectedDateSales);
	});
	sesBasicAutoScroll('#enddate').datepicker({
			format: 'yyyy-m-d',
			weekStart: 1,
			autoclose: true,
			startDate: selectedDateSales,
	}).on('changeDate', function(ev){
		FromEndDateSales	= ev.date;	
		 sesBasicAutoScroll('#startdate').datepicker('setEndDate', FromEndDateSales);
	});	
}
</script>