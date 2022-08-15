<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: privacy.tpl 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesgdpr/views/scripts/dismiss_message.tpl';?>


<h3><?php echo $this->translate('') ?></h3>
<p><?php echo $this->translate('Here, you can add the 3rd party services from other social networking websites like Facebook Linkedin, Youtube, etc which you use on your website for login, uploading video, sharing on them, importing photos, or any other purpose. You can also enable / disable and delete the services.<br>
Users on your website will see an “Opt Out” option for the added and enabled service. They will be redirected to the configured link to adjust the settings of another 3rd party services on their respective sites.  
'); ?></p>
<br />


<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
 
 
<div class="">
  <a href="admin/sesgdpr/settings/add-service" class="buttonlink smoothbox sesgdpr_icon_add">Create New Service</a>  
</div>


<script type="text/javascript">

  function multiDelete() {
    return confirm("<?php echo $this->translate('Are you sure you want to delete the selected item?');?>");
  }

  function selectAll() {
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length; i++) {
      if (!inputs[i].disabled) {
        inputs[i].checked = inputs[0].checked;
      }
    }
  }

</script>
<br>
<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s service found.', '%s services found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
    <table class='admin_table'>
      <thead>
        <tr>
          <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
          <th class='admin_table_short'><a href="javascript:void(0);"><?php echo $this->translate("ID") ?></a></th>
          <th><a href="javascript:void(0);"><?php echo $this->translate("Name") ?></a></th>
          <th><a href="javascript:void(0);"><?php echo $this->translate("URL") ?></a></th>
          <th><a href="javascript:void(0);" ><?php echo $this->translate("Status") ?></a></th>
          <th><a href="javascript:void(0);"><?php echo $this->translate("Creation Date") ?></a></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value="<?php echo $item->getIdentity(); ?>" /></td>
          <td><?php echo $item->getIdentity() ?></td>
          <td><?php echo $item->name; ?></td>
          <td><a href="<?php echo $item->url; ?>" target="_blank"><?php echo $item->url; ?></a></td>
          <td>
            <?php if($item->enabled == 1):?>
            <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesgdpr', 'controller' => 'admin-settings', 'action' => 'service-approved', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesgdpr/externals/images/icons/check.png', '', array('title'=> $this->translate('Disabled')))) ?>
          <?php else: ?>
            <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesgdpr', 'controller' => 'admin-settings', 'action' => 'service-approved', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesgdpr/externals/images/icons/error.png', '', array('title'=> $this->translate('Enabled')))) ?>
          <?php endif; ?>
          
          </td>
          <td><?php echo $item->creation_date; ?></td>
          <td>
            <?php 
              echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesgdpr', 'controller' => 'settings', 'action' => 'add-service', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class' => 'smoothbox')); 
            ?>
            |
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesgdpr', 'controller' => 'settings', 'action' => 'delete-service', 'id' => $item->getIdentity()), $this->translate("Delete"), array('class' => 'smoothbox')) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <br />
    <div class='buttons'>
      <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
    </div>
  </form>
  <br/>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
<?php else:?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no service created by you yet.") ?>
    </span>
  </div>
<?php endif; ?>