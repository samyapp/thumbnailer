<?php

/**
 * Accept a zip archive uploaded via a web form and generate and return a zip archive containing
 * thumbnails of every image in it.
 * http://www.phpace.com/extract-images-from-a-zip-archive
 * @copyright 2008 Sam Yapp www.phpace.com
 *
 * @licence MIT http://www.phpace.com/mit
 * essentially do whatever you want with this code but keep this copyright
 * notice in place.
 * @version 1.0 2008-09-17
 *
 * @usage Put my image resizing function http://www.phpace.com/image-resizing-with-php in 
 * the same directory and browse to this script to use.
 * You need the php zip extension installed, the directory needs to be writable by the script.
 */

require_once dirname(__FILE__) . '/paGdThumbnail.php';

$thumb_width = 150;
$thumb_height = 150;
$thumb_quality = 75;
$thumb_method = 'crop';
$thumb_bgColour = array(0,0,0);

// give us lots of time
set_time_limit(0);

// use to store error messages in.
$error = '';

$errors = array(
	UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
	UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
	UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
	UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
	UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
);

// get url to link to thumbs
$pathinfo = pathinfo($_SERVER['SCRIPT_NAME']);
$download_url = 'http://'.$_SERVER['HTTP_HOST'] . '/' . $pathinfo['dirname'] . '/thumbs.zip';

// has the form been submitted?
if( isset($_POST['submit']) ){

	// was a file submitted?
	if( isset($_FILES['zip']) ){
		// any errors?
		if( !$_FILES['zip']['error'] ){
			// security check
			if( is_uploaded_file($_FILES['zip']['tmp_name']) ){
				// is it a zip file?
				if( preg_match('#\.zip$#i', $_FILES['zip']['name']) ){

					// get the thumbnail options from the form
					$thumb_width = (int)$_POST['thumb_width'];
					$thumb_height = (int)$_POST['thumb_height'];
					$thumb_method = $_POST['thumb_method'];

					// create a zip archive object
					$zip = new ZipArchive();
					if( $zip->open($_FILES['zip']['tmp_name']) ){
						$output_name = 'thumbs.zip';
						// create output archive
						$output = new ZipArchive();
						if( $output->open($output_name, ZipArchive::OVERWRITE ) ){

							// loop through all the files in the archive
							for( $i = 0; $i < $zip->numFiles; $i++){
								$entry = $zip->statIndex($i);
								// is it an image?	
								if( $entry['size'] > 0 && preg_match('#\.(jpg|gif|png)$#i', $entry['name'] ) ){
									$file = $zip->getFromIndex($i);
									if( $file ){
										$image = imagecreatefromstring($file);
										if( $image ){
											$thumb = paGdThumbnail($image, $thumb_width, $thumb_height, $thumb_method, $thumb_bgColour);
											imagedestroy($image);
											if( $thumb ){
												ob_start();
												imagejpeg($thumb,null,$thumb_quality);
												imagedestroy($thumb);
												$thumb_data = ob_get_clean();
												$output->addFromString(preg_replace('#\.(jpg|gif|png)$#i', '_t.jpg', $entry['name']), $thumb_data);
											}
										}
									}
								}
							}
							$output->close();
						}
						$zip->close();
						header("Location: $download_url");
						exit();
					}
				}
				else{

				}
			}
		}
		// display an error message
		else{
			$error = $errors[$_FILES['zip']['error']];
		}

	}

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Zip to Thumbnails :p</title>
</head>
<body>
	<h2>Upload a zip archive containing images in jpeg, png or gif format.</h2>
	<p>You will receive a zip archive containing a thumbnail for each image in the original archive.</p>
	<p>Select the thumbnail width, height and method using the form below.</p>
<?php if( $error ){ ?>
	<h3 style="color: red;"><?php echo $error?></h3>
<?php } ?>
	<form action="<?php echo $_SERVER['SCRIPT_NAME']?>" method="post" enctype="multipart/form-data">
		<input type="file" name="zip" />
		Width: <input type="text" name="thumb_width" value="<?php echo $thumb_width?>" size="4" />
		Height: <input type="text" name="thumb_width" value="<?php echo $thumb_width?>" size="4" />
		<select name="thumb_method">
			<option value="crop">Crop</option>
			<option value="scale"<?php if( $thumb_method == 'scale' ) echo ' selected="selected" '?>>Scale</option>
		</select>
		<input type="submit" name="submit" value="Create Thumbnails" />
	</form>
</body>
</html>