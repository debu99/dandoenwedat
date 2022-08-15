<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: view.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
<div class="sesbasic_view_stats_popup">
  <h3><?php echo $this->translate("View Details"); ?> </h3>
  <table>
    <tr>
      <?php if($this->item->photo_id): ?>
      <?php $img_path = Engine_Api::_()->storage()->get($this->item->photo_id, '')->getPhotoUrl();
      $path = $img_path; 
      ?>
      <?php else: ?>
      <?php $path = $this->baseUrl() . '/application/modules/Sesevent/externals/images/nophoto_event_thumb_icon.png'; ?>
      <?php endif; ?>
      <td colspan="2"><a href="<?php echo $this->item->getHref(); ?>" target="_blank"><img src="<?php echo $path; ?>" style="height:75px; width:75px;"/></a></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Title') ?>:</td>
      <td><?php if(!is_null($this->item->title) && $this->item->title != '') {?>
        <a href="<?php echo $this->item->getHref(); ?>" target="_blank"><?php echo  $this->item->title ; ?></a>
        <?php
        } else { 
        echo "-";
        } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Owner') ?>:</td>
      <td><?php echo  $this->item->getOwner(); ?></td>
    </tr>
    <?php 
  if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated'))
  $ticketExt = true;
  ?>
    <tr>
      <td><?php echo $this->translate('Ratings') ?>:</td>
      <td>
        <?php if($this->item->rating): ?>
        <div class="sesbasic_text_light">
          <?php if( $this->item->rating > 0 ): ?>
          <?php for( $x=1; $x<= $this->item->rating; $x++ ): ?>
          <span class="sesbasic_rating_star_small fa fa-star"></span>
          <?php endfor; ?>
          <?php if((round($this->item->rating) - $this->item->rating) > 0): ?>
          <span class="sesbasic_rating_star_small fa fa-star-half-o"></span>
          <?php endif; ?>
          <?php endif; ?>
        </div>
        <?php else: ?>
          <?php for( $x=1; $x<= 5; $x++ ): ?>
            <span class="sesbasic_rating_star_small fa fa-star-o star-disabled"></span>
          <?php endfor; ?>
        <?php endif; ?>
      </td>
    </tr>
    
    
    <?php
	     $guestInfo = true;
       if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('seseventticket') && Engine_Api::_()->getApi('settings', 'core')->getSetting('seseventticket.pluginactivated')){
        $ticket = $ticket = Engine_Api::_()->getDbtable('tickets', 'sesevent')->getTicket(array('event_id' => $this->item->getIdentity()));
        if(count($ticket)) {
          $guestInfo = false;
        }
     }
     
     
			$membershipTable = Engine_Api::_()->getDbtable('membership', 'sesevent');
			$membershipTableName = $membershipTable->info('name');
	
			$selectAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS attending');
			$attending = $selectAttenting->where('active =?',1)->where('rsvp =?',2)->where('resource_id =?',$this->item->getIdentity())->query()->fetchColumn();
			
			$selectNotAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS notattending');
			$notattending = $selectNotAttenting->where('active =?',1)->where('resource_id =?',$this->item->getIdentity())->where('rsvp =?',0)->query()->fetchColumn();
			
			$selectMaybeAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS maybeattending');
			$maybeattending = $selectMaybeAttenting->where('active =?',1)->where('resource_id =?',$this->item->getIdentity())->where('rsvp =?',1)->query()->fetchColumn();
			
			$selectNewAttenting = $membershipTable->select()->from($membershipTableName, 'count(*) AS newattending');
			$newattending = $selectNewAttenting->where('active =?',0)->where('resource_id =?',$this->item->getIdentity())->query()->fetchColumn();	
   ?>
    
   <?php if(!$guestInfo){ ?>
   	<tr>
      <td><?php echo $this->translate('Total Guest Attending') ?>:</td>
      <td><?php echo  $attending; ?></td>
    </tr>
   <?php }else{ ?>
   	<tr>
      <td><?php echo $this->translate('Guests Attending') ?>:</td>
      <td><?php echo  $attending; ?></td>
    </tr>
   		<tr>
      <td><?php echo $this->translate('Guests Maybe Attending') ?>:</td>
      <td><?php echo  $maybeattending; ?></td>
    </tr>
   <tr>
      <td><?php echo $this->translate('Guests Not Attending') ?>:</td>
      <td><?php echo  $notattending; ?></td>
    </tr>
   <tr>
      <td><?php echo $this->translate('New Guests Requests') ?>:</td>
      <td><?php echo  $newattending; ?></td>
    </tr>
   <?php } ?>
    
    
    <tr>
      <td><?php echo $this->translate('IP Address') ?>:</td>
      <td><?php echo  $this->item->ip_address ?></td>
    </tr>
    <?php if(isset($ticketExt)){ ?>
    <tr>
      <td><?php echo $this->translate('Total Tickets') ?>:</td>
      <td><?php echo  $this->item->totaltickets(); ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td><?php echo $this->translate('Approved') ?>:</td>
      <td><?php  if($this->item->is_approved == 1){ ?>
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/check.png'; ?>"/> <?php }else{ ?> 
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/error.png'; ?>" /> <?php } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Featured') ?>:</td>
      <td><?php  if($this->item->featured == 1 && $this->item->is_approved == 1){ ?>
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/check.png'; ?>"/> <?php }else{ ?> 
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/error.png'; ?>" /> <?php } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Sponsored') ?>:</td>
      <td><?php  if($this->item->sponsored == 1 && $this->item->is_approved == 1){ ?>
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/check.png'; ?>"/> <?php }else{ ?> 
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/error.png'; ?>" /> <?php } ?>
      </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Verified') ?>:</td>
      <td><?php  if($this->item->verified == 1 && $this->item->is_approved == 1){ ?>
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/check.png'; ?>"/> <?php }else{ ?> 
        <img src="<?php echo $baseURL . 'application/modules/Sesbasic/externals/images/icons/error.png'; ?>" /> <?php } ?>
      </td>
    </tr>
    
    <?php if(strtotime($this->item->enddate) < strtotime(date('Y-m-d')) && $this->item->offtheday == 1){ 
                    Engine_Api::_()->getDbtable('events', 'sesevent')->update(array(
                        'offtheday' => 0,
                        'starttime' =>'',
                        'endtime' =>'',
                      ), array(
                        "event_id = ?" => $this->item->event_id,
                      ));
                      $itemofftheday = 0;
               }else
                $itemofftheday = $this->item->offtheday; ?>
    <tr>
      <td><?php echo $this->translate('Of the Day') ?>:</td>
      <td><?php  if($itemofftheday == 1 && $this->item->is_approved == 1){ ?>
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