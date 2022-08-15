<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: templates.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>
<div class="ememsub_search_reasult">
  <?php echo $this->htmlLink(array('action' => 'index', 'reset' => false), $this->translate('Back to Manage Templates'), array('class' => 'buttonlink ememsub_icon_back')) ?>
</div>
<h3><?php echo $this->translate("Manage Template Style") ?></h3>
<p class="ememsub_search_reasult"><?php echo $this->translate('From this section, you can change styling for each plan which you have created from the %s.',$this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'package','reset' => true), $this->translate('SE Plans page'), array())); ?></p>

<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if( count($this->paginator) ): ?>
  <div class="ememsub_search_reasult">
    <?php echo $this->translate(array('%s Plan found.', '%s Plans found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>">
    <div class="admin_table_form">
      <table class='admin_table'>
        <thead>
          <tr>
            <th class='admin_table_short' style="width:1%;"><?php echo $this->translate("ID") ?></th>
            <th style="width:60%;"><?php echo $this->translate("Title") ?></th>
            <th style="width:20%;"><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->paginator as $item):?>
          <tr>
            <td><?php echo $item->package_id ?></td>
            <td><?php echo $this->translate(Engine_Api::_()->ememsub()->textTruncation($item->getTitle(),50)); ?></td>
            <td>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'create', 'package_id' => $item->package_id,'template_id'=>$this->template_id), $this->translate("Change style")) ?> 
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </form>
  <div>
    <?php echo $this->paginationControl($this->paginator,null,null,$this->urlParams); ?>
  </div>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no templete created by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>
     
