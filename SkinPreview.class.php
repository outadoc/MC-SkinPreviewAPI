<?php
	
	class SkinRenderer {

		private $skin_width;

		public function __construct($render_width = 85) {
			$this->skin_width = $render_width;
		}

		public function renderSkin($skin_path, $skin_type, $skin_side) {
			// Load the skin
			$skin = imagecreatefrompng($skin_path);

			// If for some reason we couldn't download the file, use a steve skin instead
			if($skin === false) {
				$skin_path = 'char.png';
				$skin = imagecreatefrompng($skin_path);
			}

			// Create the destination image (16*32 transparent png file)
			$preview = imagecreatetruecolor(16, 32);

			// Set the desired arm width (3 or 4 pixels) and check if it's a post-1.8 skin
			$arm_width = ($skin_type === 'alex' ? 3 : 4);
			$is_new_format = $this->isNewSkinFormat($skin_path);
			
			// Let's have a transparent background!
			$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
			imagefill($preview, 0, 0, $transparent);
			

			// Copy all the parts of the skin where they belong to, on a new blank image
			
			if($skin_side == 'front') {
				// Making a preview of the front of the skin

				// Face
				imagecopy($preview, $skin, 4, 0, 8, 8, 8, 8);

				// Chest
				imagecopy($preview, $skin, 4, 8, 20, 20, 8, 12);

				// Right arm
				imagecopy($preview, $skin, 4 - $arm_width, 8, 44, 20, $arm_width, 12);

				// Left arm
				if(!$is_new_format || $this->areAllPixelsOfSameColor($skin, 36, 52, $arm_width, 12)) {
					$this->flipSkin($preview, $skin, 12, 8, 44, 20, $arm_width, 12);
				} else {
					imagecopy($preview, $skin, 12, 8, 36, 52, $arm_width, 12);
				}

				// Right leg
				imagecopy($preview, $skin, 4, 20, 4, 20, 4, 12);

				// Left leg
				if(!$is_new_format || $this->areAllPixelsOfSameColor($skin, 20, 52, 4, 12)) {
					$this->flipSkin($preview, $skin, 8, 20, 4, 20, 4, 12);
				} else {
					imagecopy($preview, $skin, 8, 20, 20, 52, 4, 12);
				}

				// Head armor
				$this->overlayArmor($skin, $preview, 4, 0, 40, 8, 8, 8);

				if($is_new_format) {
					// Chest
					$this->overlayArmor($skin, $preview, 4, 8, 32, 36, 8, 12);

					// Right arm
					$this->overlayArmor($skin, $preview, 4 - $arm_width, 8, 44, 36, $arm_width, 12);

					// Left arm
					$this->overlayArmor($skin, $preview, 12, 8, 52, 52, $arm_width, 12);

					// Right leg
					$this->overlayArmor($skin, $preview, 4, 20, 4, 36, 4, 12);

					// Left leg
					$this->overlayArmor($skin, $preview, 8, 20, 4, 52, 4, 12);
				}

			} else {
				
				// Making a preview of the back of the skin

				// Face
				imagecopy($preview, $skin, 4, 0, 24, 8, 8, 8);

				// Chest
				imagecopy($preview, $skin, 4, 8, 32, 20, 8, 12);

				// Right arm
				imagecopy($preview, $skin, 12, 8, 48 + $arm_width, 20, $arm_width, 12);

				// Left arm
				if(!$is_new_format || $this->areAllPixelsOfSameColor($skin, 40 + $arm_width, 52, $arm_width, 12)) {
					$this->flipSkin($preview, $skin, 4 - $arm_width, 8, 48 + $arm_width, 20, $arm_width, 12);
				} else {
					imagecopy($preview, $skin, 4 - $arm_width, 8, 40 + $arm_width, 52, $arm_width, 12);
				}

				// Right leg
				imagecopy($preview, $skin, 8, 20, 12, 20, 4, 12);

				// Left leg
				if(!$is_new_format || $this->areAllPixelsOfSameColor($skin, 28, 52, 4, 12)) {
					$this->flipSkin($preview, $skin, 4, 20, 12, 20, 4, 12);
				} else {
					imagecopy($preview, $skin, 4, 20, 28, 52, 4, 12);
				}

				// Head armor
				$this->overlayArmor($skin, $preview, 4, 0, 56, 8, 8, 8);

				if($is_new_format) {
					// Chest
					$this->overlayArmor($skin, $preview, 4, 8, 32, 36, 8, 12);

					// Right arm
					$this->overlayArmor($skin, $preview, 12, 8, 48 + $arm_width, 36, $arm_width, 12);

					// Left arm
					$this->overlayArmor($skin, $preview, 4 - $arm_width, 8, 56 + $arm_width, 52, $arm_width, 12);

					// Right leg
					$this->overlayArmor($skin, $preview, 8, 20, 12, 36, 4, 12);

					// Left leg
					$this->overlayArmor($skin, $preview, 4, 20, 12, 52, 4, 12);
				}
			}

			imagedestroy($skin);

			return $this->resizeBitmap($preview);
		}

		public function renderSkinBase64($skin_path, $skin_type, $skin_side) {
			$data = $this->renderSkin($skin_path, $skin_type, $skin_side);

			// Write the image to the PHP output buffer
			ob_start();
    		imagepng($data);
    		$contents = ob_get_contents();
			ob_end_clean();

			// Encode the contents of the buffer to base 64
			return base64_encode($contents);
		}

		private function resizeBitmap(&$skin) {
			// Resize the render: currently, it's a 16*32 file. We usually want it larger.
			$fullsize = imagecreatetruecolor($this->skin_width, $this->skin_width * 2);
			imagesavealpha($fullsize, true);
			
			// Fill the render with a transparent background
			$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
			imagefill($fullsize, 0, 0, $transparent);
			
			// Copy the render to the full-sized image
			imagecopyresized($fullsize, $skin, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($skin), imagesy($skin));

			return $fullsize;
		}

		private function flipSkin(&$preview, &$skin, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h) {
			// In Minecraft, some parts of the skins are flipped horizontally, so we have to do that too
			// Uses the same parameters as imagecopy

			$tmp = imagecreatetruecolor($src_w, $src_h);

			// Sets a transparent background
			imagesavealpha($tmp, true);
			$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
			imagefill($tmp, 0, 0, $transparent);

			// Copy to a new image, flip and copy back to the original
			imagecopy($tmp, $skin, 0, 0, $src_x, $src_y, $src_w, $src_h);
			$this->flipHorizontal($tmp);
			imagecopy($preview, $tmp, $dst_x, $dst_y, 0, 0, $src_w, $src_h);

			imagedestroy($tmp);
		}

		private function flipHorizontal(&$img) {
		 	$size_x = imagesx($img);
		 	$size_y = imagesy($img);

		 	$tmp = imagecreatetruecolor($size_x, $size_y);

		 	// Sets a transparent background
		 	imagesavealpha($tmp, true);
			$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
			imagefill($tmp, 0, 0, $transparent);

		 	$x = imagecopyresampled($tmp, $img, 0, 0, ($size_x-1), 0, $size_x, $size_y, 0-$size_x, $size_y);
		 	
		 	if ($x) {
				$img = $tmp;
			}
		}

		private function areAllPixelsOfSameColor(&$img, $x, $y, $w, $h) {		
			$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
			$black = imagecolorallocatealpha($img, 255, 255, 255, 0);

			// Check for a 8*8 square of pixels starting at ($x;$y)
			for($i = $x; $i < $x + $w; $i++) {
				for($j = $y; $j < $y + $h; $j++) {

					// If this pixel isn't the same color as the first one, then return false
					if(imagecolorat($img, $i, $j) != $transparent 
						&& imagecolorat($img, $i, $j) != $black) {
						return false;
					} 
				}
			}

			return true;
		}
		
		private function isNewSkinFormat(&$skin_path) {
			$size = getimagesize($skin_path);
			return ($size[1] == $size[0] && $size[0] == 64);
		}

		private function overlayArmor(&$img, &$dest, $dst_x, $dst_y, $x, $y, $w, $h) {
			if(!$this->areAllPixelsOfSameColor($img, $x, $y, $w, $h)) {
				imagecopy($dest, $img, $dst_x, $dst_y, $x, $y, $w, $h);
			}
		}

	}
