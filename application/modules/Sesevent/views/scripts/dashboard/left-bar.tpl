<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: left-bar.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/dashboard.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/styles.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>
<div class="layout_middle">
  <div class="generic_layout_container sesevent_dashboard_main_nav">
   <?php echo $this->content()->renderWidget('sesevent.browse-menu',array('dashboard'=>true)); ?> 
  </div>
	<div class="generic_layout_container">
		<div class="sesbasic_dashboard_container sesbasic_clearfix">
			<div class="sesbasic_dashboard_top_section sesbasic_clearfix sesbm">
        <div class="sesbasic_dashboard_top_section_left">
          <div class="sesbasic_dashboard_top_section_item_photo"> <?php echo $this->htmlLink($this->event->getHref(), $this->itemPhoto($this->event, 'thumb.icon')) ?> </div>
          <div class="sesbasic_dashboard_top_section_item_title"> <?php echo $this->htmlLink($this->event->getHref(),$this->event->getTitle()); ?> </div>
        </div>
        <div class="sesbasic_dashboard_top_section_btns">
          <a href="<?php echo $this->event->getHref(); ?>" class="sesbasic_link_btn"><?php echo $this->translate("View Event"); ?></a>
          <?php if($this->event->authorization()->isAllowed(Engine_Api::_()->user()->getViewer(), 'delete')){ ?>
            <a href="<?php echo $this->url(array('event_id' => $this->event->event_id,'action'=>'delete'), 'sesevent_specific', true); ?>" class="sesbasic_link_btn smoothbox"><?php echo $this->translate("Delete Event"); ?></a>
          <?php } ?>
        </div>
			</div>
      <div class="sesbasic_dashboard_tabs sesbasic_bxs">
        <ul class="sesbm">
          <li class="sesbm">
            <?php $manage_event = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'manage_event')); ?>
            <a href="#Manage" class="sesbasic_dashboard_nopropagate"> <i class="tab-icon db_calendar"></i> <i class="tab-arrow fa fa-caret-down sesbasic_text_light"></i> <span><?php echo $this->translate($manage_event->title); ?></span> </a>
            <?php $edit_event = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'edit_event')); ?>
            <?php $edit_photo = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'edit_photo')); ?>
            <?php $contact_information = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'contact_information')); ?>
            <?php $seo = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'seo')); ?>
            <?php $style = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'style')); ?>
            <?php $overview = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'overview')); ?>
            <?php $backgroundphoto = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'backgroundphoto')); ?>
            <?php $speaker_event = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'speaker_event')); ?>
            <ul class="sesbm" style="display:none">
              <?php if($edit_event->enabled): ?>
              <li><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url), 'sesevent_dashboard', true); ?>" class="dashboard_a_link" ><?php echo $this->translate($edit_event->title); ?></a></li>
              <?php endif; ?>
              <?php if($edit_photo->enabled): ?>
              <li><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'mainphoto'), 'sesevent_dashboard', true); ?>" class="dashboard_a_link" ><?php echo $this->translate($edit_photo->title); ?></a></li>
              <?php endif; ?>
              <?php if($contact_information->enabled): ?>
              <li><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'contact-information'), 'sesevent_dashboard', true); ?>" class="sesbasic_dashboard_nopropagate_content dashboard_a_link"><?php echo $this->translate($contact_information->title); ?></a></li>
              <?php endif; ?>
              <?php if($seo->enabled): ?>
              <li><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url, 'action'=>'seo'), 'sesevent_dashboard', true); ?>" class="sesbasic_dashboard_nopropagate_content dashboard_a_link"><?php echo $this->translate($seo->title); ?></a></li>
              <?php endif; ?>
              <?php if(@$style->enabled): ?>
              <li><a  href="<?php echo $this->url(array('event_id' => $this->event->custom_url, 'action'=>'style'), 'sesevent_dashboard', true); ?>" class="sesbasic_dashboard_nopropagate_content dashboard_a_link"><?php echo $this->translate($style->title); ?></a></li>
              <?php endif; ?>
              <?php if(@$overview->enabled): ?>
              <li><a class="dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url, 'action'=>'overview'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($overview->title); ?></a></li>
              <?php endif; ?>
              <?php if(@$backgroundphoto->enabled): ?>
              <li><a class="dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url, 'action'=>'backgroundphoto'), 'sesevent_dashboard', true); ?>" ><?php echo $this->translate($backgroundphoto->title); ?></a></li>
              <?php endif; ?>
              <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventspeaker')): ?>
              <?php if($speaker_event->enabled): ?>
              <li><a  class="dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url, 'action' => 'speakers'), 'seseventspeaker_dashboard', true); ?>" ><?php echo $this->translate($speaker_event->title); ?></a></li>
              <?php endif; ?>
              <?php endif; ?>
              <?php if(0 && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sesblog')):?>
              <li><a href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'show-blog-request'), 'sesevent_dashboard', true); ?>" class="sesbasic_dashboard_nopropagate_content dashboard_a_link"><?php echo $this->translate('Manage Blog Requests');?></a></li>
              <?php endif;?>
            </ul>
          </li>
          <?php 
              $viewer = Engine_Api::_()->user()->getViewer();
              $level = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
              $member_level_current_user = $level->flag;
              $allowed_member_levels = array("superadmin", "admin");
              $allowedToMakeTickets = in_array($member_level_current_user, $allowed_member_levels);
          ?>
          <?php if($allowedToMakeTickets && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')): ?>
          <li class="sesbm">
            <?php $tickets = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'tickets')); ?>
            <a href="#Ticket" class="sesbasic_dashboard_nopropagate"> <i class="tab-icon db_ticket"></i> <i class="tab-arrow fa fa-caret-down sesbasic_text_light"></i> <span><?php echo $this->translate($tickets->title); ?></span> </a>
            <?php $manage_tickets = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'manage_tickets')); ?>
            <?php $create_tickets = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'create_tickets')); ?>
            <?php $account_details = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'account_details')); ?>
            <?php $ticket_information = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'event_ticket_information')); ?>
            <?php $sales_statistics = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sales_statistics')); ?>
            <?php $manage_orders = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'manage_orders')); ?>
            <?php $sales_orders = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sales_orders')); ?>
            <?php $payment_requests = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'payment_requests')); ?>
            <?php $payment_transactions = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'payment_transactions')); ?>
            <?php $search_ticket = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'search_ticket')); ?>
            <ul class="sesbm" style="display:none">
              <?php if($create_tickets->enabled): ?>
              <li> <a class="dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'create-ticket'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($create_tickets->title); ?></a> </li>
              <?php endif; ?>
              <?php if($manage_tickets->enabled): ?>
              <li><a  id="manage-ticket" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'manage-ticket'), 'sesevent_dashboard', true); ?>" class="sesbasic_dashboard_nopropagate_content dashboard_a_link"><?php echo $this->translate($manage_tickets->title); ?></a></li>
              <?php endif; ?>
               <?php if($manage_orders->enabled): ?>
              <li> <a  id="sesevent_manage_order" class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'manage-orders'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($manage_orders->title); ?></a> </li>
              <?php endif; ?>
              <?php if($search_ticket->enabled): ?>
              <li> <a  id="sesevent_search_ticket_search" class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'search-ticket'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($search_ticket->title); ?></a> </li>
              <?php endif; ?>
              <?php if($payment_requests->enabled): ?>
              <li> <a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'payment-requests'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($payment_requests->title); ?></a> </li>
              <?php endif; ?>
              <?php if($payment_transactions->enabled): ?>
              <li> <a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'payment-transaction'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($payment_transactions->title); ?></a> </li>
              <?php endif; ?>
              <?php if($ticket_information->enabled): ?>
              <li> <a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'ticket-information'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($ticket_information->title); ?></a> </li>
              <?php endif; ?>
              <?php if($sales_statistics->enabled): ?>
              <li> <a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'sales-stats'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($sales_statistics); ?></a> </li>
              <?php endif; ?>
              <?php if($sales_orders->enabled): ?>
              <li> <a  class="dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'sales-reports'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($sales_orders); ?></a> </li>
              <?php endif; ?>
              <?php if($account_details->enabled): ?>
              <li> <a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" id="dashboard_account_details" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'account-details'), 'sesevent_dashboard', true); ?>"><?php echo $this->translate($account_details->title); ?></a> </li>
              <?php endif; ?>        
            </ul>
          </li>
          <?php endif; ?>
          <?php if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')): ?>
          <?php if($this->event->is_sponsorship){ ?>
          <li class="sesbm">
            <?php $sponsorship = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship')); ?>
            <a  href="#Sponsorship" class="sesbasic_dashboard_nopropagate"> <i class="tab-icon db_sponsor"></i> <i class="tab-arrow fa fa-caret-down sesbasic_text_light"></i> <span><?php echo $this->translate($sponsorship->title); ?></span> </a>
            <?php $sponsorship_manage = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_manage')); ?>
            <?php $sponsorship_create = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_create')); ?>
            <?php $sponsorship_requests = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_requests')); ?>
            <?php $sponsorship_sales_stats = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_sales_stats')); ?>
            <?php $sponsorship_manage_orders = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_manage_orders')); ?>
            <?php $sponsorship_sales_reports = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_sales_reports')); ?>
            <?php $sponsorship_payment_requests = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_payment_requests')); ?>
            <?php $sponsorship_payment_transactions = Engine_Api::_()->getDbtable('dashboards', 'sesevent')->getDashboardsItems(array('type' => 'sponsorship_payment_transactions')); ?>
            <ul class="sesbm" style="display:none">
              <?php if($sponsorship_manage->enabled): ?>
              <li><a  id="sesevent_manage_sponsorships" class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'manage-sponsorship'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_manage->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_create->enabled): ?>
              <li><a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link"  href="<?php echo $this->url(array('event_id' => $this->event->custom_url), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_create->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_requests->enabled): ?>
              <li><a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'request-sponsorship'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_requests->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_sales_stats->enabled): ?>
              <li><a  class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'sales-stats'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_sales_stats->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_manage_orders->enabled): ?>
              <li><a class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'manage-orders'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_manage_orders->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_sales_reports->enabled): ?>
              <li><a  class="dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'sales-reports'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_sales_reports->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_payment_requests->enabled): ?>
              <li><a class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'payment-requests'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_payment_requests->title); ?></a></li>
              <?php endif; ?>
              <?php if($sponsorship_payment_transactions->enabled): ?>
              <li><a class="sesbasic_dashboard_nopropagate_content dashboard_a_link" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'payment-transaction'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate($sponsorship_payment_transactions->title); ?></a></li>
              <?php endif; ?>
            </ul>
          </li>
          <?php } ?>
          <?php endif; ?>
        </ul>
          <?php if(isset($this->event->cover_photo) && $this->event->cover_photo != 0 && $this->event->cover_photo != ''){ 
               $eventCover =	Engine_Api::_()->storage()->get($this->event->cover_photo, '')->getPhotoUrl(); 
         }else
            $eventCover =''; 
      	?>
        <div class="sesevent_dashboard_event_info sesbasic_clearfix sesbm">
          <?php if(isset($this->event->cover_photo) && $this->event->cover_photo != 0 && $this->event->cover_photo != ''){ ?>
            <div class="sesevent_dashboard_event_info_cover"> 
              <img src="<?php echo $eventCover; ?>" />
             <?php if($this->event->featured || $this->event->sponsored){ ?>
              <p class="sesevent_labels">
                <?php if($this->event->featured ){ ?>
                <span class="sesevent_label_featured"><?php echo $this->translate("FEATURED"); ?></span>
                <?php } ?>
                <?php if($this->event->sponsored ){ ?>
                <span class="sesevent_label_sponsored"><?php echo $this->translate("SPONSORED"); ?></span>
                <?php } ?>
              </p>
             <?php } ?>
             <?php if($this->event->verified ){ ?>
              <div class="sesevent_verified_label" title="<?php echo $this->translate("VERIFIED"); ?>"><i class="fa fa-check"></i></div>
             <?php } ?>
              <div class="sesevent_dashboard_event_main_photo sesbm">
                <img src="<?php echo $this->event->getPhotoUrl(); ?>" /> 
              </div>
            </div>
          <?php } else { ?>
            <div class="sesevent_dashboard_event_photo">
              <img src="<?php echo $this->event->getPhotoUrl(); ?>" />
      <?php if($this->event->featured || $this->event->sponsored){ ?>
              <p class="sesevent_labels">
                <?php if($this->event->featured ){ ?>
                <span class="sesevent_label_featured"><?php echo $this->translate("FEATURED"); ?></span>
                <?php } ?>
                <?php if($this->event->sponsored ){ ?>
                <span class="sesevent_label_sponsored"><?php echo $this->translate("SPONSORED"); ?></span>
                <?php } ?>
              </p>
             <?php } ?>
             <?php if($this->event->verified ){ ?>
              <div class="sesevent_verified_label" title="<?php echo $this->translate("VERIFIED"); ?>"><i class="fa fa-check"></i></div>
             <?php } ?>
            </div>
          <?php }; ?>
          <div class="sesevent_dashboard_event_info_content sesbasic_clearfix sesbd">
            <div class="sesevent_dashboard_event_details">
              <div class="sesevent_dashboard_event_title">
                <a href="<?php echo $this->event->getHref(); ?>"><b><?php echo $this->event->getTitle(); ?></b></a>
              </div>
              <?php if($this->event->location && Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent_enable_location', 1)):?>
                <?php $locationText = $this->translate('Location');?>
                <?php $locationvalue = $this->event->location;?>
                <?php echo $location = "<div class='sesevent_list_stats sesevent_list_location'><span class='widthfull'><i class='fas fa-map-marker-alt sesbasic_text_light' title='".$locationText."'></i><span title='" .$locationvalue. "'><a href='".$this->url(array('resource_id' => $this->event->event_id,'resource_type'=>'sesevent_event','action'=>'get-direction'), 'sesbasic_get_direction', true)."' class='openSmoothbox'>".$this->event->location."</a></span></span></div>";?>
              <?php endif;?>
              
              <div class="sesevent_list_stats sesevent_list_time"> 
                <span class="widthfull"> 
                  <i class="far fa-calendar-alt sesbasic_text_light" title="<?php echo $this->translate('Start & End Time');?>"></i> 
                  <?php echo $this->eventStartEndDates($this->event); ?>
                </span> 
              </div>
             <?php if($this->event->category_id){ 
              $category = Engine_Api::_()->getItem('sesevent_category', $this->event->category_id);
             ?>
              <div class="sesevent_list_stats">
                <span><i class="fa fa-folder-open sesbasic_text_light"></i> 
                <a href="<?php echo $category->getHref(); ?>"><?php echo $category->getTitle(); ?></a> 
                </span> 
              </div>
             <?php } ?>
             <?php 
              $currentTime = time();
              if(strtotime($this->event->starttime) > $currentTime){
                $status = 'notStarted';
              }else if(strtotime($this->event->endtime) < $currentTime){
                $status = 'expire';
              }else{
                $status = 'onGoing';	
              }
             ?>
             <?php if($status == 'notStarted'){ ?>
              <div class="sesevent_event_status sesbasic_clearfix open clear floatL">
                <span class="sesevent_event_status_txt">Event not started</span>
              </div>
            <?php }else if($status == 'expire'){ ?>
              <div class="sesevent_event_status sesbasic_clearfix close clear floatL">
                <span class="sesevent_event_status_txt">Event expires</span>
              </div>
            <?php }else{ ?>
              <div class="sesevent_event_status sesbasic_clearfix open clear floatL">
                <span class="sesevent_event_status_txt">Event ongoing</span>
              </div>
            <?php } ?>
              <?php if(!$this->event->is_approved){ ?>
              <div class="sesevent_event_status sesbasic_clearfix unapproved clear floatL">
                <span class="sesevent_event_status_txt">UNAPPROVED</b></span>
              </div>
              <?php } ?>
              <?php echo $this->content()->renderWidget('sesevent.advance-share',array('dashboard'=>true)); ?> 
            </div>
        	</div>    
      	</div>
      </div>

<script type="application/javascript">
sesJqueryObject(document).ready(function(){
	var totalLinks = sesJqueryObject('.dashboard_a_link');
	for(var i =0;i < totalLinks.length ; i++){
			var data_url = sesJqueryObject(totalLinks[i]).attr('href');
			var linkurl = window.location.href ;
			if(linkurl.indexOf(data_url) > 0){
					sesJqueryObject(totalLinks[i]).parent().addClass('active');
					sesJqueryObject(totalLinks[i]).parent().parent().parent().find('a.sesbasic_dashboard_nopropagate').trigger('click');
			}
	}
});
sesJqueryObject(document).on('submit','#manage_order_search_form',function(event){
	if(sesJqueryObject('#manage_order_search_form').hasClass('manage_sponsorship')){
		var widgetName = 'manage-sponsorship-orders';	
	}else if(sesJqueryObject('#manage_order_search_form').hasClass('search_ticket')){
		var widgetName = 'search-ticket';	
	}else
		var widgetName = 'manage-orders';	
	event.preventDefault();
	var searchFormData = sesJqueryObject(this).serialize();
	sesJqueryObject('#loadingimgsesevent-wrapper').show();
	new Request.HTML({
			method: 'post',
			url :  en4.core.baseUrl + 'widget/index/mod/sesevent/name/'+widgetName,
			data : {
				format : 'html',
				event_id:'<?php echo $this->event_id ? $this->event_id : $this->event->getIdentity(); ?>',
				searchParams :searchFormData, 
				is_search_ajax:true,
			},
			onComplete: function(response) {
				sesJqueryObject('#loadingimgsesevent-wrapper').hide();
				sesJqueryObject('#sesevent_manage_order_content').html(response);
			}
	}).send();
});
var sendParamInSearch = '';
sesJqueryObject(document).on('click','.sesbasic_dashboard_nopropagate, .sesbasic_dashboard_nopropagate_content',function(e){
	e.preventDefault();
	//ajax request
	if(sesJqueryObject(this).hasClass('sesbasic_dashboard_nopropagate_content')){
			if(!sesJqueryObject(this).parent().hasClass('active'))
				getDataThroughAjax(sesJqueryObject(this).attr('href'));
		  sesJqueryObject(".sesbasic_dashboard_tabs > ul li").each(function() {
				sesJqueryObject(this).removeClass('active');
			});
			sesJqueryObject('.sesbasic_dashboard_tabs > ul > li ul > li').each(function() {
					sesJqueryObject(this).removeClass('active');
			});			
			sesJqueryObject(this).parent().addClass('active');
			sesJqueryObject(this).parent().parent().parent().addClass('active');
	}	
});
var ajaxRequest;
//get data through ajax
function getDataThroughAjax(url){
	if(!url)
		return;
	history.pushState(null, null, url);
	if(typeof ajaxRequest != 'undefined')
		ajaxRequest.cancel();
	sesJqueryObject('.sesbasic_dashboard_content').html('<div class="sesbasic_loading_container"></div>');
	ajaxRequest = new Request.HTML({
      method: 'post',
      url : url,
      data : {
        format : 'html',
				is_ajax:true,
				dataAjax : sendParamInSearch,
				is_ajax_content:true,
      },
      onComplete: function(response) {
				sendParamInSearch = '';
				sesJqueryObject('.sesbasic_dashboard_content').html(response);
				if(typeof executeAfterLoad == 'function'){
					executeAfterLoad();
				}
				if(sesJqueryObject('#loadingimgsesevent-wrapper').length)
					sesJqueryObject('#loadingimgsesevent-wrapper').hide();
			}
    });
    ajaxRequest.send();
}
sesJqueryObject(".sesbasic_dashboard_tabs > ul li a").each(function() {
	var c = sesJqueryObject(this).attr("href");
	sesJqueryObject(this).click(function() {
		if(sesJqueryObject(this).hasClass('sesbasic_dashboard_nopropagate')){
			if(sesJqueryObject(this).parent().find('ul').is(":visible")){
				sesJqueryObject(this).parent().find('ul').slideUp()
			}else{
					sesJqueryObject(".sesbasic_dashboard_tabs ul ul").each(function() {
							sesJqueryObject(this).slideUp();
					});
					sesJqueryObject(this).parent().find('ul').slideDown()
			}
					return false
			}	
	})
});
var error = false;
var objectError ;
var counter = 0;
var customAlert;
function validateForm(){
		var errorPresent = false;
		if(sesJqueryObject('#sesevent_ajax_form_submit').length>0)
			var submitFormVal= 'sesevent_ajax_form_submit';
		else if(sesJqueryObject('#sesevent_ticket_submit_form').length>0)
			var submitFormVal= 'sesevent_ticket_submit_form';
		else
			return false;
		objectError;
		sesJqueryObject('#'+submitFormVal+' input, #'+submitFormVal+' select,#'+submitFormVal+' checkbox,#'+submitFormVal+' textarea,#'+submitFormVal+' radio').each(
				function(index){
						customAlert = false;
						var input = sesJqueryObject(this);
						if(sesJqueryObject(this).closest('div').parent().not('fieldset').css('display') != 'none' && sesJqueryObject(this).closest('div').parent().not('fieldset').find('.form-label').find('label').first().hasClass('required') && sesJqueryObject(this).prop('type') != 'hidden' && sesJqueryObject(this).closest('div').parent().not('fieldset').attr('class') != 'form-elements'){	
						  if(sesJqueryObject(this).prop('type') == 'checkbox'){
								value = '';
								if(sesJqueryObject('input[name="'+sesJqueryObject(this).attr('name')+'"]:checked').length > 0) { 
										value = 1;
								};
								if(value == '')
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'select-multiple'){
								if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'select-one' || sesJqueryObject(this).prop('type') == 'select' ){
								if(sesJqueryObject(this).val() === '')
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'radio'){
								if(sesJqueryObject("input[name='"+sesJqueryObject(this).attr('name').replace('[]','')+"']:checked").val() === '')
									error = true;
								else
									error = false;
							}else if(sesJqueryObject(this).prop('type') == 'textarea'){
								if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
									error = true;
								else
									error = false;
							}else{
								if(sesJqueryObject(this).val() === '' || sesJqueryObject(this).val() == null)
									error = true;
								else
									error = false;
							}
							if(error){
							 if(counter == 0){
							 	objectError = this;
							 }
								counter++
							}else{
							}
							if(error)
								errorPresent = true;
							error = false;
						}
				}
			);	
			if(!errorPresent){
				if(sesJqueryObject('#price').length && sesJqueryObject('#price').val() != '' &&  !sesJqueryObject.isNumeric(sesJqueryObject('#price').val() || sesJqueryObject('#price').val() < 0 )){
						errorPresent = true;
						objectError = sesJqueryObject('#price');
						alert("<?php echo $this->translate('Please Enter valid amount'); ?>");
				}else if(sesJqueryObject('#total').length && sesJqueryObject('#total').val() != '' && (!sesJqueryObject.isNumeric(sesJqueryObject('#total').val()) || sesJqueryObject('#total').val() < 0)){
						errorPresent = true;
						objectError = sesJqueryObject('#total');
						customAlert = true;
						alert("<?php echo $this->translate('Please Enter valid total quantity'); ?>");
				}else if(sesJqueryObject('#min_quantity').length && sesJqueryObject('#min_quantity').val() != '' && (!sesJqueryObject.isNumeric(sesJqueryObject('#min_quantity').val()) || sesJqueryObject('#min_quantity').val() < 0)){
						errorPresent = true;
						objectError = sesJqueryObject('#min_quantity');
						customAlert = true;
						alert("<?php echo $this->translate('Please Enter valid min quantity'); ?>");
				}else if(sesJqueryObject('#max_quantity').length && sesJqueryObject('#max_quantity').val() != '' && (!sesJqueryObject.isNumeric(sesJqueryObject('#max_quantity').val()) || sesJqueryObject('#max_quantity').val() < 0)){
						errorPresent = true;
						objectError = sesJqueryObject('#max_quantity');
						customAlert = true;
						alert("<?php echo $this->translate('Please Enter valid max quantity'); ?>");
				}else if(sesJqueryObject('#max_quantity').length && sesJqueryObject('#max_quantity').val() != '' && (parseInt(sesJqueryObject('#max_quantity').val()) < parseInt(sesJqueryObject('#min_quantity').val()) || parseInt(sesJqueryObject('#min_quantity').val()) > parseInt(sesJqueryObject('#total').val()) || parseInt(sesJqueryObject('#max_quantity').val()) > parseInt(sesJqueryObject('#total').val()))){
						errorPresent = true;
						objectError = sesJqueryObject('#total');
						customAlert = true;
						alert("<?php echo $this->translate('Please Enter valid min & max quantity'); ?>");
				}else	if(sesJqueryObject('#starttime-date').length && sesJqueryObject('#starttime-date').val() == ''){
						errorPresent = true;
						objectError = sesJqueryObject('#starttime-date');
				}else if(sesJqueryObject('#endtime-date').length && sesJqueryObject('#endtime-date').val() == ''){
						errorPresent = true;
						objectError = sesJqueryObject('#endtime-date');
				}else if(sesJqueryObject('#starttime-date').length && sesJqueryObject('#endtime-date').length){
					var startDate = new Date(sesJqueryObject('#starttime-date').val()+" "+sesJqueryObject('#starttime-hour').val()+":"+sesJqueryObject('#starttime-minute').val()+":00 "+sesJqueryObject('#starttime-ampm').val());	
					var endDate = new Date(sesJqueryObject('#endtime-date').val()+" "+sesJqueryObject('#endtime-hour').val()+":"+sesJqueryObject('#endtime-minute').val()+":00 "+sesJqueryObject('#endtime-ampm').val());
					if(startDate.getTime() > endDate.getTime()){
							errorPresent = true;
							objectError = sesJqueryObject('#starttime-date');
					}
				}
			}
			return errorPresent ;
}
var ajaxDeleteRequest;
sesJqueryObject(document).on('click','.sesevent_ajax_delete',function(e){
	e.preventDefault();
	var object = sesJqueryObject(this);
	var url = object.attr('href');
	var value = object.attr('data-value');
	if(!value)
		value = "Are you sure want to delete?";
	if(typeof ajaxDeleteRequest != 'undefined')
			ajaxDeleteRequest.cancel();
	if(confirm(value)){
		 new Request.HTML({
      method: 'post',
      url : url,
      data : {
        format : 'html',
				is_ajax:true,
      },
      onComplete: function(response) {
				if(response)
					sesJqueryObject(object).parent().parent().remove();
				else
					alert('Something went wrong,please try again later');
			}
    }).send();
	}
});
var submitFormAjax;
sesJqueryObject(document).on('submit','#sesevent_ajax_form_submit',function(e){
	e.preventDefault();
	//validate form
	var validation = validateForm();
	//if error comes show alert message and exit.
		if(validation)
		{
			if(!customAlert){
				if(sesJqueryObject(objectError).hasClass('event_calendar')){
					alert('<?php echo $this->translate("Start date must be less than end date."); ?>');
				}else{
					alert('<?php echo $this->translate("Please complete the red mark fields"); ?>');
				}
			}
			if(typeof objectError != 'undefined'){
			 var errorFirstObject = sesJqueryObject(objectError).parent().parent();
			 sesJqueryObject('html, body').animate({
        scrollTop: errorFirstObject.offset().top
    	 }, 2000);
			}
			return false;	
		}else{
			if(!sesJqueryObject('#sesdashboard_overlay_content').length)
				sesJqueryObject('#sesevent_ajax_form_submit').before('<div class="sesbasic_loading_cont_overlay" id="sesdashboard_overlay_content" style="display:block;"></div>');
			else
				sesJqueryObject('#sesdashboard_overlay_content').show();
			//submit form 
			var form = sesJqueryObject('#sesevent_ajax_form_submit');
			var formData = new FormData(this);
			formData.append('is_ajax', 1);
			submitFormAjax = sesJqueryObject.ajax({
            type:'POST',
            url: sesJqueryObject(this).attr('action'),
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(data){
							sesJqueryObject('#sesdashboard_overlay_content').hide();
							var dataJson = data;
						try{
							var dataJson = JSON.parse(data);
						}catch(err){
							//silence
						}
							if(dataJson.redirect){
								sesJqueryObject('#'+dataJson.redirect).trigger('click');
								return;
							}else{
								if(data){
										sesJqueryObject('.sesbasic_dashboard_content').html(data);
								}else{
									alert('Something went wrong,please try again later');	
								}
							}
						},
            error: function(data){
            	//silence
						}
        });
		}
});
//validate email
function checkEmail(){
	var email = sesJqueryObject('input[name="event_contact_email"]').val(),
	 emailReg = "/^([w-.]+@([w-]+.)+[w-]{2,4})?$/";
	if(!emailReg.test(email) || email == '')
	{
			 return false;
	}
	return true;
}
//validate phone number
function checkPhone(){
	var phone = $('input[name="event_contact_phone"]').val(),
		intRegex = "/[0-9 -()+]+$/";
	if((phone.length < 6) || (!intRegex.test(phone)))
	{
		 return false;
	}
	return true;
}
//cancel ticket form
function cancelTicketCreate(){
		sesJqueryObject('#manage-ticket').trigger('click');
}
//open payment details page
function paymentDetail(){
	sesJqueryObject('#dashboard_account_details').trigger('click');
};
sesJqueryObject(document).on('click','#sesevent_currency_coverter',function(){
	var url = "<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'currency-converter'), 'sesevent_dashboard', true); ?>";
	openURLinSmoothBox(url);
	return false;
});
function manageSponsorship(){
	sesJqueryObject('#sesevent_manage_sponsorships').trigger('click');
}
  sesJqueryObject(document).on('submit', '#manage_sponsorship_search_form', function (event) {
    event.preventDefault();
    var searchFormData = sesJqueryObject(this).serialize();
    sesJqueryObject('#loadingimgsesevent-wrapper').show();
    new Request.HTML({
      method: 'post',
      url: en4.core.baseUrl + 'sesevent/sponsorship/manage-sponsorship/event_id/<?php echo $this->event->custom_url; ?>',
      data: {
        format: 'html',
        event_id: '<?php echo $this->event->event_id; ?>',
        searchParams: searchFormData,
        is_search_ajax: true,
      },
      onComplete: function (response) {
        sesJqueryObject('#loadingimgsesevent-wrapper').hide();
        sesJqueryObject('#sesevent_manage_tickets_content').html(response);
      }
    }).send();
  });
</script>
