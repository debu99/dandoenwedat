<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage-dashboards.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>


<h3><?php echo "Manage Dashboard Menu Items"; ?></h3>
<p><?php echo "Here, you can manage the event dashboard menu items and edit their titles. You can also enable / disable any menu item from below."; ?> </p>
<br />
<table class='admin_table' style="width:50%;">
  <thead>
    <tr>
      <th><?php echo $this->translate("Menu Item") ?></th>
      <th><?php echo $this->translate("Enabled") ?></th>
      <th><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php $ticketsArray = array('tickets', 'manage_tickets', 'create_tickets', 'event_ticket_information', 'search_ticket', 'account_details', 'sales_statistics', 'manage_orders', 'sales_orders', 'payment_requests', 'payment_transactions'); 
    $sponsorshipArray = array('sponsorship', 'sponsorship_manage', 'sponsorship_create', 'sponsorship_requests', 'sponsorship_sales_stats', 'sponsorship_manage_orders', 'sponsorship_sales_reports', 'sponsorship_payment_requests', 'sponsorship_payment_transactions');
    ?>
    <?php foreach ($this->paginator as $result):  ?>
      <?php
	      if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
		      if(in_array($result->type,$ticketsArray)): 
			      continue; 
			    endif;
	      }
      ?>
      <?php
	      if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventsponsorship') || !Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventsponsorship.pluginactivated')){
		      if(in_array($result->type,$sponsorshipArray)): 
			      continue; 
			    endif;
	      }
      ?>
      <tr>
        <td><?php echo $result->title ?></td>
				<td>
					<?php if(!$result->main): ?>
						<?php echo ( $result->enabled ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'settings', 'action' => 'enabled', 'dashboard_id' => $result->dashboard_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Disable'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'settings', 'action' => 'enabled', 'dashboard_id' => $result->dashboard_id), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Enable')))) ) ?>
					<?php else: ?>
						<?php echo "-"; ?>
					<?php endif; ?>
				</td>
        <td>
          <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-settings', 'action' => 'edit-dashboards-settings', 'dashboard_id' => $result->dashboard_id), $this->translate("Edit"), array('class' => 'smoothbox')) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>