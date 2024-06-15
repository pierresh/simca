<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Simca examples</title>
	<style>
		svg {
			background-color: white;
		}
	</style>
</head>
<body>
	<?php
		use Symfony\Component\ErrorHandler\Debug;

		require __DIR__ . '/../vendor/autoload.php';

		Debug::enable();

		require __DIR__ . '/example.php';
	?>
</body>
</html>