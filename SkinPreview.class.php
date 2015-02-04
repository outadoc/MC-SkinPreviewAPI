<?php

	/* 
	 * SkinPreview.class.php - Library to render previews of Minecraft (tm) skins
	 * Copyright (C) 2012-2015 Baptiste Candellier
	 * 
	 * This program is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 * 
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 * 
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 */

	class SkinRenderer
	{

		private $skin_width;
		private $fallback_skin_path;

		/**
		 * Creates a new skin renderer. It can then be used to render as many skins as you like.
		 *
		 * @param int $render_width the width of the rendered skin (corresponding height will be calculated automatically)
		 * @param string $fallback_skin_path the skin file that will be used if the requested skin can't be loaded
		 */
		public function __construct($render_width = 85, $fallback_skin_path = 'char.png')
		{
			$this->skin_width         = $render_width;
			$this->fallback_skin_path = $fallback_skin_path;
		}

		/**
		 * Renders a local Minecraft skin using its path.
		 *
		 * @param string $skin_path the path to the skin that is to be rendered
		 * @param string $skin_type the skin type; must be 'steve' or 'alex'
		 * @param string $skin_side the side of the skin to render; must be 'front' or 'back'
		 *
		 * @return resource A resource containing the rendered skin.
		 *         You can use it with functions like imagepng.
		 */
		public function renderSkinFromPath($skin_path, $skin_type = 'steve', $skin_side = 'front')
		{
			// Load the skin
			$skin = imagecreatefrompng($skin_path);

			// If for some reason we couldn't download the file, use a steve skin instead
			if ($skin === false) {
				$skin_path = $this->fallback_skin_path;
				$skin      = imagecreatefrompng($skin_path);
			}

			return $this->renderSkinFromResource($skin, $skin_type, $skin_side);
		}

		/**
		 * Renders a local Minecraft skin from its bitmap.
		 *
		 * @param resource $skin a resource containing the actual skin to render
		 * @param string $skin_type the skin type; must be 'steve' or 'alex'
		 * @param string $skin_side the side of the skin to render; must be 'front' or 'back'
		 *
		 * @return resource A resource containing the rendered skin.
		 *         You can use it with functions like imagepng.
		 */
		public function renderSkinFromResource($skin, $skin_type = 'steve', $skin_side = 'front')
		{
			// Create the destination image (16*32 transparent png file)
			$preview = imagecreatetruecolor(16, 32);

			// Set the desired arm width (3 or 4 pixels) and check if it's a post-1.8 skin
			$arm_width     = ($skin_type === 'alex' ? 3 : 4);
			$is_new_format = $this->isNewSkinFormat($skin);

			// Let's have a transparent background!
			$transparent = imagecolorallocatealpha($preview, 255, 255, 255, 127);
			imagefill($preview, 0, 0, $transparent);


			// Copy all the parts of the skin where they belong to, on a new blank image

			if ($skin_side == 'front') {
				// Making a preview of the front of the skin

				// Face
				imagecopy($preview, $skin, 4, 0, 8, 8, 8, 8);

				// Chest
				imagecopy($preview, $skin, 4, 8, 20, 20, 8, 12);

				// Right arm
				imagecopy($preview, $skin, 4 - $arm_width, 8, 44, 20, $arm_width, 12);

				// Left arm
				if (!$is_new_format || $this->isRectTransparent($skin, 36, 52, $arm_width, 12)) {
					$this->flipRectHorizontal($preview, $skin, 12, 8, 44, 20, $arm_width, 12);
				} else {
					imagecopy($preview, $skin, 12, 8, 36, 52, $arm_width, 12);
				}

				// Right leg
				imagecopy($preview, $skin, 4, 20, 4, 20, 4, 12);

				// Left leg
				if (!$is_new_format || $this->isRectTransparent($skin, 20, 52, 4, 12)) {
					$this->flipRectHorizontal($preview, $skin, 8, 20, 4, 20, 4, 12);
				} else {
					imagecopy($preview, $skin, 8, 20, 20, 52, 4, 12);
				}

				// Head armor
				$this->overlayArmor($skin, $preview, 4, 0, 40, 8, 8, 8);

				if ($is_new_format) {
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
				if (!$is_new_format || $this->isRectTransparent($skin, 40 + $arm_width, 52, $arm_width, 12)) {
					$this->flipRectHorizontal($preview, $skin, 4 - $arm_width, 8, 48 + $arm_width, 20, $arm_width, 12);
				} else {
					imagecopy($preview, $skin, 4 - $arm_width, 8, 40 + $arm_width, 52, $arm_width, 12);
				}

				// Right leg
				imagecopy($preview, $skin, 8, 20, 12, 20, 4, 12);

				// Left leg
				if (!$is_new_format || $this->isRectTransparent($skin, 28, 52, 4, 12)) {
					$this->flipRectHorizontal($preview, $skin, 4, 20, 12, 20, 4, 12);
				} else {
					imagecopy($preview, $skin, 4, 20, 28, 52, 4, 12);
				}

				// Head armor
				$this->overlayArmor($skin, $preview, 4, 0, 56, 8, 8, 8);

				if ($is_new_format) {
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

			return $this->resizeBitmap($preview, $this->skin_width);
		}

		/**
		 * Renders a Minecraft skin as a base 64 string.
		 *
		 * @param string $skin_path the path to the skin that is to be rendered
		 * @param string $skin_type the skin type; must be 'steve' or 'alex'
		 * @param string $skin_side the side of the skin to render; must be 'front' or 'back'
		 *
		 * @return string the rendered skin, encoded as a PNG base64 string.
		 */
		public function renderSkinBase64($skin_path, $skin_type = 'steve', $skin_side = 'front')
		{
			$data = $this->renderSkinFromPath($skin_path, $skin_type, $skin_side);

			// Write the image to the PHP output buffer
			ob_start();
			imagepng($data);
			$contents = ob_get_contents();
			ob_end_clean();

			// Encode the contents of the buffer to base 64
			return base64_encode($contents);
		}

		/**
		 * Resizes a bitmap to the specified width. The height will be calculated automatically.
		 *
		 * @param resource $bmp the image to be resized
		 * @param int $width the width of the final bitmap
		 *
		 * @return resource the resized image
		 */
		private function resizeBitmap(&$bmp, $width)
		{
			// Resize the render: currently, it's a 16*32 file. We usually want it larger.
			$fullsize = imagecreatetruecolor($width, $width * 2);
			imagesavealpha($fullsize, true);

			// Fill the render with a transparent background
			$transparent = imagecolorallocatealpha($fullsize, 255, 255, 255, 127);
			imagefill($fullsize, 0, 0, $transparent);

			// Copy the render to the full-sized image
			imagecopyresized($fullsize, $bmp, 0, 0, 0, 0, imagesx($fullsize), imagesy($fullsize), imagesx($bmp), imagesy($bmp));

			return $fullsize;
		}

		/**
		 * Flips a part of a bitmap horizontally and draws it onto another bitmap.
		 * Behaves like imagecopy.
		 *
		 * @param resource $dest the bitmap we will be drawing onto
		 * @param resource $src the bitmap that contains the pixels to flip
		 * @param int $dst_x x-coordinate of destination point
		 * @param int $dst_y y-coordinate of destination point
		 * @param int $src_x x-coordinate of source point
		 * @param int $src_y y-coordinate of source point
		 * @param int $src_w source width
		 * @param int $src_h source height
		 */
		private function flipRectHorizontal(&$dest, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h)
		{
			// In Minecraft, some parts of the skins are flipped horizontally, so we have to do that too
			// Uses the same parameters as imagecopy

			$tmp = imagecreatetruecolor($src_w, $src_h);

			// Sets a transparent background
			imagesavealpha($tmp, true);
			$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
			imagefill($tmp, 0, 0, $transparent);

			// Copy to a new image, flip and copy back to the original
			imagecopy($tmp, $src, 0, 0, $src_x, $src_y, $src_w, $src_h);
			$this->flipHorizontal($tmp);
			imagecopy($dest, $tmp, $dst_x, $dst_y, 0, 0, $src_w, $src_h);

			imagedestroy($tmp);
		}

		/**
		 * Flips all the pixels of a bitmap horizontally.
		 *
		 * @param resource $bmp the bitmap to flip
		 */
		private function flipHorizontal(&$bmp)
		{
			$size_x = imagesx($bmp);
			$size_y = imagesy($bmp);

			$tmp = imagecreatetruecolor($size_x, $size_y);

			// Sets a transparent background
			imagesavealpha($tmp, true);
			$transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
			imagefill($tmp, 0, 0, $transparent);

			$x = imagecopyresampled($tmp, $bmp, 0, 0, ($size_x - 1), 0, $size_x, $size_y, 0 - $size_x, $size_y);

			if ($x) {
				$bmp = $tmp;
			}
		}

		/**
		 * Overlays an armor part onto a destination.
		 *
		 * @param resource $armor the bitmap containing an armor part
		 * @param resource $dest the bitmap to draw the armor on to
		 * @param int $dst_x x-coordinate of destination point
		 * @param int $dst_y y-coordinate of destination point
		 * @param int $x x-coordinate of source point
		 * @param int $y y-coordinate of source point
		 * @param int $w source width
		 * @param int $h source height
		 */
		private function overlayArmor(&$armor, &$dest, $dst_x, $dst_y, $x, $y, $w, $h)
		{
			if (!$this->isRectTransparent($armor, $x, $y, $w, $h)) {
				imagecopy($dest, $armor, $dst_x, $dst_y, $x, $y, $w, $h);
			}
		}

		/**
		 * Checks if all the pixels of a determined area are either transparent or black.
		 *
		 * @param resource $img the bitmap containing the pixels to check
		 * @param int $x x-coordinate of source point
		 * @param int $y y-coordinate of source point
		 * @param int $w source width
		 * @param int $h source height
		 *
		 * @return bool true if the rectangle is completely black or transparent, false if it's not
		 */
		private function isRectTransparent(&$img, $x, $y, $w, $h)
		{
			$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
			$black       = imagecolorallocatealpha($img, 255, 255, 255, 0);

			// Check for a 8*8 square of pixels starting at ($x;$y)
			for ($i = $x; $i < $x + $w; $i++) {
				for ($j = $y; $j < $y + $h; $j++) {

					// If this pixel isn't the same color as the first one, then return false
					if (imagecolorat($img, $i, $j) != $transparent
						&& imagecolorat($img, $i, $j) != $black
					) {
						return false;
					}
				}
			}

			return true;
		}

		/**
		 * Checks if a skin is of the post-1.8 format.
		 *
		 * @param resource $skin the skin to check
		 *
		 * @return bool true if the skin is in post-1.8 format, else false
		 */
		private function isNewSkinFormat(&$skin)
		{
			return (imagesy($skin) == imagesx($skin) && imagesx($skin) == 64);
		}

		/**
		 * Changes the width of the skins to be rendered.
		 *
		 * @param int $width the width of the skin preview
		 */
		public function setSkinWidth($width)
		{
			$this->skin_width = $width;
		}

	}
