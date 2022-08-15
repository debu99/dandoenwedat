<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<h3><?php echo $this->translate("Manage Currency") ?></h3><br />
     <p class="description"> This page list all the currencies you can enable on your website [The compatible currencies are the ones coming in “Fully Supported” section in Currency Dropdown here:<a href="http://www.yourwebsiteurl.com/admin/payment/settings"> http://www.yourwebsiteurl.com/admin/payment/settings]</a>. The default currency can be chosen from the Global Settings of this plugin (This is a one time setting and can not be changed later).  
</br>
</br>
Here, you can manage multiple currencies in which Event Tickets will be shown on your website. The price of the ticket will be saved in Default currency in database and will be shown in different currency according to the currency rate below.
</br>
</br>
Formula: To enter to enter currency rate:
</br>
1  Default Currency = Desired Currency Value
</br>
</br>

<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: currency.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<label style = "font-weight: bold;" >For example</label>: If US Dollar is default currency and 
</br>
1 US Dollar = 1.33 Australian Dollar
</br>
Then Currency rate will be 1.33 for Australian Dollar.</p>
<div class='clear '>
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'>ID</th>
        <th><?php echo $this->translate('Currency Name') ?></th>
        <th><?php echo $this->translate('Currency Symbol') ?></th>
        <th><?php echo $this->translate('Currency Rate') ?></th>
        <th><?php echo $this->translate('Action') ?></th>
      </tr>
    </thead>
    <tbody>
        <?php $i =1;
            $settings = Engine_Api::_()->getApi('settings', 'core');
            foreach ($this->fullySupportedCurrencies as $key => $item): ?>
          <tr>
            <td><?php echo $i; ?></td>
            <td><?php echo $item; ?></td>
            <td><?php echo $key; ?></td>
            <td><?php $getSetting = $settings->getSetting('sesevent.'.$key);
            if($getSetting != '')
              echo $getSetting;
            else
              echo "-";
             ?></td>
            <td>
            <?php if($key != Engine_Api::_()->sesevent()->defaultCurrency()){ ?>
            <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'settings', 'action' => 'edit-currency', 'id' => $key),
                $this->translate("edit"),
                array('class' => 'smoothbox')) ?>
            <?php }else{ ?>    
              Default
            <?php } ?>
            </td>
          </tr>
        <?php $i++;endforeach; ?>
    </tbody>
  </table>
</div>


