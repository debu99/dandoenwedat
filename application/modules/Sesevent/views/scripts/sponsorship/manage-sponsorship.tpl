<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: manage-sponsorship.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php
if(!$this->is_search_ajax):
if(!$this->is_ajax):
echo $this->partial('dashboard/left-bar.tpl', 'sesevent', array('event' => $this->event));?>
<div class="sesbasic_dashboard_content sesbm sesbasic_clearfix">
  <?php endif; 
  echo $this->partial('dashboard/event_expire.tpl', 'sesevent', array('event' => $this->event)); 
endif;
?>
  <?php if(!$this->is_search_ajax): ?>

  <div class="sesbasic_dashboard_content_header sesbasic_clearfix">
    <h3><?php echo $this->translate('Manage Sponsorships') ?>
      <a class="sesbasic_button fa fa-plus floatR" href="<?php echo $this->url(array('event_id' => $this->event->custom_url,'action'=>'create'), 'sesevent_sponsorship', true); ?>"><?php echo $this->translate('Create New Sponsorship'); ?></a>
    </h3>
  </div>
  <div class="sesbasic_browse_search sesbasic_browse_search_horizontal sesbasic_dashboard_search_form">
    <?php echo $this->searchForm->render($this); ?>
  </div>
  <?php endif;?>
  <div id="sesevent_manage_tickets_content">
    <?php if( count($this->eventSponsorship) > 0): ?>
    <div class="sesbasic_dashboard_table sesbasic_bxs">
      <form method="post" >
        <table>
          <thead>
            <tr>
              <th class="centerT"><?php echo $this->translate("ID"); ?></th>
              <th><?php echo $this->translate("Sponsorship Title") ?></th>
              <th><?php echo $this->translate("Status") ?></th>
              <th><?php echo $this->translate("Quantity") ?></th>
              <th><?php echo $this->translate("Price") ?></th>
              <th><?php echo $this->translate("Photo") ?></th>
              <th><?php echo $this->translate("Options") ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($this->eventSponsorship as $item): ?>
            <tr>
              <td class="centerT"><?php echo $item->sponsorship_id ?></td>
              <td><?php echo $item->title; ?></td>
              <td class="centerT"><?php echo ($item->status) ? 'Published' : 'Draft' ; ?></td>
               <td><?php echo $item->total ? $item->total : 'Unlimited'; ?></td>
              <td><?php echo Engine_Api::_()->sesevent()->getCurrencyPrice($item->price); ?></td>
              <td><img src="<?php echo $item->getPhotoUrl(); ?>"  style="height:40px; width:40px;" /></td> 
              <td class="table_options">
                <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'edit','id'=>$item->sponsorship_id), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Edit Sponsorship"), 'class' => 'fa fa-edit')) ?>
                <?php echo $this->htmlLink($this->url(array('event_id' => $this->event->custom_url,'action'=>'delete-sponsorship','id'=>$item->sponsorship_id), 'sesevent_sponsorship', true), $this->translate(""), array('title' => $this->translate("Delete Sponsorship"), 'class' => 'sesevent_ajax_delete fa fa-trash')) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </form>
    </div>
    <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate("No Sponsorship created yet.") ?>
      </span>
    </div>
    <?php endif; ?>
  </div>

  <?php if(!$this->is_search_ajax): 
  if(!$this->is_ajax): ?>
</div>
</div>
<?php endif; endif; ?>
<script>
  sesJqueryObject('#loadingimgsesevent-wrapper').hide();
</script>