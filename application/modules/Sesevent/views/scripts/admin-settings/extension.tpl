
<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: extension.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js'); ?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<div class='sesbasic-form sesbasic-categories-form'>
  <div>
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket')): ?>
      <div class='sesbasic-form-cont'>
        <div class='clear'>
          <div class='settings sesbasic_admin_form'>
            <?php echo $this->form->render($this); ?>
          </div>
          <div class="sesbasic_waiting_msg_box" style="display:none;">
            <div class="sesbasic_waiting_msg_box_cont">
              <?php echo $this->translate("Please wait.. It might take some time to activate plugin."); ?>
              <i></i>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
	    <?php $eventticketInstalled = Engine_Api::_()->sesbasic()->pluginInstalled('seseventticket');  ?>
			<?php if(empty($eventticketInstalled)): ?>
				<div id="" class="ses_tip_red tip" style="margin:10px 10px 0;">
				  <span>
				    <?php echo 'At your site Advanced Event Tickets Extension is not installed. So, please purchase Advanced Event Tickets Extension from here.'; ?>
				  </span>
				</div>
			<?php elseif(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket')): ?>
				<div id="" class="ses_tip_red tip" style="margin:10px 10px 0;">
				  <span>
				    <?php echo 'At you site Advanced Event Tickets Extension is installed but not enable. So, you can enable this extension from "Manage Packages" section.'; ?>
				  </span>
				</div>
			<?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated',0)){?>
	<script type="application/javascript">
  	sesJqueryObject('.global_form').submit(function(e){
			sesJqueryObject('.sesbasic_waiting_msg_box').show();
		});
  </script>
<?php }else{ ?> 
<script type="application/javascript">
	var sTax = sesJqueryObject('#sesevent_ticket_service_tax-element ul').children();
	for(var i=0;i<sTax.length;i++){
			if(!sesJqueryObject(sTax[i]).html())
				continue;
			sesJqueryObject(sTax[i]).append('<span class="seseventticket_value_option"><a class="btn_click_s_tax" data-rel="edit" href="javascript:;" title="Edit"><i class="fa fa-edit"></i></a></span><span class="seseventticket_value_option"><a data-rel="delete" class="btn_click_s_tax" href="javascript:;" title="Delete"><i class="fa fa-trash"></i></a></span>');
	}
	sesJqueryObject('#sesevent_ticket_service_tax-element').find('ul').prepend('<li><a class="btn_click_s_tax sesventticket_add_link" data-rel="create" href="javascript:;"><i class="fa fa-plus"></i>Create New</a></li>');
sesJqueryObject(document).on('click','.btn_click_s_tax',function(e){
	var elem = sesJqueryObject(this).attr('data-rel');
	var data = '';
	if(elem != 'create'){
		data = sesJqueryObject(this).parent().parent().find('label').html().replace('%','');
	}
	var url =  "<?php echo $this->url(array( 'module' => 'sesevent', 'controller' => 'settings', 'action' => 'service-tax'),'admin_default',true); ?>/actionA/"+elem+'/data/'+data;
	Smoothbox.open(url);
	parent.Smoothbox.close;
	return false;
})

//entertainemtn tax
var eTax = sesJqueryObject('#sesevent_ticket_entertainment_tax-element ul').children();
	for(var i=0;i<eTax.length;i++){
			if(!sesJqueryObject(eTax[i]).html())
				continue;
			sesJqueryObject(eTax[i]).append('<span class="seseventticket_value_option"><a class="btn_click_e_tax" data-rel="edit" href="javascript:;" title="Edit"><i class="fa fa-edit"></i></a></span><span class="seseventticket_value_option"><a data-rel="delete" class="btn_click_e_tax" href="javascript:;" title="Delete"><i class="fa fa-trash"></i></a></span>');
	}
	sesJqueryObject('#sesevent_ticket_entertainment_tax-element').find('ul').prepend('<li><a class="btn_click_e_tax sesventticket_add_link" data-rel="create" href="javascript:;"><i class="fa fa-plus"></i> Create New</a></li>');	

sesJqueryObject(document).on('click','.btn_click_e_tax',function(e){
	var elem = sesJqueryObject(this).attr('data-rel');
	var data = '';
	if(elem != 'create'){
		data = sesJqueryObject(this).parent().parent().find('label').html().replace('%','');
	}
	var url =  "<?php echo $this->url(array( 'module' => 'sesevent', 'controller' => 'settings', 'action' => 'entertainment-tax'),'admin_default',true); ?>/actionA/"+elem+'/data/'+data;
	Smoothbox.open(url);
	parent.Smoothbox.close;
	return false;
})
</script>
<?php } ?>
