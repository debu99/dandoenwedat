<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<h3>
  <?php echo $this->translate("Manage Payment Gateways") ?>
</h3>
<p>
  <?php echo $this->translate("PAYMENT_VIEWS_ADMIN_GATEWAYS_INDEX_DESCRIPTION") ?>
</p>


<br />
<br />
<?php if( !empty($this->error) ): ?>
  <ul class="form-errors">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>
  <br />
<?php endif; ?>
<div class='sesbasic_search_reasult'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s gateway found", "%s gateways found", $count), $count) ?>
  </div>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
</div>
<table class='admin_table' style='width: 40%;'>
  <thead>
    <tr>
      <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
      <th><?php echo $this->translate("Title") ?></th>
      <th style='width: 1%;' class='admin_table_centered'><?php echo $this->translate("Enabled") ?></th>
      <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if( count($this->paginator) ): ?>
      <?php foreach( $this->paginator as $item ): ?>
        <tr>
          <td>
            <?php echo $item->gateway_id ?>
          </td>
          <td class='admin_table_bold'>
            <?php echo $item->title ?>
          </td>
          <td class='admin_table_centered'>
            <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
          </td>
          <td class='admin_table_options'>
            <a href='<?php echo $this->url(array('action' => 'edit', 'gateway_id' => $item->gateway_id));?>'>
              <?php echo $this->translate("edit") ?>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>