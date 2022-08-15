<?php ?>

<script type="application/javascript">
  <?php if($this->success) { ?>
    sesJqueryObject(document).ready(function() {
      window.close();
      //window.opener.location.href =  en4.core.baseUrl + 'members/home?tabtype=instagram';
      window.opener.getcontentInstragram();
    });
  <?php } else { ?>
    window.close();
  <?php } ?>
</script>
