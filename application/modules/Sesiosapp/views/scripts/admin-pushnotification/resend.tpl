<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: resend.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php
  if (APPLICATION_ENV == 'production')
    $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.min.js');
  else
    $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Observer.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Local.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/autocompleter/Autocompleter.Request.js');
?>
<div class="settings sesiosapp_admin_form sesiosapp_notification">
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<style>
#global_content_simple .sesiosapp_notification form{
	width:600px;
}
#global_content_simple .sesiosapp_notification form textarea{
	min-width:inherit;
	width:90%;
}
.global_form #toValues-label > label{ display:none;}
#toValues-label {
	width: 177px;
	text-align: right;
	padding: 4px 15px 0px 2px;
	margin-bottom: 10px;
	overflow: hidden;
	float: left;
	clear: left;
	font-size: .9em;
	font-weight: bold;
	color: #777;
}
.tag {
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	border-radius: 3px;
	display: inline-block;
	background-color: #d3e6ef;
	font-weight: bold;
	display: inline-block;
	float: left;
	padding: .4em .6em .4em .6em;
	margin: 0px 10px 5px 0px;
	font-size: .8em;
}
</style>
<script type="text/javascript">
function testuser(){
  if(!scriptJquery('#title').val())  {
    scriptJquery('#title').css('border','1px solid red');
    return false;  
  }
  scriptJquery('#title').css('border','');
  scriptJquery('.global_form').css('position','relative').append('<span class="sesiosapp_loading_cont_overlay" style="display:block"></span>');
  
  var formData = new FormData(scriptJquery('.global_form')[0]);
			formData.append('test_user', 'true');
			submitFormAjax = scriptJquery.ajax({
					type:'POST',
					url: en4.core.baseUrl+'admin/sesbrowserpush/settings/notification/',
					data:formData,
					cache:false,
					contentType: false,
					processData: false,
					success:function(data){
            scriptJquery('.sesiosapp_loading_cont_overlay').remove();  
          },
					error: function(data){
						//silence
					}
			});
  
}
 scriptJquery(document).ready(function(e){
  criteriaOpen(scriptJquery('#criteria').val()); 
  <?php if($this->user_id){ ?> 
  scriptJquery('#toValues-wrapper').hide();
  scriptJquery('#criteria-wrapper').hide();
  <?php } ?>  
 })
 function criteriaOpen(value){
   if(value == 'all'){
    scriptJquery('#to-wrapper').hide();
    scriptJquery('#toValues-wrapper').hide();
    scriptJquery('#network-wrapper').hide();
    scriptJquery('#memberlevel-wrapper').hide();
   }else if(value == 'memberlevel'){
    scriptJquery('#to-wrapper').hide();
    scriptJquery('#toValues-wrapper').hide();
    scriptJquery('#network-wrapper').hide();
    scriptJquery('#memberlevel-wrapper').show();
   }else if(value == 'network'){
    scriptJquery('#to-wrapper').hide();
    scriptJquery('#toValues-wrapper').hide();
    scriptJquery('#network-wrapper').show();
    scriptJquery('#memberlevel-wrapper').hide();
   }else if(value == 'user'){
    scriptJquery('#to-wrapper').show();
    scriptJquery('#toValues-wrapper').show();
    scriptJquery('#network-wrapper').hide();
    scriptJquery('#memberlevel-wrapper').hide(); 
   }else{
    scriptJquery('#to-wrapper').hide();
    scriptJquery('#toValues-wrapper').hide();
    scriptJquery('#network-wrapper').hide();
    scriptJquery('#memberlevel-wrapper').hide();  
   }
 }
  // Populate data
  var maxRecipients = 10000000000;
  var to = {
    id : false,
    type : false,
    guid : false,
    title : false
  };
  var isPopulated = false;

  <?php if( !empty($this->isPopulated) && !empty($this->toObject) ): ?>
    isPopulated = true;
    to = {
      id : <?php echo sprintf("%d", $this->toObject->getIdentity()) ?>,
      type : '<?php echo $this->toObject->getType() ?>',
      guid : '<?php echo $this->toObject->getGuid() ?>',
      title : '<?php echo $this->string()->escapeJavascript($this->toObject->getTitle()) ?>'
    };
  <?php endif; ?>
  
  function removeFromToValue(id) {
    // code to change the values in the hidden field to have updated values
    // when recipients are removed.
    var toValues = $('toValues').value;
    var toValueArray = toValues.split(",");
    var toValueIndex = "";

    var checkMulti = id.search(/,/);

    // check if we are removing multiple recipients
    if (checkMulti!=-1){
      var recipientsArray = id.split(",");
      for (var i = 0; i < recipientsArray.length; i++){
        removeToValue(recipientsArray[i], toValueArray);
      }
    }
    else{
      removeToValue(id, toValueArray);
    }

    // hide the wrapper for usernames if it is empty
    if ($('toValues').value==""){
      $('toValues-wrapper').setStyle('height', '0');
    }

    $('to').disabled = false;
  }

  function removeToValue(id, toValueArray){
    for (var i = 0; i < toValueArray.length; i++){
      if (toValueArray[i]==id) toValueIndex =i;
    }

    toValueArray.splice(toValueIndex, 1);
    $('toValues').value = toValueArray.join();
  }

  en4.core.runonce.add(function() {
    if( !isPopulated ) { // NOT POPULATED
      new Autocompleter.Request.JSON('to', '<?php echo $this->url(array('module' => 'sesapi', 'controller' => 'index', 'action' => 'suggest','message' => true), 'default', true) ?>', {
        'minLength': 1,
        'delay' : 250,
        'selectMode': 'pick',
        'autocompleteType': 'message',
        'multiple': false,
        'className': 'sesiosapp-autosuggest',
        'filterSubset' : true,
        'tokenFormat' : 'object',
        'tokenValueKey' : 'label',
        'injectChoice': function(token){
          if(token.type == 'user'){
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
          else {
            var choice = new Element('li', {
              'class': 'autocompleter-choices friendlist',
              'id':token.label
            });
            new Element('div', {
              'html': this.markQueryValue(token.label),
              'class': 'autocompleter-choice'
            }).inject(choice);
            this.addChoiceEvents(choice).inject(this.choices);
            choice.store('autocompleteChoice', token);
          }
            
        },
        onPush : function(){
          if( $('toValues').value.split(',').length >= maxRecipients ){
            $('to').disabled = true;
          }
        }
      });
      
      new Composer.OverText($('to'), {
        'textOverride' : '<?php echo $this->translate('Start typing...') ?>',
        'element' : 'label',
        'isPlainText' : true,
        'positionOptions' : {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -4 : 4 ),
            y: 2
          }
        }
      });

    } else { // POPULATED

      var myElement = new Element("span", {
        'id' : 'tospan' + to.id,
        'class' : 'tag tag_' + to.type,
        'html' :  to.title /* + ' <a href="javascript:void(0);" ' +
                  'onclick="this.parentNode.destroy();removeFromToValue("' + toID + '");">x</a>"' */
      });
      $('to-element').appendChild(myElement);
      $('to-wrapper').setStyle('height', 'auto');

      // Hide to input?
      $('to').setStyle('display', 'none');
      $('toValues-wrapper').setStyle('display', 'none');
    }
  });
</script>
<?php
    $this->headScript()
      ->appendFile($this->layout()->staticBaseUrl . 'externals/mdetect/mdetect' . ( APPLICATION_ENV != 'development' ? '.min' : '' ) . '.js')
      ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Core/externals/scripts/composer.js');
?>

<?php if(!empty($this->usersMulti)){ ?>
  <script type="application/javascript">
    if(scriptJquery('#toValues-element').length){
      <?php 
        $html = '';
        foreach($this->usersMulti as $multi){
          $html .= '<span id="tospan_'.$multi->getTitle().'_'.$multi->getIdentity().'" class="tag">'.$multi->getTitle().' <a href="javascript:void(0);" onclick="this.parentNode.destroy();removeFromToValue(&quot;'.$multi->getIdentity().'&quot;,&quot;toValues&quot;);">x</a></span>';     
        }
      ?>  
      scriptJquery('#toValues-element').append('<?php echo $html; ?>');
    }
  </script>
<?php } ?>
