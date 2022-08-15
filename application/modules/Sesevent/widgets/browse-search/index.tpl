<?php
/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesevent
 * @package    Sesevent
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
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
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/jquery.timepicker.css'); ?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/styles/bootstrap-datepicker.css'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesbasic/externals/scripts/jquery1.11.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/jquery.timepicker.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Sesevent/externals/scripts/bootstrap-datepicker.js'); ?>
<style>
#start_date, #end_date{display:inline-block !important;}
</style>
<div class="sesbasic_browse_search <?php echo $this->view_type=='horizontal' ? 'sesbasic_browse_search_horizontal' : ''; ?>">
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
    en4.core.runonce.add(function() {
        sesJqueryObject('#loadingimgsesevent-wrapper').hide();
    });
	function showHideOptions<?php echo $this->identity; ?>(display){
		var elem = sesJqueryObject('.sesevent_widget_advsearch_hide_<?php echo $this->identity; ?>');
		if(elem.length == 0){
			sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').hide();	
			return;
		}
		for(var i = 0 ; i < elem.length ; i++){
			if(sesJqueryObject(elem[i]).attr('id') == 'subcat_id' && sesJqueryObject('#subcat_id option').length	< 2 && display == 'inline-block'){
				continue;
			}else if(sesJqueryObject(elem[i]).attr('id') == 'subsubcat_id' && sesJqueryObject('#subsubcat_id option').length	< 2 && display == 'inline-block'){
				continue;	
			}
				sesJqueryObject(elem[i]).parent().parent().css('display',display);
		}
	}
	function checkSetting<?php echo $this->identity; ?>(first){
		var hideShowOption = sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').hasClass('active');
		if(hideShowOption){
				showHideOptions<?php echo $this->identity; ?>('none');
				if(typeof first == 'undefined'){
					sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').html("<i class='fa fa-plus-circle'></i><?php echo $this->translate('Show Advanced Settings') ?>");
				}
				sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').removeClass('active');
		}else{
				showHideOptions<?php echo $this->identity; ?>('inline-block');
				sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').html("<i class='fa fa-minus-circle'></i><?php echo $this->translate('Hide Advanced Settings') ?>");
				sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').addClass('active');
		}	
	}
	sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').click(function(e){
		checkSetting<?php echo $this->identity; ?>();
	});
    en4.core.runonce.add(function() {
		sesJqueryObject('#advanced_options_search_<?php echo $this->identity; ?>').html("<i class='fa fa-plus-circle'></i><?php echo $this->translate('Show Advanced Settings') ?>");
		checkSetting<?php echo $this->identity; ?>('true');	
	})
</script>
<?php $request = Zend_Controller_Front::getInstance()->getRequest();?>
<?php $controllerName = $request->getControllerName();?>
<?php $actionName = $request->getActionName();
?>

<?php if($controllerName == 'index' && ($actionName == 'browse' || $actionName == 'upcoming'  || $actionName == 'past' || $actionName == 'all-results' )){ ?>
<?php if($actionName == 'all-results'):?>
<?php $pageName = 'advancedsearch_index_sesevent_event';?>
<?php elseif($actionName == 'past'):?>
    <?php $pageName = 'sesevent_index_past';?>
  <?php elseif($actionName == 'upcoming'):?>
    <?php $pageName = 'sesevent_index_upcoming';?>
  <?php else:?>
    <?php $pageName = 'sesevent_index_browse';?>
  <?php endif;?>
  <?php $identity = Engine_Api::_()->sesevent()->getIdentityWidget('sesevent.browse-events','widget',$pageName); ?>
  <?php if($identity):?>
    <script type="application/javascript">
        en4.core.runonce.add(function() {
	sesJqueryObject('#filter_form').submit(function(e){		
	if(sesJqueryObject('.sesevent_event_all_events').length > 0){
		e.preventDefault();
	  sesJqueryObject('#loadingimgsesevent-wrapper').show();
		loadMap_<?php echo $identity;?> = true;
	  if(typeof paggingNumber<?php echo $identity; ?> == 'function'){
			sesJqueryObject('#sesbasic_loading_cont_overlay_<?php echo $identity?>').css('display', 'block');
	    isSearch = true;
	    e.preventDefault();
	    searchParams<?php echo $identity; ?> = sesJqueryObject(this).serialize();
			sesJqueryObject('#loadingimgsesevent-wrapper').show();
	    paggingNumber<?php echo $identity; ?>(1);
	  }else if(typeof viewMore_<?php echo $identity; ?> == 'function'){
			sesJqueryObject('#browse-widget_<?php echo $identity; ?>').html('');
	 	  sesJqueryObject('#loading_image_<?php echo $identity; ?>').show();
	    isSearch = true;
	    e.preventDefault();
	    searchParams<?php echo $identity; ?> = sesJqueryObject(this).serialize();
	    page<?php echo $identity; ?> = 1;
			sesJqueryObject('#loadingimgsesevent-wrapper').show();
	    viewMore_<?php echo $identity; ?>();
	  }
	}
	return true;
	});	
      });
    </script>
  <?php endif;?>
<?php }else if($controllerName == 'index' && $actionName == 'locations'){?>
  <script type="application/javascript">
sesJqueryObject(document).ready(function(){
		sesJqueryObject('#filter_form').submit(function(e){
			e.preventDefault();
			var error = false;
			/*if(sesJqueryObject('#locationSesList').val() == ''){
				sesJqueryObject('#locationSesList').css('border-color','red');
				error = true;
			}else{
				sesJqueryObject('#locationSesList').css('border-color','');
			}
			if(sesJqueryObject('#miles').val() == 0){
				error = true;
				sesJqueryObject('#miles').css('border-color','red');
			}else{
				sesJqueryObject('#miles').css('border-color','');
			}
			if(map_location_widget_sesevent && !error){*/
				sesJqueryObject('#loadingimgsesevent-wrapper').show();
					e.preventDefault();
					searchParams = sesJqueryObject(this).serialize();
					sesJqueryObject('#loadingimgsesevent-wrapper').show();
				  callNewMarkersAjax();
			/*}*/
		return true;
		});	
});
</script>
<?php } ?>
<script type="text/javascript">
  var Searchurl = "<?php echo $this->url(array('module' =>'sesevent','controller' => 'index', 'action' => 'get-event'),'default',true); ?>";
  en4.core.runonce.add(function()
  {
    var contentAutocomplete = new Autocompleter.Request.JSON('search_text', Searchurl, {
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
      window.location.href = selected.retrieve('autocompleteChoice').url;
    });
  });
    
  function showSubCategory(cat_id,selected) {
    var url = en4.core.baseUrl + 'sesevent/index/subcategory/category_id/' + cat_id;
    new Request.HTML({
      url: url,
      data: {
      'selected':selected
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
	if ($('subcat_id') && responseHTML) {
	  if ($('subcat_id-wrapper')) {
	  $('subcat_id-wrapper').style.display = "inline-block";
	  }
	  $('subcat_id').innerHTML = responseHTML;
	} else {
	  if ($('subcat_id-wrapper')) {
	    $('subcat_id-wrapper').style.display = "none";
	    $('subcat_id').innerHTML = '';
	  }
	  if ($('subsubcat_id-wrapper')) {
	    $('subsubcat_id-wrapper').style.display = "none";
	    $('subsubcat_id').innerHTML = '';
	  }
	}
      }
    }).send(); 
  }
  
  function showSubSubCategory(cat_id,selected) {
    if(cat_id == 0){
      if ($('subsubcat_id-wrapper')) {
	$('subsubcat_id-wrapper').style.display = "none";
	$('subsubcat_id').innerHTML = '';
      }	
      return false;
    }

    var url = en4.core.baseUrl + 'sesevent/index/subsubcategory/subcategory_id/' + cat_id;
    (new Request.HTML({
      url: url,
      data: {
      'selected':selected
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
	if ($('subsubcat_id') && responseHTML) {
	  if ($('subsubcat_id-wrapper')) {
	    $('subsubcat_id-wrapper').style.display = "inline-block";
	  }
	  $('subsubcat_id').innerHTML = responseHTML;

	} else {
	  if ($('subsubcat_id-wrapper')) {
	    $('subsubcat_id-wrapper').style.display = "none";
	    $('subsubcat_id').innerHTML = '';
	  }
	}
      }
    })).send();  
  }

  en4.core.runonce.add(function() {
    if($('category_id')){
      var catAssign = 1;
      <?php if(isset($_GET['category_id']) && $_GET['category_id'] != 0){ ?>
	<?php if(isset($_GET['subcat_id'])){$catId = $_GET['subcat_id'];}else $catId = ''; ?>
	showSubCategory('<?php echo $_GET['category_id']; ?>','<?php echo $catId; ?>');
	<?php if(isset($_GET['subsubcat_id'])){ ?>
	  <?php if(isset($_GET['subsubcat_id'])){$subsubcat_id = $_GET['subsubcat_id'];}else $subsubcat_id = ''; ?>
	showSubSubCategory("<?php echo $_GET['subcat_id']; ?>","<?php echo $_GET['subsubcat_id']; ?>");
	<?php }else{?>
	  $('subsubcat_id-wrapper').style.display = "none";
	<?php } ?>
      <?php  }else{?>
	$('subcat_id-wrapper').style.display = "none";
	$('subsubcat_id-wrapper').style.display = "none";
      <?php } ?>
    }
  });

  en4.core.runonce.add(function() {
    mapLoad = false;
    if(sesJqueryObject('#lat-wrapper').length > 0){
      sesJqueryObject('#lat-wrapper').css('display' , 'none');
      sesJqueryObject('#lng-wrapper').css('display' , 'none');
      initializeSesEventMapList();
    }
      sesJqueryObject('#loadingimgsesevent-wrapper').hide();
  });
  en4.core.runonce.add(function() {
        var selectedDate =  new Date(sesJqueryObject('#start_date').val());
        var FromEndDate;
        if(sesJqueryObject('#start_date').length){
            sesBasicAutoScroll('#start_date').datepicker({
                        format: 'm/d/yyyy',
                        weekStart: 1,
                        autoclose: true,
                        endDate: FromEndDate,
                }).on('changeDate', function(ev){
                    selectedDate = ev.date;
                    if(sesJqueryObject('#end_date').length){
                        FromEndDate = new Date(sesBasicAutoScroll('#end_date').val());
                        sesBasicAutoScroll('#end_date').datepicker('setStartDate', selectedDate);
                    }
                });
        }
        if(sesJqueryObject('#end_date').length){
                sesBasicAutoScroll('#end_date').datepicker({
                        format: 'm/d/yyyy',
                        weekStart: 1,
                        autoclose: true,
                        startDate: selectedDate,
                }).on('changeDate', function(ev){
                    FromEndDate = new Date(ev.date.valueOf());
                    FromEndDate.setDate(FromEndDate.getDate(new Date(ev.date.valueOf())));
                    if(sesJqueryObject('#start_date').length)
                    sesBasicAutoScroll('#start_date').datepicker('setEndDate', FromEndDate);
            });
        }
  });
 </script>