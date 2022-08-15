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
<h3><?php echo $this->translate("Manage Hosts"); ?></h3>
<br />
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
 <p><?php echo $this->translate('This page lists all of the hosts your users have added to their events. These hosts can be site members or non-site members. You can use this page to monitor these hosts and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific host. Leaving the filter fields blank will show all the hosts on your social network.<br /><br />Below, you can also choose any number of hosts as Host of the Day, Featured, Sponsored or Verified.<br /><br />You can edit and delete only those hosts who are not members of your website.') ?></p>
      <br />
			
<div class='admin_search sesbasic_search_form'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />
<div class='clear settings'>
  <form id='multidelete_form' method="post" action="<?php echo $this->url(array('module' => 'sesevent', 'controller' => 'host', 'action' => 'multi-delete'), 'admin_default'); ?>" onSubmit="return multiDelete()">
    <div>
     
      <?php if(count($this->paginator) > 0):?>
        <div class="admin_table_form">
          <table class="admin_table">
          	<thead>
              <tr>
              <!--<div style="width:5%">
                <input onclick="selectAll()" type='checkbox' class='checkbox'>
              </div>-->
              <th style="width:20%">
                <?php echo "Host Name";?>
              </th>
              <th style="width:20%">
                <?php echo "Host Photo";?>
              </th>
              <th class="admin_table_centered" style="width:15%">
                <?php echo "Events Hosted";?>
              </th>
              <th class="admin_table_centered" style="width:5%" title="Featured">
                <?php echo "F";?>
              </th>
              <th class="admin_table_centered" title="Sponsored" style="width:5%">
                <?php echo "S";?>
              </th>
              <th class="admin_table_centered" title="Of the Day" style="width:10%">
                <?php echo "OTD";?>
              </th>
              <th class="admin_table_centered" title="Verified" style="width:5%">
                <?php echo "V";?>
              </th>
              <th style="width:20%">
                <?php echo "Action";?>
              </th>
          	</tr>
          </thead>
          <tbody>  
            <?php foreach ($this->paginator as $item):
            $user = $this->item('user', $item->user_id); ?>
              <tr class="item_label">
                <input type='hidden'  name='order[]' value='<?php echo $item->host_id; ?>'>
                <!--<div style="width:5%;">
                  <input name='delete_<?php echo $item->host_id ?>_<?php echo $item->host_id ?>' type='checkbox' class='checkbox' value="<?php echo $item->host_id ?>_<?php echo $item->host_id ?>"/>
                </div>-->
                <td style="width:20%;">
                  <?php echo $this->htmlLink($item->getHref(), $this->string()->truncate($item->host_name, 20), array('target' => '_blank', 'title' => $item->getTitle()))?>
                </td>
                <td style="width:20%;">
                  <img src="<?php echo $item->getPhotoUrl(); ?>" height="100" width="100" />
                </td>
                <td class="admin_table_centered" style="width:15%">
                  <?php echo  Engine_Api::_()->getDbtable('events', 'sesevent')->getHostEventCounts(array('host_id' => $item->host_id, 'type' => $item->type));  ?>
                </td>
                <td class="admin_table_centered" style="width:5%;">
                  <?php echo ( $item->featured ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'host', 'action' => 'featured', 'host_id' => $item->host_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Unmark as Featured'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'host', 'action' => 'featured', 'host_id' => $item->host_id), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Mark Featured')))) ) ?>
                </td>
                <td class="admin_table_centered" style="width:5%;">
                  <?php echo ( $item->sponsored ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'host', 'action' => 'sponsored', 'host_id' => $item->host_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Unmark as Sponsored'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'host', 'action' => 'sponsored', 'host_id' => $item->host_id), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Mark Sponsored')))) ) ?>
                </td>
                <td class="admin_table_centered" style="width:5%;">
                  <?php if($item->offtheday == 1):?>  
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'manage', 'action' => 'oftheday', 'id' => $item->host_id, 'type' => 'sesevent_host', 'param' => 0), $this->htmlImage($baseURL . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title'=> $this->translate('Edit Host Member of the Day'))), array('class' => 'smoothbox')); ?>
                  <?php else: ?>
                    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'manage', 'action' => 'oftheday', 'id' => $item->host_id, 'type' => 'sesevent_host', 'param' => 1), $this->htmlImage($baseURL . 'application/modules/Sesbasic/externals/images/icons/error.png', '', array('title'=> $this->translate('Make Host Member of the Day'))), array('class' => 'smoothbox')) ?>
                  <?php endif; ?>
                </td>
                <td class="admin_table_centered" style="width:5%;">
                  <?php echo ( $item->verified ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'host', 'action' => 'verified', 'host_id' => $item->host_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png', '', array('title' => $this->translate('Unmark as Verified'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesevent', 'controller' => 'host', 'action' => 'verified', 'host_id' => $item->host_id), $this->htmlImage('application/modules/Sesbasic/externals/images/icons/error.png', '', array('title' => $this->translate('Mark as Verified')))) ) ?>
                </td>
                <td style="width:20%;">
                	<a href="<?php echo $item->getHref(); ?>" target="_blank" title="<?php echo $this->translate("View") ?>">View
                   </a>
                	 <?php  $viewer = Engine_Api::_()->user()->getViewer(); ?>
                	 <?php if($viewer->level_id == 1 ): ?>
                   	<?php if(!Engine_Api::_()->getApi('settings', 'core')->getSetting('sesevent.sitehostredirect', 1) || $item->type == 'offsite'){ ?>
                      | <a href="<?php echo $this->url(array('action'=>'edit', 'host_id'=>$item->getIdentity()),'sesevent_host',true) ?>" target="_blank" title="<?php echo $this->translate("Edit") ?>">Edit
                      </a>
                      <?php } ?>
                     <?php if($item->type == 'offsite'){ ?>
                      | <a href="<?php echo $this->url(array('action'=>'delete', 'host_id'=>$item->getIdentity(),'format' => 'smoothbox','admin'=>true),'sesevent_host',true) ?>" class="sesbasic_icon_btn smoothbox" title="<?php echo $this->translate("Delete") ?>">Delete
                      </a>
                     <?php } ?>
                   <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
        	</tbody>
        </table>
        </div>
      <?php else:?>
        <div class="tip">
          <span>
            <?php echo "There are no host members yet.";?>
          </span>
        </div>
      <?php endif;?>
    </div>
  </form>
</div>

<script type="text/javascript"> 
 
  function selectAll(){
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;

    for (i = 1; i < inputs.length - 1; i++) {
      if (!inputs[i].disabled) {
       inputs[i].checked = inputs[0].checked;
      }
    }
  }
  
  function multiDelete(){
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure that you want to delete this host? It will not be recoverable after being deleted and the events hosted by this host will now be hosted by the owner of those events.")) ?>');
  }
</script>