<?php

?>
<?php include APPLICATION_PATH .  '/application/modules/Sesevent/views/scripts/dismiss_message.tpl';?>

<script type="text/javascript">

  function importsesevent() {

    $('loading_image').style.display = '';
    $('seevent_import').style.display = 'none';
    en4.core.request.send(new Request.JSON({
      url: en4.core.baseUrl + 'admin/sesevent/import',
      method: 'get',
      data: {
        'is_ajax': 1,
        'format': 'json',
      },
      onSuccess: function(responseJSON) {
        if (responseJSON.error_code) {
          $('loading_image').style.display = 'none';
          $('sesevent_message').innerHTML = "<span style='color:red;'>Some error might have occurred during the import process. Please refresh the page and click on “Start Importing Event” again to complete the import process.</span>";
        } else {
          $('loading_image').style.display = 'none';
          $('sesevent_message').style.display = 'none';
          $('sesevent_message1').innerHTML = "<span style='color:green;'>" + '<?php echo $this->string()->escapeJavascript($this->translate("Events from SE Event have been successfully imported.")) ?>' + "</span>";
        }
      }
    }));
  }
</script>
<div class='settings'>
  <form class="global_form">
    <div>
      <h3><?php echo $this->translate('Import SE Events into this Plugin');?></h3>
      <p class="description">
        <?php echo $this->translate('Here, you can import events from SE Event plugin into this plugin.'); ?>
      </p>
      <div class="clear sesbasic_import_msg sesbasic_import_loading" id="loading_image" style="display: none;">
        <span><?php echo $this->translate("Importing ...") ?></span>
      </div>
      <div id="sesevent_message" class="clear sesbasic_import_msg sesbasic_import_error"></div>
      <div id="sesevent_message1" class="clear sesbasic_import_msg sesbasic_import_success"></div>
      <?php if(count($this->events) > 0): ?>
        <div id="seevent_import">
          <button class="sesbasic_import_button" type="button" name="sesevent_import" onclick='importsesevent();'>
            <?php echo $this->translate('Start Importing Event');?>
          </button>
        </div>
      <?php else: ?>
        <div class="tip">
          <span>
            <?php echo $this->translate('There are no event in SE Event plugin to be imported into this plugin.') ?>
          </span>
        </div>
      <?php endif; ?>
    </div>
  </form>
</div>