<?php
	define('MC_SKINS_BASE_URL', 'http://s3.amazonaws.com/MinecraftSkins/');
	
	$skin_path = null;
	$skin = null;
	
	//parameters: username=[mc username] OR url=[URL pointing to a skin png]
	
	if(isset($_GET['username']) && $_GET['username'] != null) {
		//if username is given, we set the skin path to the url pointing to the username on s3.amazonaws.com
		$skin_path = MC_SKINS_BASE_URL . $_GET['username'] . '.png';
	} else if(isset($_GET['url']) && $_GET['url'] != null) {
		//else if we're given an URL, we set the skin path to this url
		$skin_path = $_GET['url'];
	} else {
		$skin_path = 'char.png';
	}
	
	//first, we load the skin
	$skin = @imagecreatefrompng($skin_path);

	if(!$skin) {
		//if for some reason we couldn't download the file, use the local skin
		$skin = imagecreatefrompng('char.png');
	}

	//then, we create the destination image (16*32 transparent png file)
	$preview = imagecreatetruecolor(16, 32);
	
	//we want it to have a transparent background
	$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
	imagefill($preview, 0, 0, $transparent);
	
	//we copy all the parts of the skin where they belong to, on a new blank image
	if($_GET['side'] == 'back') {
		//if we want to get the preview of the back of the skin

		//copy head back
		imagecopy($preview, $skin, 4, 0, 24, 8, 8, 8);
		//copy back
		imagecopy($preview, $skin, 4, 8, 32, 20, 8, 12);
		//copy arms back
		imagecopy($preview, $skin, 0, 8, 52, 20, 4, 12);
		flipSkin($preview, $skin, 12, 8, 52, 20, 4, 12);
		//copy legs back
		imagecopy($preview, $skin, 4, 20, 12, 20, 4, 12);
		flipSkin($preview, $skin, 8, 20, 12, 20, 4, 12);
		//copy armor
		if(!checkForPlainSquare($skin, 56, 8)) {
			imagecopy($preview, $skin, 4, 0, 56, 8, 8, 8);
		}

	} else {
		//else, if we want the front of the skin

		//copy face
		imagecopy($preview, $skin, 4, 0, 8, 8, 8, 8);
		//copy chest
		imagecopy($preview, $skin, 4, 8, 20, 20, 8, 12);
		//copy arms
		imagecopy($preview, $skin, 0, 8, 44, 20, 4, 12);
		flipSkin($preview, $skin, 12, 8, 44, 20, 4, 12);
		//copy legs
		imagecopy($preview, $skin, 4, 20, 4, 20, 4, 12);		
		flipSkin($preview, $skin, 8, 20, 4, 20, 4, 12);
		//copy armor
		if(!checkForPlainSquare($skin, 40, 8)) {
			imagecopy($preview, $skin, 4, 0, 40, 8, 8, 8);
		}
	}
	
	//we don't need the original skin anymore
	imagedestroy($skin);
	
	//resize the preview: currently, it's a 16*32 file. We want it larger. 85*170 is fine
	$fullsize = imagecreatetruecolor(85, 170);
	imagesavealpha($fullsize, true);
	$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
	imagefill($fullsize, 0, 0, $transparent);
	
	//copy the preview to the full-sized image
	imagecopyresized($fullsize, $preview, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview), imagesy($preview));
	
	//and we're done :D
	header ("Content-type: image/png");
	imagepng($fullsize);
	
	function flipSkin($preview, $skin, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h) {
		//in Minecraft, some parts of the skins are flipped horizontally. We're simulating it here.
		//uses the same parameters as imagecopy

		$tmp = imagecreatetruecolor(4, 12);

		//set a transparent background
		imagesavealpha($tmp, true);
		$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
		imagefill($tmp, 0, 0, $transparent);

		//copy, flip and copy back
		imagecopy($tmp, $skin, 0, 0, $src_x, $src_y, $src_w, $src_h);
		flipHorizontal($tmp);
		imagecopy($preview, $tmp, $dst_x, $dst_y, 0, 0, $src_w, $src_h);

		imagedestroy($tmp);
	}
	
	function flipHorizontal(&$img) {
	 	$size_x = imagesx($img);
	 	$size_y = imagesy($img);

	 	$tmp = imagecreatetruecolor($size_x, $size_y);

	 	//set a transparent background
	 	imagesavealpha($tmp, true);
		$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
		imagefill($tmp, 0, 0, $transparent);

	 	$x = imagecopyresampled($tmp, $img, 0, 0, ($size_x-1), 0, $size_x, $size_y, 0-$size_x, $size_y);
	 	
	 	if ($x) {
			$img = $tmp;
		} else {
			die("Unable to flip image");
		}
	}

	function checkForPlainSquare($img, $x, $y) {
		//remember the color of the first pixel
		$firstPixColor = imagecolorat($img, 0, 0);

		//check for a 8*8 square of pixels starting at ($x;$y)
		for($i = $x; $i < $x + 8; $i++) {
			for($j = $y; $j < $y + 8; $j++) {
				//if this pixel isn't the same color, then return false
				if(imagecolorat($img, $i, $j) != $firstPixColor) {
					return false;
				} 
			}
		}

		//if all pixels are the same color, this should be true
		return true;
	}
?>