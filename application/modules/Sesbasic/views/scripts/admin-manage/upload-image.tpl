<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: upload-photo.tpl 2015-07-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
?>

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>JustBoil's Result Page</title>
		<script language="javascript" type="text/javascript">
			window.parent.window.jbImagesDialog.uploadFinish({
				filename: '<?php echo $this->photo_url ?>',
				result: '<?php $this->status ? 'file_uploaded' : $this->error ?>',
				resultCode: '<?php $this->status ? 'ok' : 'failed' ?>'
			});
		</script>
		<style type="text/css">
			body {font-family: Courier, "Courier New", monospace; font-size:11px;}
		</style>
	</head>
	<body>
		Result: <?php $this->status ? 'file_uploaded' : $this->error ?>
	</body>
</html>
