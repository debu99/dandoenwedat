<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvancedactivity
 * @package    Sesadvancedactivity
 * @copyright  Copyright 2016-2017 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2017-01-12 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/styles/datepicker/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/datepicker/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/datepicker/bootstrap-datepicker.js'); ?>
<style>
  #date{display:block !important;}
</style>
<h3><?php echo "Custom Feed Notification Settings"; ?></h3>
<p><?php echo "Here, you can create custom notifications which will display in feed on your website. You can create new custom notification by clicking on “Create New Custom Notification” link below. These notifications will display in the feed on Member Home Page only."; ?></p><br />
<div class="sesbasic_search_reasult">
	<?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedactivity', 'controller' => 'events', 'action' => 'create'), $this->translate("Create New Custom Notification"), array('class'=>'sesbasic_icon_add buttonlink smoothbox')) ?>
</div>
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate('Are you sure you want to delete the selected events ?') ?>");
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
<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
  <?php endif; ?>
  <div>
        <?php if( count($this->paginator) ): ?>
 					 <div class="sesbasic_search_reasult">
    <?php echo $this->translate(array('%s Event found.', '%s Events found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
  			<?php endif; ?>
        <?php if(count($this->paginator) > 0):?>
        	<div class="sesbasic_manage_table">
          	<div class="sesbasic_manage_table_head" style="width:100%;">
              <div style="width:4%">
                <input onclick='selectAll();' type='checkbox' class='checkbox' />
              </div>
              <div style="width:4%">
                <?php echo "Id";?>
              </div>
              <div style="width:32%">
               <?php echo $this->translate("Title") ?>
              </div>
              <div style="width:10%">
               <?php echo $this->translate("Date") ?>
              </div>
              <div style="width:20%" class="admin_table_centered">
               <?php echo $this->translate("Image") ?>
              </div>
              <div style="width:10%" class="admin_table_centered">
               <?php echo $this->translate("Status") ?>
              </div>
              <div style="width:20%">
               <?php echo $this->translate("Options") ?>
              </div>
            </div>
          	<ul class="sesbasic_manage_table_list" id='menu_list' style="width:100%;">
            <?php foreach ($this->paginator as $item) : ?>
              <li class="item_label" id="content_<?php echo $item->getIdentity() ?>" style="cursor:pointer;">
                <div style="width:4%;">
                  <input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity() ?>' />
                </div>
                <div style="width:4%;">
                  <?php echo $item->getIdentity(); ?>
                </div>
                <div style="width:32%;">
                  <?php echo $item->title; ?>
                </div> 
                <div style="width:10%;">
                  <?php echo date('Y-m-d',strtotime($item->date)); ?>
                </div> 
                <div style="width:20%;" class="admin_table_centered">
                  <?php if($item->file_id){ ?>
                  <img src="<?php echo Engine_Api::_()->storage()->get($item->file_id, '')->getPhotoUrl(); ?>" style="max-width:100px;max-height:100px;">
                  <?php }else{ ?>
                  
                  <?php } ?>
                </div> 
                <div style="width:10%;" class="admin_table_centered">
                  <?php echo ( $item->active ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedactivity', 'controller' => 'events', 'action' => 'enabled', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Disable'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedactivity', 'controller' => 'events', 'action' => 'enabled', 'id' => $item->getIdentity()), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Enable')))) ) ?>
                </div>                
                <div style="width:20%;">          
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesadvancedactivity', 'controller' => 'events', 'action' => 'create', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class'=>'smoothbox')) ?>
            |
            <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sesadvancedactivity', 'controller' => 'events', 'action' => 'delete', 'id' => $item->getIdentity()),
                $this->translate("Delete"),
                array('class' => 'smoothbox')) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          	<div class='buttons'>
            <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
          </div>
          </div>
        <?php else:?>
          <div class="tip">
            <span>
              <?php echo "There are no Events added by you.";?>
            </span>
          </div>
        <?php endif;?>
      </div>
  <br />
  </form>
  <br />
  <div>
  </div>
  <script type="text/javascript">
  function executeAfterLoad(){
    if(!sesBasicAutoScroll('#date').length )
      return;
    var FromEndDateOrder;
    var selectedDateOrder =  new Date(sesBasicAutoScroll('#date').val());
    sesBasicAutoScroll('#date').datepicker({
        format: 'yyyy-mm-dd',
        weekStart: 1,
        autoclose: true,
        endDate: FromEndDateOrder, 
    }).on('changeDate', function(ev){
      selectedDateOrder = ev.date;  
      sesBasicAutoScroll('#date').datepicker('setStartDate', selectedDateOrder);
    });
  }
  executeAfterLoad();
</script>