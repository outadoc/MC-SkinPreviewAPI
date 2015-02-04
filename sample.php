<?php

	// Tell the browser we're sending a PNG image
	header("Content-type: image/png");

	// Instantiate the renderer
	$renderer = new SkinRenderer(85);

	// Render the skin (with its path, type and desired side)
	$skin = $skinprev->renderSkin('test/pre_1_8.png', 'steve', 'front');

	// Display the image
	imagepng($skin);
