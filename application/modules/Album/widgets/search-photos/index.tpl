<script type="text/javascript">
//<![CDATA[
  window.addEvent('domready', function() {
    $('sort').addEvent('change', function(){
      $(this).getParent('form').submit();
    });
  })
//]]>
</script>

<?php echo $this->searchForm->render($this) ?>
