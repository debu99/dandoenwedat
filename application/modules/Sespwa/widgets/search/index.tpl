<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespwa
 * @package    Sespwa
 * @copyright  Copyright 2018-2019 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2018-11-24 00:00:00 SocialEngineSolutions $
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
<!--  <form id="global_search_form" method="get">-->
<input placeholder="<?php echo $this->translate("Search"); ?>" id="text_search" type="text" name="query" />
<button onclick="javascript:showAllSearchResults();"><i class="fa fa-search"></i></button>
<!--  </form>-->
<script>
  function showAllSearchResults() {
    var text_search = $('text_search').value;
    if(text_search) { 
      window.location.href= '<?php echo $this->url(array("controller" => "search"), "default", true); ?>' + "?query=" + $('text_search').value;
    } else {
      return false;
    }
  }
  //Take refrences from "/application/modules/Blog/views/scripts/index/create.tpl"
  en4.core.runonce.add(function() {
    var searchAutocomplete = new Autocompleter.Request.JSON('text_search', "<?php echo $this->url(array('module' => 'sespwa', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>", {
      'postVar': 'text',
      'delay' : 250,      
      'minLength': 1,
      'selectMode': 'pick',
      'autocompleteType': 'tag',
      'customChoices': true,
      'filterSubset': true,
      'multiple': false,
      'className': 'sesbasic-autosuggest',
			'indicatorClass':'input_loading',
      'injectChoice': function(token) {
        if(token.url != 'all') {
	  var choice = new Element('li', {
	    'class': 'autocompleter-choices',
	    'html': token.photo,
	    'id': token.label
	  });
	  new Element('div', {
	    'html': this.markQueryValue(token.label),
	    'class': 'autocompleter-choice'
	  }).inject(choice);
	  new Element('div', {
	    'html': this.markQueryValue(token.resource_type),
	    'class': 'autocompleter-choice bold'
	  }).inject(choice);
	  choice.inputValue = token;
	  this.addChoiceEvents(choice).inject(this.choices);
	  choice.store('autocompleteChoice', token);
        }
        else {
         var choice = new Element('li', {
	    'class': 'autocompleter-choices',
	    'html': '',
	    'id': 'all'
	  });
// 	  new Element('div', {
// 	    'html': 'Show All Results',
// 	    'class': 'autocompleter-choice',
// 	    onclick: 'javascript:showAllSearchResults();'
// 	  }).inject(choice);
	  choice.inputValue = token;
	  this.addChoiceEvents(choice).inject(this.choices);
	  choice.store('autocompleteChoice', token);
        }
      }
    });
    searchAutocomplete.addEvent('onSelection', function(element, selected, value, input) {
      var url = selected.retrieve('autocompleteChoice').url;
      window.location.href = url;
    });
  });
  
  
  sesJqueryObject(document).ready(function() {
    sesJqueryObject('#text_search').keydown(function(e) {
      if (e.which === 13) {
        showAllSearchResults();
      }
    });
  });
</script>
