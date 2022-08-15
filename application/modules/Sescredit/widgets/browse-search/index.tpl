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
<?php
$base_url = $this->layout()->staticBaseUrl;
$this->headScript()
->appendFile($base_url . 'externals/autocompleter/Observer.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.Local.js')
->appendFile($base_url . 'externals/autocompleter/Autocompleter.Request.js');
?>
<?php $this->headTranslate(array('SesSun','SesMon','SesTue','SesWed','SesThu','SesFri','SesSat',"SesJan", "SesFeb", "SesMar", "SesApr", "SesMay", "SesJun", "SesJul", "SesAug", "SesSep", "SesOct", "SesNov", "SesDec"));?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Attach.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/scripts/Picker.Date.Range.js'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/picker-style.css'); ?>
    <?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/datepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<div class="sesbasic_clearfix sesbasic_bxs sescredit_browse_search sescredit_browse_search_horizontal">
  <?php echo $this->form->render($this) ?>
</div>
<?php $identity = Engine_Api::_()->sesbasic()->getIdentityWidget("sescredit.my-transactions",'widget',"sescredit_index_transaction"); ?>
<script type="application/javascript">
  function formSubmit<?php echo $identity; ?>(obj){
    if(sesJqueryObject('._mytransactionstable_content').length > 0){
      sesJqueryObject('#activity-transaction_<?php echo $identity; ?>').html('');
      sesJqueryObject('#sescredit_transaction_loading').show();
      sesJqueryObject('#sescredit_table_contaner_<?php echo $identity; ?>').hide();
      sesJqueryObject('#loading_image_<?php echo $identity; ?>').show();
      sesJqueryObject('#loadingimgsescontest-wrapper').show();
      is_search_<?php echo $identity; ?> = 1;
      if(typeof paggingNumber<?php echo $identity; ?> == 'function'){
        isSearch = true;
        searchParams<?php echo $identity; ?> = sesJqueryObject(obj).serialize();
        paggingNumber<?php echo $identity; ?>(1);
      }else if(typeof viewMore_<?php echo $identity; ?> == 'function'){
        isSearch = true;
        searchParams<?php echo $identity; ?> = sesJqueryObject(obj).serialize();
        page<?php echo $identity; ?> = 1;
        viewMore_<?php echo $identity; ?>();
      }
    }
  }
  en4.core.runonce.add(function () {
    sesJqueryObject(document).on('submit','#filter_form',function(e){
      e.preventDefault();
      formSubmit<?php echo $identity; ?>(this);
      return true;
    });	
  });
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
</script>
<style>
  .datepicker .footer button.apply:before{content:"Search";}
  .datepicker .footer button.cancel:before{content:"Cancel";}
</style>
