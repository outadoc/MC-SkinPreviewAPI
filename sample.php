<?php

	require_once 'SkinPreview.class.php';

	// Disable error messages, since we're trying to send binary data
	error_reporting(0);

	// Tell the browser we're sending a PNG image
	header("Content-type: image/png");

	// Instantiate the renderer
	$renderer = new SkinRenderer(85);

	// Render the skin (with its path, type and desired side)
	$skin = $renderer->renderSkinFromPath('test/pre_1_8.png', 'steve', 'front');

	// Render as base 64 encoded data
	// $skin = $renderer->renderSkinBase64('test/pre_1_8.png', 'steve', 'front');

	// Display the image
	imagepng($skin);
