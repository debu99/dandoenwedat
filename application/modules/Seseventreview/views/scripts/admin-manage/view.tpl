<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
<div class="sesbasic_view_stats_popup">
  <h3>Statistics of <?php echo $this->item->title;  ?> </h3>
  <table>
    <tr>
      <?php $event = Engine_Api::_()->getItem('sesevent_event',$this->item->content_id); ?>
      <td><?php echo $this->translate('Event Title') ?>:</td>
      <td><?php if($event) { ?>
       <a href="<?php echo $event->getHref() ?>" target="_blank"> <?php echo  $event->getTitle(); ?> </a>
      <?php  } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Title') ?>:</td>
      <td><?php if(!is_null($this->item->title) && $this->item->title != '') {
        echo  $this->item->title ;
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Pros') ?>:</td>
      <td><?php if(!is_null($this->item->pros) && $this->item->pros != '') {
        echo  $this->item->pros ;
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Cons') ?>:</td>
      <td><?php if(!is_null($this->item->cons) && $this->item->cons != '') {
        echo  $this->item->cons ;
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Owner') ?>:</td>
      <td><?php echo  $this->item->getOwner(); ?></td>
    </tr>
   
    
    <tr>
      <td>Rating:</td>
      <td class="seseventreview_view_details_rating">
        <div class="sesbasic_rating_star">
          <?php $ratingCount = $this->item->rating;?>
          <?php for($i=0; $i<5; $i++){?>
            <?php if($i < $ratingCount):?>
              <span id="" class="fa fa-star"></span>
            <?php else:?>
              <span id="" class="fa fa fa-star-o star-disable"></span>
            <?php endif;?>
          <?php }?>
        </div>
        <?php $reviewParameters = Engine_Api::_()->getDbtable('parametervalues', 'seseventreview')->getParameters(array('content_id'=>$this->item->getIdentity(),'user_id'=>$this->item->owner_id)); ?>
        <?php if(count($reviewParameters)>0){ ?>
            <?php foreach($reviewParameters as $reviewP){ ?>
            <div class="sesbasic_clearfix">
              <div class="sesevent_rating_parameter_label"><?php echo $reviewP['title']; ?></div>
              <div class="sesbasic_rating_parameter sesbasic_rating_parameter_small">
              <?php $ratingCount = $reviewP['rating'];?>
              <?php for($i=0; $i<5; $i++){?>
                <?php if($i < $ratingCount):?>
                  <span id="" class="sesbasic-rating-parameter-unit"></span>
                <?php else:?>
                  <span id="" class="sesbasic-rating-parameter-unit sesbasic-rating-parameter-unit-disable"></span>
                <?php endif;?>
              <?php }?>
              </div>
            </div>
          <?php } ?>
        <?php } ?>
    	</td>
    </tr>
    
    <?php $customFieldsData = Engine_Api::_()->seseventreview()->getCustomFieldMapData($this->item); 
    if(count($customFieldsData) > 0){ 
       foreach($customFieldsData as $valueMeta){
       if($valueMeta['value'] == '')	
        continue;
        echo '<tr><td>'. $valueMeta['label']. ':</td>
              <td>' .$valueMeta['value'].'</td></tr>';
       }    
     } ?>
    <tr>
      <td><?php echo $this->translate('Recommended') ?>:</td>
      <td><?php  if($this->item->recommended == 1){ ?>
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/check.png'; ?>"/> <?php }else{ ?> 
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/error.png'; ?>" /> <?php } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Comments') ?>:</td>
      <td><?php echo $this->item->comment_count ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Likes') ?>:</td>
      <td><?php echo $this->item->like_count ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Views') ?>:</td>
      <td><?php echo $this->locale()->toNumber($this->item->view_count) ?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Date') ?>:</td>
      <td><?php echo $this->item->creation_date; ;?></td>
    </tr>
  </table>
  <br />
  <button onclick='javascript:parent.Smoothbox.close()'>
    <?php echo $this->translate("Close") ?>
  </button>
</div>