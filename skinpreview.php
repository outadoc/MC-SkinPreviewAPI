<?php
	define('MC_SKINS_BASE_URL', 'http://s3.amazonaws.com/MinecraftSkins/');
	$skin_src = 'char.png';
	
	if(isset($_GET['pseudo']) && $_GET['pseudo'] != null && !is404(MC_SKINS_BASE_URL . $_GET['pseudo'] . '.png')) {
		$skin_src = MC_SKINS_BASE_URL . $_GET['pseudo'] . '.png';
	} else if(isset($_GET['url']) && $_GET['url'] != null && !is404($_GET['url'])) {
		$skin_src = $_GET['url'];
	}
	
	$skin = imagecreatefrompng($skin_src);
	$preview = imagecreatetruecolor(16, 32);
	
	$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
	imagefill($preview, 0, 0, $transparent);
	
	if($_GET['side'] == 'back') {
		//head back
		imagecopy($preview, $skin, 4, 0, 24, 8, 8, 8);
		
		//back
		imagecopy($preview, $skin, 4, 8, 32, 20, 8, 12);
		
		//arms back
		imagecopy($preview, $skin, 0, 8, 52, 20, 4, 12);
		flipSkin($preview, $skin, 12, 8, 52, 20, 4, 12);
		
		//legs back
		imagecopy($preview, $skin, 4, 20, 12, 20, 4, 12);
		flipSkin($preview, $skin, 8, 20, 12, 20, 4, 12);
			
		//armor
		imagecopy($preview, $skin, 4, 0, 56, 8, 8, 8);
	} else {
		//face
		imagecopy($preview, $skin, 4, 0, 8, 8, 8, 8);
		
		//chest
		imagecopy($preview, $skin, 4, 8, 20, 20, 8, 12);
		
		//arms
		imagecopy($preview, $skin, 0, 8, 44, 20, 4, 12);
		flipSkin($preview, $skin, 12, 8, 44, 20, 4, 12);
		
		//legs
		imagecopy($preview, $skin, 4, 20, 4, 20, 4, 12);		
		flipSkin($preview, $skin, 8, 20, 4, 20, 4, 12);
			
		//armor
		imagecopy($preview, $skin, 4, 0, 40, 8, 8, 8);
	}
	
	imagedestroy($skin);
	
	$fullsize = imagecreatetruecolor(85, 170);
	imagesavealpha($fullsize, true);
	$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
	imagefill($fullsize, 0, 0, $transparent);
	
	imagecopyresized($fullsize, $preview, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($preview), imagesy($preview));
	
	header ("Content-type: image/png");
	imagepng($fullsize);
	
	function is404($skin_src) {
	    $handle = curl_init($skin_src);
	    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
	    $response = curl_exec($handle);
	    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	    curl_close($handle);
	
	    if ($httpCode >= 200 && $httpCode < 400) {
	        return false;
	    } else {
	        return true;
	    }
	}
	
	function flipSkin($preview, $skin, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h) {
		$tmp = imagecreatetruecolor(4, 12);
		imagecopy($tmp, $skin, 0, 0, $src_x, $src_y, $src_w, $src_h);
		flipHorizontal($tmp);
		imagecopy($preview, $tmp, $dst_x, $dst_y, 0, 0, $src_w, $src_h);
		imagedestroy($tmp);
	} 
	
	function flipHorizontal(&$img) {
	 	$size_x = imagesx($img);
	 	$size_y = imagesy($img);
	 	$temp = imagecreatetruecolor($size_x, $size_y);
	 	$x = imagecopyresampled($temp, $img, 0, 0, ($size_x-1), 0, $size_x, $size_y, 0-$size_x, $size_y);
	 	
	 	if ($x) {
			$img = $temp;
		} else {
			die("Unable to flip image");
		}
	}
?>