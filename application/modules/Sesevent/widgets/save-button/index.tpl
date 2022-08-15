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
<script>

function saveButton(id, type) {
	
	if ($(type + '_saveunsavehidden_' + id))
	var contentId = $(type + '_saveunsavehidden_' + id).value

	en4.core.request.send(new Request.JSON({
	url: en4.core.baseUrl + 'sesevent/index/save',
	data: {
	format: 'json',
		'id': id,
		'type': type,
		'contentId': contentId
	},
	onSuccess: function(responseJSON) {
		debugger
	if (responseJSON.save_id) {
		if ($(type + '_saveunsavehidden_' + id))
			$(type + '_saveunsavehidden_' + id).value = responseJSON.save_id;
		if ($(type + '_save_' + id))
			$(type + '_save_' + id).style.display = 'none';
		if ($(type + '_unsave_' + id))
			$(type + '_unsave_' + id).style.display = 'block';
		
	} else {
		if ($(type + '_saveunsavehidden_' + id))
			$(type + '_saveunsavehidden_' + id).value = 0;
		if ($(type + '_save_' + id))
			$(type + '_save_' + id).style.display = 'block';
		if ($(type + '_unsave_' + id))
			$(type + '_unsave_' + id).style.display = 'none';
	}
	}
	}));
}
</script>
<?php if (!empty($this->viewer_id)): ?>
	<div class="sesbasic_clearfix sesevent_button">
    <div class="" id="<?php echo $this->type ?>_save_<?php echo $this->id; ?>" style ='display:<?php echo $this->isSave ? "none" : "block" ?>' >
      <a  class="sesbasic_link_btn" href = "javascript:void(0);" onclick = "saveButton('<?php echo $this->id; ?>', '<?php echo $this->type ?>');">
				<i class="far fa-save"></i><span><?php echo $this->translate("Save this event") ?></span>
      </a>
    </div>
    <div id="<?php echo $this->type ?>_unsave_<?php echo $this->id; ?>" style ='display:<?php echo $this->isSave ? "block" : "none" ?>' >
      <a  class="sesbasic_link_btn" href = "javascript:void(0);" onclick = "saveButton('<?php echo $this->id; ?>', '<?php echo $this->type ?>');">
      	<i class="far fa-save"></i><span><?php echo $this->translate("Unsave this event") ?></span>
      </a>
    </div>
    <input type ="hidden" id = "<?php echo $this->type ?>_saveunsavehidden_<?php echo $this->id; ?>" value = '<?php echo $this->isSave ? $this->isSave : 0; ?>' />
  </div>
<?php endif; ?>