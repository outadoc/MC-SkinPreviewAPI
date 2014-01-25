function getPreviewFromSkin(path, side, zoom) {
	//the source skin
	var img_orig = document.createElement("img");
	img_orig.setAttribute("src", path);

	//the context on which we'll draw the skin
	var ctx_orig = document.createElement("canvas").getContext("2d"),
	//the canvas/context on which we'll draw the preview
		ctx_tmp = document.createElement("canvas").getContext("2d"),
		ctx_prev = document.getElementById("skinpreview").getContext("2d");

	//when the skin is loaded, process it
	img_orig.addEventListener("load", function() {
		var head, armor, chest, arm_left, arm_right, leg_left, leg_right;
		ctx_orig.drawImage(img_orig, 0, 0);

		if(side != 'back') {
			//if we want a preview of the front of the skin or if nothing is specified
			//get body parts, one at a time
			head = ctx_orig.getImageData(8, 8, 8, 8);
			armor = ctx_orig.getImageData(40, 8, 8, 8);

			chest = ctx_orig.getImageData(20, 20, 8, 12);

			arm_left = ctx_orig.getImageData(44, 20, 4, 12);
			arm_right = ctx_orig.getImageData(44, 20, 4, 12);

			leg_left = ctx_orig.getImageData(4, 20, 4, 12);
			leg_right = ctx_orig.getImageData(4, 20, 4, 12);
		} else {
			//if we want a preview of the back of the skin
			head = ctx_orig.getImageData(24, 8, 8, 8);
			armor = ctx_orig.getImageData(56, 8, 8, 8);

			chest = ctx_orig.getImageData(32, 20, 8, 12);

			arm_left = ctx_orig.getImageData(52, 20, 4, 12);
			arm_right = ctx_orig.getImageData(52, 20, 4, 12);

			leg_left = ctx_orig.getImageData(12, 20, 4, 12);
			leg_right = ctx_orig.getImageData(12, 20, 4, 12);
		}

		ctx_orig = null; //don't need that anymore

		//we got everything, just stick the parts where they belong on the preview
		ctx_tmp.putImageData(overlayArmor(head, armor), 4, 0, 0, 0, 8, 8);
		ctx_tmp.putImageData(chest, 4, 8, 0, 0, 8, 16);
		ctx_tmp.putImageData(arm_left, 0, 8, 0, 0, 4, 16);
		ctx_tmp.putImageData(flipImage(arm_right), 12, 8, 0, 0, 4, 16);
		ctx_tmp.putImageData(leg_left, 4, 20, 0, 0, 4, 16);
		ctx_tmp.putImageData(flipImage(leg_right), 8, 20, 0, 0, 4, 16);

		resizeImage(ctx_tmp.getImageData(0, 0, 16, 40), ctx_prev, (zoom !== undefined) ? zoom : 6);
	});
}

function allPixelsAreSameColor(image) {
	//remember the color of the first pixel
	var firstPixColor = [image.data[0], image.data[1], image.data[2], image.data[3]];

	for (var i = 0; i < image.data.length; i += 4) {
		if(image.data[i+0] != firstPixColor[0]
			|| image.data[i+1] != firstPixColor[1]
			|| image.data[i+2] != firstPixColor[2]
			|| image.data[i+3] != firstPixColor[3]) {

			return false;
		}
	}

	//if all pixels are the same color, this should be true
	return true;
}

function overlayArmor(head, armor) {
	if(!allPixelsAreSameColor(armor)) {
		for (var i = 0; i < head.data.length; i += 4) {
			if(armor.data[i+3] != 0) {
				head.data[i+0] = armor.data[i+0];
				head.data[i+1] = armor.data[i+1];
				head.data[i+2] = armor.data[i+2];
			}
		}
	}

	return head;
}

function flipImage(image) {
	var canvas = document.createElement("canvas");
	var ctx = canvas.getContext("2d");

	var imgWidth = image.width;
	var imgHeight = image.height;

	canvas.width = imgWidth;
	canvas.height = imgHeight;

	ctx.putImageData(image, 0, 0);

	var imageData = ctx.getImageData(0, 0, imgWidth, imgHeight);

	//traverse every row and flip the pixels
	for (i = 0; i < imageData.height; i++) {
		//we only need to do half of every row since we're flipping the halves
		for (j = 0; j < imageData.width / 2; j++) {
			var index = (i*4) * imageData.width + (j*4);
			var mirrorIndex = ((i+1)*4)*imageData.width-((j+1)*4);

			for (p = 0; p<4; p++) {
				var temp = imageData.data[index+p];
				imageData.data[index+p] = imageData.data[mirrorIndex+p];
				imageData.data[mirrorIndex+p] = temp;
			}
		}
	}

	return imageData;
}

function resizeImage(imgData, ctx, zoom) {
	// Draw the zoomed-up pixels to a different canvas context
	for (var x = 0; x < imgData.width; x++){
		for (var y = 0; y < imgData.height; y++){
			// Find the starting index in the one-dimensional image data
			var i = (y * imgData.width + x) * 4;
			var r = imgData.data[i+0];
			var g = imgData.data[i+1];
			var b = imgData.data[i+2];
			var a = imgData.data[i+3];

			ctx.fillStyle = "rgba(" + imgData.data[i+0] + "," + imgData.data[i+1] + "," + imgData.data[i+2] +"," + (imgData.data[i+3] /255) + ")";
			ctx.fillRect(x*zoom, y*zoom, zoom, zoom);
		}
	}
}