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
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<h3>Manage Credit Points</h3>
<p>This page lists all the modules for which you can give points to the members for various activities on your website. You can set the credit points based on the Member Levels on your website. In addition to setting up points based on individual activity, you can also enable / disbale the activities for earning points on your site.</p>
<p>You can also enter the text for each activity in various languages on your website which will be displayed to your users in respective widgets.</p><br />
<p>When users delete their activities on your website, then you can choose to deduct points for those deletion activities. Mention those points for each activity in the Deduction Points section below.<p>
<script type="text/javascript">
  var fetchLevelSettings =function(obj){
    sesJqueryObject(obj).closest('form').trigger('submit');
  }
</script>
<?php $localeObject = Zend_Registry::get('Locale');?>
<?php $languages = Zend_Locale::getTranslationList('language', $localeObject);?>
<?php $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');?>
<?php $languageList = Zend_Registry::get('Zend_Translate')->getList();?>
<div class='sescredit_search'><?php echo $this->form->render($this); ?></div>
<div class="sescredit_points_info sesbasic_bxs">
  <form name="" id="" method="post">
    <div class="sescredit_points_info_table">
      <div class="sescredit_points_info_table_header">
        <div class="_activitytype _label">Action Feed Item</div>
        <div class="_status _label">Status</div>
        <div class="_points">
          <div class="_label">Credit Points</div>
            <div>First Time</div>
            <div>Next Time</div>
            <div>Max Points/Day</div>
            <div>Deduction Points</div>
        </div>
        <?php foreach ($languageList as $key => $language):?>
          <div class="_lang _label"><?php echo $this->languageNameList[$language]; ?></div>
        <?php endforeach;?>
      </div>
      <div class="sescredit_points_info_table_content">
        <?php foreach($this->moduleBaseActionTypes as $key => $value):?>
          <?php foreach($value as $typeKey =>$type):?>
            <?php $populatedArray = Engine_Api::_()->getDbTable('values','sescredit')->getValues(array('type' => strtolower(str_replace('ADMIN_ACTIVITY_TYPE_','',$type)),'member_level' => isset($_GET['member_level']) ? $_GET['member_level'] : $this->level_id));?>
            <div class="sescredit_points_info_table_item">
              <input type="hidden" name="type[]" value="<?php echo $type;?>"/>
              <div class="_activitytype"><?php echo str_replace(array('(subject)','(object)'),'',$this->translate($type));?></div>
              <div class="_status" align="center">
                <?php if(empty($populatedArray['value_id'])):?>
                  <input type="hidden" name="status[<?php echo $type;?>]" value="0"/>
                <?php endif;?>
                <?php if($populatedArray['status']):?>
                   <a href="javascript:void(0);" onclick="changeStatus(<?php echo !empty($populatedArray['value_id']) ? $populatedArray['value_id'] : 0;?>,this);">
                    <?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png','',array('title'=> $this->translate('Disable'),'id' => "sescredit_status_icon_".$populatedArray['value_id']));?>
                  </a>
                <?php else: ?>
                   <a href="javascript:void(0);" onclick="changeStatus(<?php echo !empty($populatedArray['value_id']) ? $populatedArray['value_id'] : 0;?>,this);">
                    <?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png','', array('title'=> $this->translate('Enable'),'id' => "sescredit_status_icon_".$populatedArray['value_id']));?>
                  </a>
               <?php endif;?>  
              </div>
              <div class="_points"><input type="text" name="firstactivity[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $populatedArray['firstactivity'];?>"></div>
              <div class="_points"><input type="text" name="nextactivity[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $populatedArray['nextactivity'];?>"></div>
              <div class="_points"><input type="text" name="maxperday[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $populatedArray['maxperday'];?>"></div>
              <div class="_points"><input type="text" name="deduction[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $populatedArray['deduction'];?>"></div>
              <?php foreach ($languageList as $key => $language):?>
                <div class="_lang"><textarea placeholder="Enter text here.." name="<?php echo $language;?>[]"><?php echo $populatedArray[$language];?></textarea></div>
              <?php endforeach;?>
            </div>
          <?php endforeach;?>
        <?php endforeach;?>
      </div>
    </div>
    <div class="_btn">
      <button type="submit" name="submit">Save Changes</button>
    </div>
  </form>
</div>

<script type="text/javascript">
  function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
  }
  function changeStatus(value_id,element) {
    sesJqueryObject(element).find('img').attr('src','application/modules/Core/externals/images/loading.gif');
    console.log(value_id);
    if(value_id == 0) {
      var value = sesJqueryObject(element).parent().find('input').val();
      if(value == 1)
        value = 0;
      else
        value = 1;
      sesJqueryObject(element).parent().find('input').val(value);
      console.log(value,sesJqueryObject(element).parent().find('input'));
      updateImage(value,element);
      return;
    }
    var sendAjaxRequest  = new Request.HTML({
      method: 'post',
      'url': en4.core.baseUrl + 'sescredit/admin-credits/enable',
      'data': {
        format: 'html',
        id: value_id,
      },
      onSuccess: function (responseTree, responseElements, responseHTML, responseJavaScript) {
        var response = JSON.parse(responseHTML);
        updateImage(response.value,element);
      }
    });
    sendAjaxRequest.send();
  }
  function updateImage(value,element) {
    if(value == 1){
      sesJqueryObject(element).find('img').attr('src','application/modules/Sesbasic/externals/images/icons/check.png');
    }else{
      sesJqueryObject(element).find('img').attr('src','application/modules/Sesbasic/externals/images/icons/error.png');
    }
  }
</script>

