<?php

/**
 * Create a thumbnail for each image in the same directory as this script, then generate html to view thumbs and images.
 * http://www.phpace.com/create-thumbnails-for-all-images-in-a-directory
 * @copyright 2008 Sam Yapp www.phpace.com
 *
 * @licence MIT http://www.phpace.com/mit
 * essentially do whatever you want with this code but keep this copyright
 * notice in place.
 * @version 1.0 2008-09-17
 */

// thumbnail configuration
$thumb_width = 75;
$thumb_height = 75;
$thumb_method = 'crop';
$thumb_bgColour = null;//array(255,255,240);
$thumb_quality = 60;

// Let script run for as long as it needs to when creating thumbnails.
set_time_limit(0);
require_once dirname(__FILE__) . '/paGdThumbnail.php';

// get the path to the current directory
$path = dirname(__FILE__);

// get the url for the images
$path_info = pathinfo($_SERVER['SCRIPT_NAME']);
$url = $path_info['dirname'];

// create an array to store image names in.
$images = array();

$dir = new DirectoryIterator($path);

/*
 Loop through the directory, finding all images that aren't thumbnails.
 For each image:
	- if it doesn't already have a thumbnail, or the thumbnail is older than the image,
		create a thumbnail.
	- get info about the image
	- add the image to our images array
*/
foreach( $dir as $entry ){
	if( $entry->isFile() ){
		if( preg_match('#^(.+?)(_t)?\.(jpg|gif|png)#i', $entry->getFilename(), $matches) ){

			list( ,$name, $is_a_thumb, $extension) = $matches;

			// if its not a thumbnail
			if( !$is_a_thumb ){
				// does a valid thumbnail exist?
				$has_thumb = false;
				$thumb_file = $path . '/' . $matches[1] . '_t.jpg';
				if( file_exists($thumb_file) && filemtime($thumb_file) > filemtime($entry->getPathname()) ){
					$has_thumb = true;
				}
				else{
					// no thumbnail, so we shall create one!

					// create a gd image. reading the contents of the image file into a string, then
					// using imagecreatefromstring saves having to check the filetype and which 
					// imagecreatefrom(jpeg/gif/png) function to use
					$image = imagecreatefromstring(file_get_contents($entry->getPathname()));

					if( $image ){
						// create the thumbnail
						$thumb = paGdThumbnail($image, $thumb_width, $thumb_height, $thumb_method, $thumb_bgColour);
						// free the image resource
						imagedestroy($image);
						if( $thumb ){
							// save the thumbnail
							if( imagejpeg($thumb, $thumb_file, $thumb_quality) ){
								$has_thumb = true;
							}
							// free the memory used by the thumbnail image
							imagedestroy($thumb);
						}
					}
				}
				if( $has_thumb ){
					$images[$entry->getFilename()] = $name;
				}
			}
		}
	}
}

// sort the images array so always in the same order
ksort($images);

// end or processing, now display the gallery
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Thumbnails :p</title>
<style type="text/css">
<!--
#thumbs{
	position: relative;
}

#thumbs div{
	float: left;
	width: <?php echo $thumb_width + 30?>px;
	height: <?php echo $thumb_height + 30?>px;
	text-align: center;
}

#thumbs a:link img, #thumbs a:visited img{
	border: 1px solid #acacac;
	padding: 5px;
}

#thumbs a:hover img{
	border: 1px solid black;
}

-->
</style>
</head>
<body>
<div id="thumbs">
<?php foreach( $images as $imagename => $name ){ ?>

	<div>
		<a href="<?php echo $url . '/' . $imagename?>" title="Full Size"><img src="<?php echo $url . '/' . $name . '_t.jpg'?>"  /></a>
	</div>

<?php }?>
</div>
<div id="pb">
	powered by <a href="http://www.phpace.com/">a simple php image thumbnail script</a>
</div>
</body>
</html>
