<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: list.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>
<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected events?") ?>");
}
function selectAll()
{
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
<h3><?php echo $this->translate("Manage Lists") ?></h3>
<p>
	<?php echo $this->translate('This page lists all of the lists your users have created. You can use this page to monitor these lists and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific lists. Leaving the filter fields blank will show all the lists on your social network.
Below, you can also choose any number of lists as Lists of the Day, Featured and Sponsored.'); ?>
</p>
<br />
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<?php if( count($this->paginator) ): ?>
  <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s list found.', '%s lists found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
        <th class='admin_table_short'>ID</th>
        <th><?php echo $this->translate("Title") ?></th>
        <th><?php echo $this->translate("Owner") ?></th>
        <th><?php echo $this->translate("Total Events") ?></th>
        <th align="center"><?php echo $this->translate('Featured') ?></th>
        <th align="center"><?php echo $this->translate('Sponsored') ?></th>
        <th align="center"><?php echo $this->translate("Of the Day") ?></th>
        <th><?php echo $this->translate("Options") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->list_id;?>' value='<?php echo $item->list_id ?>' /></td>
          <td><?php echo $item->getIdentity() ?></td>
          <td><?php echo  $this->htmlLink($item->getHref(), $item->getTitle()); ?></td>
          <td><?php echo $this->htmlLink($item->getHref(), $item->getOwner()); ?></td>
          <td><?php echo $item->countEvents(); ?></td>
          <td class="admin_table_centered"><?php echo $item->is_featured == 1 ?   $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'feature-sponsored', 'id' => $item->list_id,'status' =>0,'category' =>'featured','param'=>'lists'),$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Unmark as Featured')))) : $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'feature-sponsored', 'id' => $item->list_id,'status' =>1,'category' =>'featured','param'=>'lists'),$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Mark Featured')))) ; ?></td>
            <td class="admin_table_centered"><?php echo $item->is_sponsored == 1 ? $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'feature-sponsored', 'id' => $item->list_id,'status' =>0,'category' =>'sponsored','param'=>'lists'),$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Unmark as Sponsored')))) : $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'feature-sponsored', 'id' => $item->list_id,'status' =>1,'category' =>'sponsored','param'=>'lists'),$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Mark Sponsored'))))  ; ?></td>
          <td class="admin_table_centered">
          		<?php if(strtotime($item->enddate) < strtotime(date('Y-m-d')) && $item->offtheday == 1){ 
            			Engine_Api::_()->getDbtable('lists', 'sesevent')->update(array(
                      'offtheday' => 0,
                      'startdate' =>'',
                      'enddate' =>'',
                    ), array(
                      "list_id = ?" => $item->list_id,
                    ));
                    $itemofftheday = 0;
             }else
             	$itemofftheday = $item->offtheday; ?>
            <?php if($itemofftheday == 1):?>  
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'oftheday', 'id' => $item->list_id, 'type' => 'sesevent_list', 'param' => 0), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Edit List of the Day'))), array('class' => 'smoothbox')); ?>
            <?php else: ?>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'oftheday', 'id' => $item->list_id, 'type' => 'sesevent_list', 'param' => 1), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Make List of the Day'))), array('class' => 'smoothbox')) ?>
            <?php endif; ?>
          </td>
          <td>
          	<?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'view-list', 'type'=> 'sesevent_list', 'id' => $item->list_id), $this->translate("View Details"), array('class' => 'smoothbox')) ?>
            |
            <a href="<?php echo $item->getHref(); ?>" target="_blank"><?php echo $this->translate("View") ?></a>
            |
            <?php echo $this->htmlLink(
                array('route' => 'default', 'module' => 'sesevent', 'controller' => 'admin-manage', 'action' => 'delete-list', 'id' => $item->list_id),
                $this->translate("Delete"),
                array('class' => 'smoothbox')) ?>
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
  <br />
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>

<?php else: ?>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate("There are currently no lists created by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>
