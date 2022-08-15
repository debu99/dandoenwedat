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
<?php $base_url = $this->layout()->staticBaseUrl;?>
<?php $this->headScript()
  ->appendFile($base_url . 'externals/autocompleter/Observer.js')
  ->appendFile($base_url . 'externals/autocompleter/Autocompleter.js')
  ->appendFile($base_url . 'externals/autocompleter/Autocompleter.Local.js')
  ->appendFile($base_url . 'externals/autocompleter/Autocompleter.Request.js');
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sescredit/externals/styles/styles.css'); ?>
<div class="sescredit_send_points_form sesbasic_bxs">
	<?php echo $this->form->render($this); ?>
</div>	
<div class="friend_send_point_success_message sescredit_success_message sesbasic_bxs" style="display:none;"><span><?php echo $this->translate("You have successfully sent point.");?></span></div>
<script type="text/javascript">
  var Searchurl = "<?php echo $this->url(array('module' =>'sescredit','controller' => 'ajax', 'action' => 'get-friend'),'default',true); ?>";
  en4.core.runonce.add(function() {
    formObj = sesJqueryObject('#filter_form').find('div').find('div').find('div');
    var contentAutocomplete = new Autocompleter.Request.JSON('friend_name_search', Searchurl, {
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
      sesJqueryObject('#friend_user_id').val(to.id);
    });
  });
  sesJqueryObject('#sescredit_send_point_friend').submit(function(e) {
      e.preventDefault();
      var friend = sesJqueryObject('#friend_user_id').val();
      var pointValue = sesJqueryObject('#send_credit_value').val();
      var message = sesJqueryObject('#friend_message').val();
      sesJqueryObject('.sescredit_error_message').remove();
      var isValid = true;
      if(!friend) {
        sesJqueryObject('#friend_name_search').parent().append('<span class="sescredit_error_message"><span>'+en4.core.language.translate("Please enter your friend name.")+'</span></span>');
        isValid = false;
      }
      if(!pointValue || parseInt(pointValue) <= 0) {
        sesJqueryObject('#send_credit_value').parent().append('<span class="sescredit_error_message"><span>'+en4.core.language.translate("Enter points greater than 1.")+'</span></span>');
        isValid = false;
      }
      if(isValid == false)
        return false;
      sesJqueryObject.post(en4.core.baseUrl + "widget/index/mod/sescredit/id/<?php echo $this->identity; ?>/name/send-point-friend",{send_credit_value:pointValue,friend_user_id:friend,message:message},function(response) {
        response = sesJqueryObject.parseJSON(response);
        if(response.status == 0) {
          sesJqueryObject('#sescredit_send_point_friend').find('div').find('div').find('.form-elements').prepend('<ul class="form-errors"><ul class="errors"><li>'+response.message+'Please complete this field - it is required.</li></ul></ul>');
        }
        else {
          sesJqueryObject('.friend_send_point_success_message').show();
          sesJqueryObject('#sescredit_send_point_friend').hide();
          setTimeout(function(){
            sesJqueryObject('.friend_send_point_success_message').hide();
            sesJqueryObject('#sescredit_send_point_friend').show();
            document.getElementById('sescredit_send_point_friend').reset();
          }, 4000)
        }
      });
  }); 
</script>