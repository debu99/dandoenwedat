<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesgdpr
 * @package    Sesgdpr
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: get-gdpr-data.tpl 2018-05-19 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core')->getSetting('gdpr_content', array('cookie','dataProtection','privacySettings','requestArchive','unsubscribe','forgotMe')); ?>

<div class="sesgdpr_pp_popup sesbasic_bxs">
	<div class="_header">
  	<?php echo $this->translate("Privacy Preferences");?>
  </div>
  <div class="_maincontainer sesgdpr_clearfix">
  	<div class="_tabs">
    	<ul id="sesgdpa_consents">
      <li><a href="javascript:;" data-rel="cookie"><?php echo $this->translate("Cookie Settings");?></a></li>
        <?php if(in_array('dataProtection',$settings)){ ?>
      	<li class=""><a href="javascript:;" data-rel="dpo"><?php echo $this->translate("Contact DPO");?></a></li>
        <?php } ?>       
        <?php if(in_array('privacySettings',$settings)){ ?>
        <li><a href="javascript:;" data-rel="privacy"><?php echo $this->translate("Privacy Settings");?></a></li>
        <?php } ?>
        <?php if(in_array('requestArchive',$settings)){ ?>
        <li><a href="javascript:;" data-rel="request"><?php echo $this->translate("Request Archive");?></a></li>
        <?php } ?>
        <?php if(in_array('unsubscribe',$settings)){ ?>
        <li><a href="javascript:;" data-rel="unsubscribe"><?php echo $this->translate("Unsubscribe");?></a></li>
        <?php } ?>
        <?php if(in_array('forgotMe',$settings)){ ?>
        <li><a href="javascript:;" data-rel="forget"><?php echo $this->translate("Forget Me");?></a></li>
        <?php } ?>
      </ul>
    </div>
    
    <div class="_cont" id="sesgdpa_consent_div">
       <div class="_form _cookie" style="display:none;">
        <h3><?php echo $this->translate('Strictly Necessary Cookie Settings'); ?></h3>
        <p>
          <?php echo $this->translate('When you visit any website, it may store or retrieve information on your browser, mostly in the form of cookies. This information might be about you, your preferences or your device and is mostly used to make the site work as you expect it to. The information does not usually directly identify you, but it can give you a more personalized web experience.<br>There are some Cookies that are necessary for the site to function properly which you can not disallow.'); ?>
        </p>
        <?php $cookieuser = Engine_Api::_()->getApi('settings', 'core')->getSetting('sesconsent_bypass_cookie'); ?>
        <?php if($cookieuser){ ?>
        <div class="sesgdpr_cookies_used_box">
        	<div class="_head">
            <div class="_title"><?php echo $this->translate('Cookies Used'); ?></div>
            <div><button><?php echo $this->translate("Always Active"); ?></button></div>
      		</div>
          <div class="_content">
          	<code><?php echo str_replace(',',', ',$cookieuser); ?></code>	
          </div>
        </div>
        <?php } ?>
      </div>
      <?php if(in_array('dataProtection',$settings)){ ?>
  		<div class="_form _contactdpo" style="display:none;"><?php echo $this->contactdpo->setTitle($this->translate('Fill up the form below to directly contact our DPO (Data Protection Officer).'))->render();?></div>
      <?php } ?>
      <?php if(in_array('privacySettings',$settings)){ ?>
      <div class="_form _privacysetting" style="display:none;">
      	<table>
        	<thead>
          	<tr>
            	<th><?php echo $this->translate("Service");?></th>
              <th><?php echo $this->translate("Reason for use");?></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
           <?php foreach(Engine_Api::_()->getDbTable('services','sesgdpr')->getServices(array('enabled'=>1)) as $service){ ?>
          	<tr>
            	<td><?php echo $this->translate($service->name); ?></td>
              <td><?php echo $this->translate($service->description); ?></td>
              <td><a href="<?php echo $service->url; ?>" target="_blank" class="sesconsent_opt"><?php echo $this->translate("Optout");?></a></td>
            </tr>
          	<?php } ?>
          	
          <tbody>
        </table>
      </div>
      <?php } ?>
      
      <?php $this->contactdpo->removeElement('message'); ?>
      <?php if(in_array('requestArchive',$settings)){ ?>
      <div class="_form _request" style="display:none;"><?php echo $this->contactdpo->setTitle($this->translate('Submit this form to request all your data we currently possess. We will send you a report outlining all of the data via email.'))->render();?></div>
      <?php } ?>
      <?php if(in_array('unsubscribe',$settings)){ ?>
      <div class="_form _unsubscribe" style="display:none;"><?php echo $this->contactdpo->setTitle($this->translate('Please use the form below to be unsubscribed from all of our email marketing and advertising lists.'))->render();?></div>
      <?php } ?>
       <?php if(in_array('forgotMe',$settings)){ ?>
      <div class="_form _forget" style="display:none;"><?php echo $this->contactdpo->setTitle($this->translate('Submit a forget-me (erasure) request, to request the deletion of all the data we currently possess on you. Once request is made an we have deleted all the data, the action cannot be undone.
'))->render();?></div>
    	<?php } ?>
    </div>    
  </div>
</div>

<script type="application/javascript">
 en4.core.runonce.add(function()
  {
     callBackSesconsent('<?php echo $this->type; ?>');
  });
  
var htmlSuccessMessage = '<div class="sesgdpr_success_msg sesbasic_clearfix"><i class="fa fa-check-circle"></i><span><?php echo $this->translate("Your request has been successfully submitted."); ?></span></div>';
</script>