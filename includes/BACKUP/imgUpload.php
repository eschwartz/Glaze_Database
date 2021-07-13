<?php
include('SuperJS.php');
?>
<?php
// Image Uploader
// AJAX-type script
// For use with iframe
// See: http://www.ajaxf1.com/tutorial/ajax-file-upload-tutorial.html
$varID = trim($_POST['imgUpload_varID']);	// for some reason, glazeID had "\n"
$userID = trim($_POST['imgUpload_userID']);
$imgFile = $_FILES['imgFile'];
$imgDescr = mysql_escape_string(strip_tags($_POST['imgDescr']));

// Set values for image fields
// And prep for pushing onto JS 'glazeImage' array.
$glazeImage = array(
	'ImageDescr' => "'".mysql_escape_string(strip_tags($_POST['imgDescr']))."'",
	'ImageAuthor' => trim($_POST['imgUpload_userID']),
	'ImageDatePosted' => 'CURDATE()'
	);

$conn = mysql_connect("localhost", "earthcr1_DYLMG", "Plastichorse22@");
mysql_select_db("earthcr1_DYLMG");

require_once('image_upload/class.upload.php');
$img = new Upload($imgFile);

$img->image_resize = true;
$img->image_convert = jpg;
$img->image_x = 600;
$img->image_ratio_y = true;

$fileName = "glaze".$varID."_".urlencode($img->file_src_name_body);		// Rename file to "glaze(glazeID)_(imgName).jpg
$path = '/usr/'.$userID.'/images/';

$img->file_new_name_body = $fileName;		
$img->dir_auto_create = true;									// Creates directory if one doesn't exit
$img->Process('..'.$path);										// ??Unable to use root-relative "/dir/"

if ($img->processed) {
	$success = true;
	$msg = "Success.";

	$glazeImage['ImageSrc'] = "'".str_replace("../", "/",$img->file_dst_pathname)."'";		// Switches to root relative path (??)
	
	// Add image to Images table
	$sql = 	  "INSERT INTO Images (". join(',',array_keys($glazeImage)) . ") \n"
			. "VALUES (". join(',',array_values($glazeImage)) . ")";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	// Add image to GlazeImages table
	$newImageID = mysql_insert_id();
	$sql =	  "INSERT INTO VarImages (VarID, ImageID, ImageAuthor) \n"
			. "VALUES ($varID, $newImageID, $userID)";
	$rs = mysql_query($sql,$conn) or die("Could not execute query: " . mysql_error() . "<br>\n SQL code is: ". str_replace("\n","<br>\n",$sql));
	
	// Create Thumbnail image
	$img->image_x = 200;
	$img->image_y = 200;
	$img->image_resize = true;
	$img->image_convert = jpg;
	$img->image_ratio_crop = true;
	$img->file_new_name_body = 'thumb_'.$img->file_dst_name_body;
	$img->Process('..'.$path);		// Saves as /usr/~usrID~/images/thumb_glaze(~glazeID~)_(~imgName~).jpg
	if (!$img->processed) {
		$success = false;
		$msg ="Error in creating thumbnail: " . $img->error;
	}
	
	$img->clean();
}
else {
	$success = false;
	$msg ="Error: " . $img->error;
	$src = false;
}


?>

<script language="javascript" type="text/javascript">
   window.top.window.imgUploadCallback(<?=$success; ?>, <?=$newImageID?>,  <?=$glazeImage['ImageSrc']?>, "<?=$msg?>");
   // function imgUploadCallback(success, src, newImageID, msg) {
</script>   