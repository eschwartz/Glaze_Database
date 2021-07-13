<?php
require_once('../class.upload.php');
$img = new Upload($_FILES['myfile']);
//$img->file_new_name_body = 'testName';

$img->image_resize = true;
$img->image_convert = jpg;
$img->image_x = 200;
$img->image_ratio_y = true;

$img->Process('uploads/');

$result = 1;
if ($img->processed) {
	$success = true;
	$msg = "Image uploaded succesfully to:" . $img->file_dst_pathname;
	$src = $img->file_dst_pathname;
}
else {
	$success = false;
	$msg ="Error: " . $img->error;
	$src = false;
}


	sleep(1);	// Waits a second before showing file 
?>

<script language="javascript" type="text/javascript">
   window.top.window.stopUpload(<?=$success?>, "<?=$msg?>", "<?=$src?>");
</script>   