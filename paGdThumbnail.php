<?php

/**
 * Create a thumbnail version of an image using php gd image library www.php.net/gd
 * http://www.phpace.com/image-resizing-with-php
 * @copyright 2008 Sam Yapp www.phpace.com
 *
 * @licence MIT http://www.phpace.com/mit
 * essentially do whatever you want with this code but keep this copyright
 * notice in place.
 *
 * @param $image A gd image resource to create a thumbnail from.
 *
 * @param $max_width The maximum width of the thumbnail.
 * @param $max_height The maximum height of the thumbnail.
 *
 * If one of max_width or max_height is zero or null then it will be calculated
 * in proportion with the given dimension.
 * 
 * @param $method Either 'scale' to scale the image proportionally to 
 * fit the max width and height, or 'crop' to scale and then crop the image
 * so it completely fills the thumbnail max width and height.
 *
 * @param $bgColour an array of r,g,b values to use as a background colour
 * for any excess width or height of the thumbnail that the resized image does not
 * fit into. For portrait images this will be the left and right sides, for landscape
 * images the top and bottom.
 *
 * If a background colour is specified then the created thumbnail image will always be
 * exactly max_width x max_height.
 *
 * If the source image is *smaller* than the max_width and max_height then unless bgColour
 * is specified the thumbnail will be the same size as the original image.
 *
 * Specifying "crop" as the method with no bgcolour will normally result in a thumbnail of
 * exactly $max_width x $max_height, unless the original has a width or height smaller than
 * the max_width and max_height values.
 *
 * Specifying "scale" as the method will proportionally scale the image to fit within the
 * maximum width and height and create an image of these new proportional dimensions
 * unless a bgColour is specified when it will still resize proportionally but the resulting
 * thumbnail will be max_width x max_height with any empty space filled with bgColour.
 */
function paGdThumbnail($image, $max_width, $max_height, $method = 'scale', $bgColour = null)
{
	// get the current dimensions of the image
	$src_width = imagesx($image);
	$src_height = imagesy($image);

	// if either max_width or max_height are 0 or null then calculate it proportionally
	if( !$max_width ){
		$max_width = $src_width / ($src_height / $max_height);
	}
	elseif( !$max_height ){
		$max_height = $src_height / ($src_width / $max_width);
	}

	// initialize some variables
	$thumb_x = $thumb_y = 0;	// offset into thumbination image

	// if scaling the image calculate the dest width and height
	$dx = $src_width / $max_width;
	$dy = $src_height / $max_height;
	if( $method == 'scale' ){
		$d = max($dx,$dy);
	}
	// otherwise assume cropping image
	else{
		$d = min($dx, $dy);
	}
	$new_width = $src_width / $d;
	$new_height = $src_height / $d;
	// sanity check to make sure neither is zero
	$new_width = max(1,$new_width);
	$new_height = max(1,$new_height);

	$thumb_width = min($max_width, $new_width);
	$thumb_height = min($max_height, $new_height);

	// if bgColour is an array of rgb values, then we will always create a thumbnail image of exactly
	// max_width x max_height
	if( is_array($bgColour) ){
		$thumb_width = $max_width;
		$thumb_height = $max_height;
		$thumb_x = ($thumb_width - $new_width) / 2;
		$thumb_y = ($thumb_height - $new_height) / 2;
	}
	else{
		$thumb_x = ($thumb_width - $new_width) / 2;
		$thumb_y = ($thumb_height - $new_height) / 2;
	}

	// create a new image to hold the thumbnail
	$thumb = imagecreatetruecolor($thumb_width, $thumb_height);
	if( is_array($bgColour) ){
		$bg = imagecolorallocate($thumb, $bgColour[0], $bgColour[1], $bgColour[2]);
		imagefill($thumb,0,0,$bg);
	}

	// copy from the source to the thumbnail
	imagecopyresampled($thumb, $image, $thumb_x, $thumb_y, 0, 0, $new_width, $new_height, $src_width, $src_height);
	return $thumb;
}

