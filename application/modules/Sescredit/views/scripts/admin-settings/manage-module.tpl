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
<h3>Manage Module For Credit Points Redemption</h3>
<p>Here, you can manage the modules in which you want to enable your users to redeem their credit points earned from this plugin.<p>

<div class="sescredit_points_info sesbasic_bxs">
    <form name="" id="" method="post">
        <div class="sescredit_points_info_table">
            <div class="sescredit_points_info_table_header">
                <div class="_activitytype _label" style="width: 30%">Module Name</div>
                <div class="_status _label" style="width: 10%">Status</div>
                <div class="_points" style="width: 60%">
                    <div class="_label">Usage Settings</div>
                    <div style="width: 30%;">Min. Credit Points to be Redeemed</div>
                    <div  style="width: 30%;">Min. Cart Total</div>
                    <div style="width: 30%;">Max. %age of Total Cart Amount</div>
                </div>
            </div>
            <div class="sescredit_points_info_table_content">
                <?php foreach($this->paginator as $key => $value):?>
                    <?php $moduleEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($value['module']); ?>
                    <div class="sescredit_points_info_table_item">
                        <input type="hidden" name="module[]" value="<?php echo $value['managemodule_id'];?>"/>
                        <div style="width: 30%; float: left;">
                            <?php $module = Engine_Api::_()->getDbTable('modules','core')->getModule($value['module']); ?>
                            <?php echo $module ? $module->title : $value['title']; ?>
                        </div>
                        <div style="width: 10%;float: left;">
                            <?php if($value['enabled']):?>
                            <a href="javascript:void(0);" onclick="changeStatus(<?php echo !empty($value['managemodule_id']) ? $value['managemodule_id'] : 0;?>,this);">
                                <?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/check.png','',array('title'=> $this->translate('Disable'),'id' => "sescredit_status_icon_".$value['managemodule_id']));?>
                            </a>
                            <?php else: ?>
                            <a href="javascript:void(0);" onclick="changeStatus(<?php echo !empty($value['managemodule_id']) ? $value['managemodule_id'] : 0;?>,this);">
                                <?php echo $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/images/icons/error.png','', array('title'=> $this->translate('Enable'),'id' => "sescredit_status_icon_".$value['managemodule_id']));?>
                            </a>
                            <?php endif;?>
                        </div>
                        <div style="width: 60%; display: flex;float: left;">
                        <div class="_points" style="width: 33%"><input type="text" name="min_credit[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $value['min_credit'] ?>"></div>
                        <div class="_points" style="width: 33%"><input type="text" name="min_checkout_price[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $value['min_checkout_price'] ?>"></div>
                        <div class="_points limit" style="width: 33%"><input type="text" name="limit_use[]" min="0" onkeypress="return isNumberKey(event)" value="<?php echo $value['limit_use'] ?>" onkeyup="return keyUP(event)">%</div>
                        </div>
                        <div class="module_<?php echo $moduleEnable ? 1 : 0; ?>"></div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
        <div class="_btn">
            <button type="submit" name="submit">Save Changes</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    var savePreviousVal;
    function isNumberKey(evt){
        if(sesJqueryObject(evt.target).parent().hasClass('limit')){
            savePreviousVal = evt.target.value
        }
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        return !(charCode > 31 && (charCode < 48 || charCode > 57));

    }
    function keyUP(evt) {
        if(parseInt(evt.target.value) > 100){
            sesJqueryObject(evt.target).val(savePreviousVal)
        }
    }
    function changeStatus(value_id,element) {
        sesJqueryObject(element).find('img').attr('src','application/modules/Core/externals/images/loading.gif');
        if(value_id == 0) {
            var value = sesJqueryObject(element).parent().find('input').val();
            if(value == 1)
                value = 0;
            else
                value = 1;
            sesJqueryObject(element).parent().find('input').val(value);
            updateImage(value,element);
            return;
        }
        var sendAjaxRequest  = new Request.HTML({
            method: 'post',
            'url': en4.core.baseUrl + 'sescredit/admin-settings/module-enable',
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

