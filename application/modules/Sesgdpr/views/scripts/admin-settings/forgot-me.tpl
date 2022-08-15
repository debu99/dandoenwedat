<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: forget-me.tpl 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesgdpr/views/scripts/dismiss_message.tpl';?>


<h3><?php echo $this->translate('Forget Users & Erasure Requests') ?></h3>
<p><?php echo $this->translate('This page lists all the requests made by the users of your website asking to forget them from your website and erase all the data from the site. You can take action on their requests from below.<br><br>SocialEngine also provides Delete account option to each user in their Settings section, but still if a user is not able to do delete his account, then he may ask you to delete all of the data currently possessed on your website.'); ?></p>
<br />

<br />
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
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
<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s forgot me request found.', '%s forgot me requests found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
    <table class='admin_table'>
      <thead>
        <tr>
          <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
          <th class='admin_table_short'><a href="javascript:void(0);"><?php echo $this->translate("ID") ?></a></th>
          <th><a href="javascript:void(0);"><?php echo $this->translate("Name") ?></a></th>
          <th><a href="javascript:void(0);" ><?php echo $this->translate("Email") ?></a></th>
          <th><a href="javascript:void(0);" ><?php echo $this->translate("Date") ?></a></th>
          <th><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value="<?php echo $item->getIdentity(); ?>" /></td>
          <td><?php echo $item->getIdentity() ?></td>
          <td><?php echo $item->name; ?></td>
          <td><a href="mailto:<?php echo $item->email; ?>" target="_blank"><?php echo $item->email; ?></a></td>
          <td><?php echo $item->creation_date; ?></td>
          <td>
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesgdpr', 'controller' => 'settings', 'action' => 'view', 'id' => $item->getIdentity()), $this->translate("View"), array('class' => 'smoothbox')) ?>
            |
            <?php 
            if(!$item->replied){
              echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesgdpr', 'controller' => 'settings', 'action' => 'reply', 'id' => $item->getIdentity()), $this->translate("Reply"), array('class' => 'smoothbox')); 
            }else{
              echo $this->htmlLink('javascript:;', $this->translate("Replied"), array('class' => '')); 
            }
            ?>
            |
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesgdpr', 'controller' => 'settings', 'action' => 'note', 'id' => $item->getIdentity()), $this->translate("Note"), array('class' => 'smoothbox')); ?>
            |
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesgdpr', 'controller' => 'settings', 'action' => 'delete', 'id' => $item->getIdentity()), $this->translate("Delete"), array('class' => 'smoothbox')) ?>
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
      <?php echo $this->translate('There are no Forget Me requests made by your members yet.') ?>
    </span>
  </div>
<?php endif; ?>