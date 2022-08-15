<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _verification_info.tpl  2019-03-04 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

//Js code written in core.js file of sesbasic plugin
?>
<?php 
$subject = $this->subject; 

$sesmemberenable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesmember');
$sesuserdocverificationenable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('sesuserdocverification');

if(!empty($sesuserdocverificationenable)) {
  $documents = Engine_Api::_()->getDbTable('documents', 'sesuserdocverification')->getAllUserDocuments(array('user_id' => $subject->getIdentity(), 'verified' => '1', 'fetchAll' => '1'));
}

if($sesmemberenable) { 
  $getUserInfoItem = Engine_Api::_()->sesmember()->getUserInfoItem($subject->getIdentity());
}
?>
<div class="sesbasic_member_verification_tip sesbasic_bxs" style="display:none;">
  <?php if($sesmemberenable && $getUserInfoItem->user_verified) { ?>
    <div class="sesbasic_member_verification_tip_section">
      <div class="_title"><?php echo $this->translate("Verified by Website Administrator");?></div>
    </div>  
  <?php } ?>
  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesuserdocverification.distip', 1)) { ?>
   <?php $typesShow = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('sesuserdocverification.dotypetip', '')); ?>
    <?php foreach($documents as $document) { ?>
      <?php $documentType = Engine_Api::_()->getItem('sesuserdocverification_documenttype', $document->documenttype_id); ?>
        <?php if($documentType && in_array($document->documenttype_id ,$typesShow)) { ?> 
          <div class="sesbasic_member_verification_tip_section">
            <div class="_title">
            	<span><?php echo $documentType->document_name; ?> :</span>
            	<span><?php echo $this->translate("Verified");?></span>
            </div>
            <!--<div class="_des">Lorem Ipsum is simply dummy text of the printing</div>	-->
          </div>
        <?php } ?>
    <?php } ?>
  <?php } ?>
</div>
