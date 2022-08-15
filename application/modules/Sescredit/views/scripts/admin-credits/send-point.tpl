<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sescredit
 * @package    Sescredit
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: send-point.tpl  2019-01-18 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>

<?php include APPLICATION_PATH .  '/application/modules/Sescredit/views/scripts/dismiss_message.tpl';?>
<?php $base_url = $this->layout()->staticBaseUrl;?>
<?php $this->headScript()
  ->appendFile($base_url . 'externals/autocompleter/Observer.js')
  ->appendFile($base_url . 'externals/autocompleter/Autocompleter.js')
  ->appendFile($base_url . 'externals/autocompleter/Autocompleter.Local.js')
  ->appendFile($base_url . 'externals/autocompleter/Autocompleter.Request.js')
  ->appendFile($base_url . 'application/modules/Sesbasic/externals/scripts/sesJquery.js');
?>
<?php $this->headLink()->appendStylesheet($base_url . 'application/modules/Sescredit/externals/styles/styles.css'); ?>    
<div>
  <?php echo $this->htmlLink(array('action' => 'send-points', 'reset' => false), $this->translate("Back to Manage Offers"),array('class' => 'buttonlink sesbasic_icon_back')) ?>
</div>
<br />
<div class='clear sesbasic_admin_form'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type='text/javascript'>
  sesJqueryObject(document).on('change','input[type=radio][name=member_type]',function(){
    if (this.value == 0) {
      sesJqueryObject('#sescredit_specific_member-wrapper').hide();
      sesJqueryObject('#member_level-wrapper').hide();
    }else if (this.value == 1){
      sesJqueryObject('#sescredit_specific_member-wrapper').show();
      sesJqueryObject('#member_level-wrapper').hide();
    }
    else if (this.value == 2){
      sesJqueryObject('#sescredit_specific_member-wrapper').hide();
      sesJqueryObject('#member_level-wrapper').show();
    }
  });
  sesJqueryObject(document).on('change','input[type=radio][name=send_email]',function(){
    if (this.value == 0) {
      sesJqueryObject('#email_message-wrapper').hide();
    }else{
      sesJqueryObject('#email_message-wrapper').show();
    }
    
  });
  window.addEvent('domready', function() {
    var valueStyle = sesJqueryObject('input[name=member_type]:checked').val();
    if (valueStyle == 0) {
      sesJqueryObject('#sescredit_specific_member-wrapper').hide();
      sesJqueryObject('#member_level-wrapper').hide();
    }else if (valueStyle == 1){
      sesJqueryObject('#sescredit_specific_member-wrapper').show();
      sesJqueryObject('#member_level-wrapper').hide();
    }
    else if (valueStyle == 2){
      sesJqueryObject('#sescredit_specific_member-wrapper').hide();
      sesJqueryObject('#member_level-wrapper').show();
    }
    var valueStyle = sesJqueryObject('input[name=send_email]:checked').val();
    if(valueStyle == 0) {
      sesJqueryObject('#email_message-wrapper').hide();
    }
    else {
      sesJqueryObject('#email_message-wrapper').show();
    }
  });
  var Searchurl = "<?php echo $this->url(array('module' =>'sescredit','controller' => 'credits', 'action' => 'get-all-members'),'admin_default',true); ?>";
  en4.core.runonce.add(function() {
    formObj = sesJqueryObject('#filter_form').find('div').find('div').find('div');
    var contentAutocomplete = new Autocompleter.Request.JSON('sescredit_specific_member', Searchurl, {
      'postVar': 'text',
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'customChoices': true,
      'filterSubset': true,
      'multiple': false,
      'className': 'sesbasic-autosuggest',
      'injectChoice': function(token) {
	var choice = new Element('li', {
	  'class': 'autocompleter-choices', 
	  'html': token.photo, 
	  'id':token.label
	});
	new Element('div', {
	  'html': this.markQueryValue(token.label),
	  'class': 'autocompleter-choice'
	}).inject(choice);
	this.addChoiceEvents(choice).inject(this.choices);
	choice.store('autocompleteChoice', token);
      }
    });
    contentAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
      var to =  selected.retrieve('autocompleteChoice');
      sesJqueryObject('#sescredit_user_id').val(to.id);
    });
  });
</script>

