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
		<h3><?php echo $this->translate('Sales Reports'); ?></h3>
    <p><?php echo $this->translate('Below, you can see the sales report of tickets sold from this website. Entering criteria into the filter fields will help you find specific reports. You can also download the reports in csv and excel formats.'); ?></p>
  </div>
  <div class="sesbasic_browse_search sesbasic_browse_search_horizontal sesbasic_dashboard_search_form">
  	<?php echo $this->form->render($this); ?>
	</div>
<div class="sesbasic_dashboard_table_right_links">
	<a href="javascript:;"  class="sesevent_report_download" data-rel="csv"><i class="fa fa-download sesbasic_text_light"></i><?php echo $this->translate("Download Report in CSV"); ?></a>
  <a href="javascript:;" class="sesevent_report_download" data-rel="excel"><i class="fa fa-download sesbasic_text_light"></i><?php echo $this->translate("Download Report in Excel"); ?></a>
</div>
<?php if( isset($this->eventSaleData) && count($this->eventSaleData) > 0): ?>
<?php $defaultCurrency = Engine_Api::_()->sesevent()->defaultCurrency(); ?>
<div class="sesbasic_dashboard_table sesbasic_bxs">
  <form method="post" >
    <table>
      <thead>
        <tr>
          <th class="centerT"><?php echo $this->translate("S.No"); ?></th>
           <th><?php echo $this->translate("Ticket Name") ?></th>
          <th><?php echo $this->translate("Date of Purchase") ?></th>
          <th><?php echo $this->translate("Quatity") ?></th>
          <th><?php echo $this->translate("Service Tax") ?></th>
          <th><?php echo $this->translate("Entertainment Tax") ?></th>
          <th><?php echo $this->translate("Total Tax") ?></th>
        <!--  <th><?php echo $this->translate("Commission Amount") ?></th>-->
          <th><?php echo $this->translate("Total Amount") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php 
        	$counter = 1;
          foreach ($this->eventSaleData as $item): ?>
        <tr>
          <td class="centerT"><?php echo $counter; ?></td>
          <td class="centerT"><?php echo $item->title; ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->dateFormat($item->creation_date); ?></td> 
          <td class="centerT"><?php echo $item->total_tickets; ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->total_service_tax,$defaultCurrency); ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->total_entertainment_tax,$defaultCurrency); ?></td>
          <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->totalTaxAmount,$defaultCurrency); ?></td>
          <!--<td><?php //echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->commission_amount,$defaultCurrency); ?></td>-->
          <td><?php echo $item->totalAmountSale <= 0 ? $this->translate('FREE') : Engine_Api::_()->sesevent()->getCurrencyPrice($item->totalAmountSale,$defaultCurrency); ?></td>
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
    <?php echo $this->translate("No ticket sold yet.") ?>
  </span>
</div>
<?php endif; ?>
<?php if(!$this->is_ajax){ ?>
    </div>
</div>
</div>
<?php  } ?>
<script type="application/javascript">
sesJqueryObject(document).on('click','.sesevent_report_download',function(){
	var downloadType = 	sesJqueryObject(this).attr('data-rel');
	if(downloadType == 'csv'){
		sesJqueryObject('#csv').val('1');
	}else{
			sesJqueryObject('#excel').val('1');
	}
	sesJqueryObject('#sesevent_search_form_sale_report').trigger('submit');
	sesJqueryObject('#csv').val('');
	sesJqueryObject('#excel').val('');
	
});
</script>
<style>
#startdate,
#enddate{ display:block !important;}
.widthClass{width:90px !important;}
</style>
<script type="application/javascript">
sesBasicAutoScroll('#startdate').addClass('widthClass');
sesBasicAutoScroll('#enddate').addClass('widthClass');
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