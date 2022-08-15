<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

 ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $randonNumber = $this->widgetId; ?>
<?php if($this->paginator->getTotalItemCount() > 0):?>
  <?php if(!$this->is_ajax) { ?>
    <div class="sescredit_mytransactions sesbasic_bxs">
      <div class="sescredit_mytransactions_container" id='sescredit_table_contaner_<?php echo $randonNumber; ?>'>
        <div class="sescredit_mytransactions_table">
          <div class="_mytransactionstable_header sesbasic_lbg">
            <div class="_activitytype _label"><?php echo $this->translate("Points Type");?></div>
            <div class="_points">
              <div class="_label"><?php echo $this->translate("Credit Points");?></div>
              <div><i class="sescredit_icon_add fa fa-plus"></i></div>
              <div><i class="sescredit_icon_minus fa fa-minus"></i></div>
            </div>
            <div class="_date _label"><?php echo $this->translate("Date");?></div>
            <div class="_options _label"><?php echo $this->translate("Options");?></div>
          </div>
          <div class="_mytransactionstable_content" id="activity-transaction_<?php echo $randonNumber; ?>">
  <?php } ?>
          <?php foreach($this->paginator as $transaction):?>
            <div class="_mytransactionstable_item">
              <?php if($transaction->point_type == 'affiliate'):?>
                <div class="_activitytype"><?php echo $this->translate("Inviter Affiliation");?></div>
              <?php elseif($transaction->point_type == 'transfer_friend'):?>
                <div class="_activitytype"><?php echo $this->translate("Transferred to Friends");?></div>
                <?php elseif($transaction->point_type == 'sesproduct_order'):?>
                <div class="_activitytype"><?php echo $this->translate("Product Purchased");?></div>
              <?php elseif($transaction->point_type == 'receive_friend'):?>
                <div class="_activitytype"><?php echo $this->translate("Received from Friends");?></div>
              <?php elseif($transaction->point_type == 'purchase'):?>
                <div class="_activitytype"><?php echo $this->translate("Buy from site");?></div>
              <?php elseif($transaction->point_type == 'upgrade_level'):?>
                <div class="_activitytype"><?php echo $this->translate("On Membership Upgrade");?></div>
              <?php else:?>
                <div class="_activitytype"><?php echo $transaction->language;?></div>
              <?php endif;?>
              <?php if($transaction->point_type == 'credit'):?>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
                <div class="_activitypoint">-</div>
              <?php elseif($transaction->point_type == 'deduction'):?>
                <div class="_activitypoint">-</div>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <?php elseif($transaction->point_type == 'transfer_friend'):?>
                <div class="_activitypoint">-</div>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
                <?php elseif($transaction->point_type == 'sesproduct_order'):?>
                <div class="_activitypoint">-</div>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <?php elseif($transaction->point_type == 'affiliate'):?>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
                <div class="_activitypoint">-</div>
              <?php elseif($transaction->point_type == 'receive_friend'):?>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
                <div class="_activitypoint">-</div>
              <?php elseif($transaction->point_type == 'purchase'):?>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
                <div class="_activitypoint">-</div>
              <?php elseif($transaction->point_type == 'upgrade_level'):?>
                <div class="_activitypoint">-</div>
                <div class="_activitypoint"><?php echo $transaction->credit;?></div>
              <?php endif;?>
              <?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/_date.tpl';?>
              <div class="_options"><a class="sessmoothbox" href="<?php echo $this->url(array('action' => 'show-detail','id' => $transaction->credit_id),'sescredit_general',true);?>"><?php echo $this->translate("View Details");?> &raquo;</a></div>
            </div>
          <?php endforeach;?>
      <?php if(!$this->is_ajax) { ?>
          </div>
        </div>
      <?php } ?>
  <?php if(!$this->is_ajax) { ?>
      <div class="sesbasic_load_btn" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" >
        <a href="javascript:void(0);" class="sesbasic_animation sesbasic_link_btn" id="feed_viewmore_link_<?php echo $randonNumber; ?>"><i class="fa fa-sync"></i><span><?php echo $this->translate('View More');?></span></a>
      </div>
      <div class="sesbasic_load_btn sesbasic_view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;">
        <span class="sesbasic_link_btn"><i class="fa fa-spinner fa-spin"></i></span>
      </div>
      </div>
      <div id="sescredit_transaction_loading" class='sesbasic_loading_container' style='display: none;'></div>
    </div>
  <?php } ?>
<?php else:?>
  <div class="activity_transaction_noresult">
    <div id="error-message_<?php echo $randonNumber;?>">
      <div class="sesbasic_tip clearfix">
        <img src="<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('sescredit_contest_no_photo', 'application/modules/Sescredit/externals/images/no-credit.png'); ?>" alt="" />
        <span class="sesbasic_text_light">
          <?php echo $this->translate('There are no credit points found.') ?>
        </span>
      </div>
    </div>
  </div>
<?php endif;?>

<script type='text/javascript'>
  //Start Pagination Work
  var requestViewMore_<?php echo $randonNumber; ?>;
  var params<?php echo $randonNumber; ?> = <?php echo json_encode($this->params); ?>;
  var identity<?php echo $randonNumber; ?>  = '<?php echo $randonNumber; ?>';
  var page<?php echo $randonNumber; ?> = '<?php echo $this->page + 1; ?>';
  var searchParams<?php echo $randonNumber; ?> ;
  var is_search_<?php echo $randonNumber;?> = 0;
  viewMoreHide_<?php echo $randonNumber; ?>();
  function viewMoreHide_<?php echo $randonNumber; ?>() {
    if ($('view_more_<?php echo $randonNumber; ?>'))
    $('view_more_<?php echo $randonNumber; ?>').style.display = "<?php echo ($this->paginator->count() == 0 ? 'none' : ($this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' )) ?>";
  }
  function viewMore_<?php echo $randonNumber; ?> (){
    sesJqueryObject('#view_more_<?php echo $randonNumber; ?>').hide();
    sesJqueryObject('#loading_image_<?php echo $randonNumber; ?>').show();
    sesJqueryObject('.activity_transaction_noresult').remove();
    if(typeof requestViewMore_<?php echo $randonNumber; ?> != 'undefined')
    requestViewMore_<?php echo $randonNumber; ?>.cancel();
    requestViewMore_<?php echo $randonNumber; ?> = new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + "widget/index/mod/sescredit/id/<?php echo $this->widgetId; ?>/name/<?php echo $this->widgetName; ?>",
      'data': {
        format: 'html',
        page: page<?php echo $randonNumber; ?>,
        params : params<?php echo $randonNumber; ?>,
        is_ajax : 1,
        is_search:is_search_<?php echo $randonNumber;?>,
        view_more:1,
        searchParams:searchParams<?php echo $randonNumber; ?> ,
        widget_id: '<?php echo $this->widgetId;?>',
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        sesJqueryObject('#sescredit_transaction_loading').hide();
        if($('loading_images_browse_<?php echo $randonNumber; ?>'))
        sesJqueryObject('#loading_images_browse_<?php echo $randonNumber; ?>').remove();
        if($('loadingimgsescredit-wrapper'))
        sesJqueryObject('#loadingimgsescredit-wrapper').hide();
        document.getElementById('loading_image_<?php echo $randonNumber; ?>').style.display = 'none';
        sesJqueryObject('body').append('<div id="sescredit_hideshow_table" style="display:none;">'+responseHTML+'<div>');
        if(sesJqueryObject('#sescredit_hideshow_table').find('.activity_transaction_noresult').length) {
          sesJqueryObject('#sescredit_table_contaner_<?php echo $randonNumber; ?>').parent().append(responseHTML);
        }else {
          sesJqueryObject('#sescredit_table_contaner_<?php echo $randonNumber; ?>').show();
          document.getElementById('activity-transaction_<?php echo $randonNumber; ?>').innerHTML = document.getElementById('activity-transaction_<?php echo $randonNumber; ?>').innerHTML + responseHTML;
        }
        sesJqueryObject('#sescredit_hideshow_table').remove();
      }
    });
    requestViewMore_<?php echo $randonNumber; ?>.send();
    return false;
  }
  //End Pagination Work
</script>
