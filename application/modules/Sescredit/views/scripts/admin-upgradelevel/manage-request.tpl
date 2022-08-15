<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage-request.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      $('order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      $('order').value = order;
      $('order_direction').value = default_direction;
    }
    $('filter_form').submit();
  }
</script>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
	<h3><?php echo $this->translate("Manage Membership Upgrades") ?></h3>
	<p><?php echo $this->translate('Here, you can configure the membership upgradation requests and points on your website. Below in the "Manage Points for Upgrades" section, enter the points which will be required to upgrade to that respective member level on your website.'); ?></p>
<br />
<div class="clear sesbasic-form">
  <div>  
    <?php if( count($this->subNavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subNavigation)->render();?>
      </div>
    <?php endif; ?>
    <div class="sesbasic-form-cont">
	  <h3>Membership Upgrade Request</h3>
      <p>This page lists all the membership upgrade requests your users have requested for upgrading their memberships in exchange of their credit points. You can Approve or Reject the requests from here and search the requests using the search filter.</p>
	  <br />
      <div class='admin_search sescredit_memberspoints_search sesbasic_bxs'>
        <?php echo $this->formFilter->render($this) ?>
      </div>
      <br />
      <?php $counter = $this->paginator->getTotalItemCount(); ?>
      <?php if($this->paginator->getTotalItemCount() > 0):?>
        <div class="sesbasic_search_reasult">
          <?php echo $this->translate(array('%s request found.', '%s requests found.', $counter), $this->locale()->toNumber($counter)) ?>
        </div>
        <div class="sescredit_mytransactions ">
          <div class="transactions_table" id='sescredit_table_contaner'>
            <table class="admin_table">
              <thead class="sesbasic_lbg">
                <tr>
                  <th class='admin_table_short'><a href="javascript:void(0);" onclick="javascript:changeOrder('user_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
                  <th><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate("Owner") ?></a></th>
                  <th align="class"><a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', 'ASC');"><?php echo $this->translate("Current Member Level") ?></a></th>
                  <th align="class"><a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', 'ASC');"><?php echo $this->translate("Requested Member Level") ?></a></th>
                  <th align="class"><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'ASC');"><?php echo $this->translate("Request Date") ?></a></th>
                  <th class="_options" rowspan="2">Options</th>
                </tr>
              </thead>
              <tbody class="sescredit_transactions" id="activity-transaction">
              <?php foreach($this->paginator as $user):?>
                <tr>
                  <td><?php echo $user->user_id;?></td>
                  <td><a href="<?php echo $user->getHref();?>" ><?php echo $user->displayname;?></a></td>
                  <td class="admin_table_cantered"><?php echo Engine_Api::_()->getDbTable('upgradeusers','sescredit')->getLevelName(Engine_Api::_()->getItem('user', $user->owner_id)->level_id);?></td>
                  <td class="admin_table_cantered"><?php echo Engine_Api::_()->getDbTable('upgradeusers','sescredit')->getLevelName($user->level_id);?></td>
                  <td><?php echo $user->creation_date;?></td>
                  <?php if(!$user->status):?>
                    <td class="_options"><a class="smoothbox" href="<?php echo $this->url(array('module' => 'sescredit','controller' => 'upgradelevel','action' => 'approve','id' => $user->upgradeuser_id),'admin_default',true);?>"><?php echo $this->translate("Approve");?> | <a class="smoothbox" href="<?php echo $this->url(array('module' => 'sescredit','controller' => 'upgradelevel','action' => 'reject','id' => $user->upgradeuser_id),'admin_default',true);?>"><?php echo $this->translate("Reject");?></a></td>
                  <?php elseif($user->status == 1):?>
                    <td class="_options"><?php echo "Approved";?></td>
                  <?php elseif($user->status == 2):?>
                    <td class="_options"><?php echo "Rejected";?></td>
                  <?php endif;?>
                </tr>
              <?php endforeach;?>
              </tbody>
            </table>
          </div>
        </div>
        <br/>
        <div>
          <?php echo $this->paginationControl($this->paginator,null,null,$this->urlParams); ?>
        </div>
      <?php else:?>
        <div class="tip">
          <span class="sesbasic_text_light">
            <?php echo $this->translate('There are no requests found.') ?>
          </span>
        </div>
      <?php endif;?>
    </div>
  </div>
</div>