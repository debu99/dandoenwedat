<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php if(!$this->is_ajax){ 
echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
	<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
<?php } 
echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array(
	'event' => $this->event,
      ));	
?>
    	<div class="sesbasic_dashboard_form">
    		<?php echo $this->form->render() ?>
      </div>
    
<?php if(!$this->is_ajax){ ?>
    </div>
</div>
<script type="application/javascript">
	sesJqueryObject('#currency').hide();
<?php if(Engine_Api::_()->sesevent()->isMultiCurrencyAvailable()){ ?>
	sesJqueryObject('<span><?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?></span><span style="margin-left:10px;"><a href="javascript:;" id="sesevent_currency_coverter"><?php echo $this->translate("Currency Converter");?></a></span>').insertAfter('#currency');
<?php } else{ ?>
	sesJqueryObject('<span><?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?></span>').insertAfter('#currency');
<?php } ?>
</script>
<?php  } ?>
<script type="application/javascript">
function executeAfterLoad(){
	if(sesJqueryObject('#currency').css('display') == 'none')
		return;
	sesJqueryObject('#currency').hide();
<?php if(Engine_Api::_()->sesevent()->isMultiCurrencyAvailable()){ ?>
	sesJqueryObject('<span><?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?></span><span style="margin-left:10px;"><a href="javascript:;" id="sesevent_currency_coverter">Currency Converter</a></span>').insertAfter('#currency');
<?php }else{ ?>
	sesJqueryObject('<span><?php echo Engine_Api::_()->sesevent()->defaultCurrency(); ?></span>').insertAfter('#currency');
<?php } ?>
}
</script>
<?php if($this->is_ajax) die; ?>