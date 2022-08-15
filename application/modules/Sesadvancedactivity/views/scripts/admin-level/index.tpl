<?php

?>
<?php include APPLICATION_PATH .  '/application/modules/Sesadvancedactivity/views/scripts/dismiss_message.tpl';?>

<script type="text/javascript">
  var fetchLevelSettings =function(level_id){
    window.location.href= en4.core.baseUrl+'admin/sesadvancedactivity/level/index/id/'+level_id;
  }
</script>
<div class='sesbasic-form'>
  <div>
    <?php if(!empty($this->subnavigation)) {?>
    <?php if( count($this->subnavigation) ): ?>
      <div class='sesbasic-admin-sub-tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render();?>
      </div>
    <?php endif; ?>
   <?php } ?>
    <div class='sesbasic-form-cont'>
	    <div class='clear'>
			  <div class='settings sesbasic_admin_form'>
			    <?php echo $this->form->render($this); ?>
			  </div>
			</div>
		</div>
  </div>
</div>
