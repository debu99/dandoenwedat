<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: create.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php $this->headTranslate(array('SesSun','SesMon','SesTue','SesWed','SesThu','SesFri','SesSat',"SesJan", "SesFeb", "SesMar", "SesApr", "SesMay", "SesJun", "SesJul", "SesAug", "SesSep", "SesOct", "SesNov", "SesDec"));?>
<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Attach.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.Range.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/picker-style.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');?>
<div>
  <?php echo $this->htmlLink(array('action' => 'index', 'reset' => false), $this->translate("Back to Manage Offers"),array('class' => 'buttonlink sesbasic_icon_back')) ?>
</div>
<br />
<div class='clear sesbasic_admin_form'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type='text/javascript'>
  var inputwidth =sesJqueryObject('#show_date_field').width();
  var pickerposition =(400 - inputwidth);
  en4.core.runonce.add(function () {
    var picker = new Picker.Date.Range($('show_date_field'), {
      timePicker: false,
      columns: 2,
      positionOffset: {x: -pickerposition, y: 0}
    });
    var picker2 = new Picker.Date.Range('range_hidden', {
      toggle: $$('#range_select'),
      columns: 2,
      onSelect: function () {
        $('range_text').set('text', Array.map(arguments, function (date) {
            return date.format('%e %B %Y');
        }).join(' - '))
      }
    });
  });
  sesJqueryObject(document).on('change','input[type=radio][name=offer_time]',function(){
    if (this.value == 1) {
      sesJqueryObject('#show_date_field-wrapper').show();
    }else{
      sesJqueryObject('#show_date_field-wrapper').hide();
    }
  });
  window.addEvent('domready', function() {
    var valueStyle = sesJqueryObject('input[name=offer_time]:checked').val();
    if(valueStyle == 1) {
      sesJqueryObject('#show_date_field-wrapper').show();
    }
    else {
      sesJqueryObject('#show_date_field-wrapper').hide();
    }
  });
</script>
<style>
  .datepicker .footer button.apply:before{content:"Select";}
  .datepicker .footer button.cancel:before{content:"Cancel";}
</style>

