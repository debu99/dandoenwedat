<h2><?php echo $this->translate('SocialEngineSolutions Basic Required Plugin'); ?></h2>
<?php if (count($this->navigation)): ?>
  <div class='tabs'>
    <?php
    // Render the menu
    echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<h3><?php echo $this->translate("Manage Currency") ?></h3><br />
     <p class="description"> This page list all the currencies you can enable on your website for all the plugins from SocialEngineSolutions [The compatible currencies are the ones coming in "Fully Supported" section in Currency Dropdown <a href="/admin/payment/settings" target="_blank">here</a>]. 
<br /><br />
The default currency can be chosen one time and can not be changed later. (If you wish to choose the default currency on your website, please contact our support team from  <a href="http://www.socialenginesolutions.com/tickets/" target="_blank">here</a>.) <br /><br />
The price of the content will be saved in Default currency in database and will be shown in different currencies according to the Currency Rate below. You can manually enter the currency rates with below given formula or click on "Update Currency Rates" link to update the currencies.
<br /><br />
<strong class="bold">Formula:</strong> To enter currency rates:<br />
1 Default Currency = Desired Currency Value
<br /><br />
<strong class="bold">For example:</strong> If US Dollar is default currency and<br />
1 US Dollar = 1.33 Australian Dollar<br />
Then Currency rate will be 1.33 for Australian Dollar.</p>

<div>
<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesbasic', 'controller' => 'settings', 'action' => 'update-currency'),'Update Currency Values', array('class' => 'buttonlink sesbasic_icon_edit')) ?>
</div>
<br />
<div class='clear'>
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'>ID</th>
        <th><?php echo $this->translate('Currency Name') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Currency Symbol') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Currency Rate') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Enabled') ?></th>
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
            <td class="admin_table_centered"><?php echo $key; ?></td>
            <td class="admin_table_centered"><?php $getSetting = $settings->getSetting('sesbasic.'.$key);
            //echo "<pre>";var_dump($getSetting);die;
            if($getSetting != '')
              echo $getSetting;
            else
              echo "-";
             ?></td>
             
             <td class="admin_table_centered">
             <?php if($key != $settings->getSetting('sesbasic.defaultcurrency','USD')){ ?>
             	 <?php if($settings->getSetting('sesbasic.'.$key.'active','0')): ?>
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesbasic', 'controller' => 'settings', 'action' => 'active', 'id' => $key), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Disable')))) ?>
              <?php else: ?>
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesbasic', 'controller' => 'settings', 'action' => 'active', 'id' => $key), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Enable')))) ?>
              <?php endif; ?>
             <?php }else{ ?>
             		-
             <?php } ?>
             </td>
             
            <td>
            <?php if($key != $settings->getSetting('sesbasic.defaultcurrency','USD')){ ?>
            <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sesbasic', 'controller' => 'settings', 'action' => 'edit-currency', 'id' => $key),
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