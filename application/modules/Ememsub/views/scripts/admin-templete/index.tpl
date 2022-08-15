<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Ememsub
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2020-01-17 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php include APPLICATION_PATH .  '/application/modules/Ememsub/views/scripts/dismiss_message.tpl';?>

<h3><?php echo $this->translate("Manage Template") ?></h3>
<p class="ememsub_search_reasult"><?php echo $this->translate('From here, manage all the templates you have created for the membership subscription plans on your website. You can create as many templates as you want for the Plans.'); ?></p>
<div class="ememsub_search_reasult">
  <?php echo $this->htmlLink(array('action' => 'add-template', 'reset' => false), $this->translate('Add New Template'), array(
    'class' => 'buttonlink ememsub_icon_add smoothbox',
  )) ?>
</div>
<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if( count($this->paginator) ): ?>
  <div class="ememsub_search_reasult">
    <?php echo $this->translate(array('%s Template found.', '%s Templates found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>">
    <div class="admin_table_form">
      <table class='admin_table'>
        <thead>
          <tr>
            <th style="width:1%;" class='admin_table_short'><?php echo $this->translate("ID") ?></th>
            <th style="width:40%;"><?php echo $this->translate("Title") ?></th>
            <th style="width:40%;" align="center"><?php echo $this->translate("Active") ?></th>
            <th style="width:20%;"><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->paginator as $item):?>
          <tr>
            <td><?php echo $item->template_id ?></td>
            <td><?php echo $this->translate(Engine_Api::_()->ememsub()->textTruncation($item->getTitle(),50)); ?></td>
            <td class="admin_table_centered">
              <?php if($item->active): ?>
                <img src="application/modules/Core/externals/images/notice.png" alt="Default" />
              <?php else: ?>
                <?php echo $this->formRadio('default', $item->template_id, array('onchange' => "setDefault({$item->template_id});"), ''); ?>
              <?php endif; ?>
            </td>
            <td>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'edit-template', 'template_id' => $item->template_id), $this->translate("Edit"),array('class'=>'smoothbox')) ?>
              |
               <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'templates', 'template_id' => $item->template_id), $this->translate("Manage Template Style")) ?> 
              |
               <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'delete', 'template_id' => $item->template_id), $this->translate("Delete"),array('class'=>'smoothbox')) ?> 
              | 
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'preview', 'template_id' => $item->template_id), $this->translate("Preview"),array('class'=>'smoothbox')) ?> 
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
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no templates created by you yet.") ?>
    </span>
  </div>
<?php endif; ?>


<script type="text/javascript">
  function setDefault(template_id) {
    (new Request.JSON({
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'ememsub', 'controller' => 'admin-templete', 'action' => 'set-default'), 'default', true) ?>',
      'data' : {
        'format' : 'json',
        'template_id' : template_id
      },
      'onRequest' : function(){
        $$('input[type=radio]').set('disabled', true);
      },
      'onSuccess' : function(responseJSON, responseText)
      {
        window.location.reload();
      }
    })).send();

  }
</script>
