<?php 
/**
 * SocialEngineSolutions
 *
 * @category   Application_Seseventreview
 * @package    Seseventreview
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: edit.tpl 2016-07-26 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>
<style>
.sesevent_review_form .global_form div.form-label label.requiredstart:after{content: " *";color: #f00;}
</style>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Seseventreview/externals/styles/styles.css'); ?>
<?php 
	$defaultProfileFieldId = "0_0_$this->defaultProfileId";
	$profile_type = 2;
  echo $this->partial('_customFields.tpl', 'seseventreview', array()); 
?>

<script>

  var defaultProfileFieldId = '<?php echo $defaultProfileFieldId ?>';
  var profile_type = '<?php echo $profile_type ?>';
  var previous_mapped_level = 0;
  
  
 function showFields(cat_value, cat_level,typed,isLoad) {
		var categoryId = getProfileType('<?php echo $this->category_id ?>');
		var subcatId = getProfileType('<?php echo $this->subcat_id ?>');
		var subsubcatId = getProfileType('<?php echo $this->subsubcat_id ?>');
		var type = categoryId+','+subcatId+','+subsubcatId;
    if (cat_level == 1 || (previous_mapped_level >= cat_level && previous_mapped_level != 1) || (profile_type == null || profile_type == '' || profile_type == 0)) {
      profile_type = getProfileType(cat_value);
      if (profile_type == 0) {
        profile_type = '';
      } else {
        previous_mapped_level = cat_level;
      }
      $(defaultProfileFieldId).value = profile_type;
      changeFields($(defaultProfileFieldId),null,isLoad,type);
    }
  }
  var getProfileType = function(category_id) {
    var mapping = <?php echo Zend_Json_Encoder::encode(Engine_Api::_()->getDbTable('categories', 'sesevent')->getMapping(array('category_id', 'profile_type_review'))); ?>;
		  for (i = 0; i < mapping.length; i++) {	
      	if (mapping[i].category_id == category_id)
        return mapping[i].profile_type_review;
    	}
    return 0;
  }
  en4.core.runonce.add(function() {
		sesJqueryObject('.seseventreview_form_rating_star').find('label').addClass('requiredstart');
    var defaultProfileId = '<?php echo '0_0_' . $this->defaultProfileId ?>' + '-wrapper';
     if ($type($(defaultProfileId)) && typeof $(defaultProfileId) != 'undefined') {
      $(defaultProfileId).setStyle('display', 'none');
    }
  });
	function showCustomOnLoad(value,isLoad){
	 <?php if(isset($this->category_id) && $this->category_id != 0){ ?>
		var categoryId = getProfileType(<?php echo $this->category_id; ?>)+',';
		<?php if(isset($this->subcat_id) && $this->subcat_id != 0){ ?>
		var subcatId = getProfileType(<?php echo $this->subcat_id; ?>)+',';
		<?php  }else{ ?>
		var subcatId = '';
		<?php } ?>
		<?php if(isset($this->subsubcat_id) && $this->subsubcat_id != 0){ ?>
		var subsubcat_id = getProfileType(<?php echo $this->subsubcat_id; ?>)+',';
		<?php  }else{ ?>
		var subsubcat_id = '';
		<?php } ?>
		var id = (categoryId+subcatId+subsubcat_id).replace(/,+$/g,"");;
			showFields(value,1,id,isLoad);
		if(value == 0)
			document.getElementsByName("0_0_1")[0].value=subcatId;	
			return false;
		<?php } ?>
	}
 window.addEvent('domready', function() {	
	 		showCustomOnLoad('','no');
  });
  
</script> 
<div class="sesevent_review_form"> 
	<?php echo $this->form->render($this);?>
</div>