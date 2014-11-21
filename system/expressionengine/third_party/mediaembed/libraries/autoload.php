<?php

spl_autoload_register(function ($className) {
	// replace namespace separator with directory separator (prolly not required)
	$className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

	// get full name of file containing the required class
	$file = __DIR__ . DIRECTORY_SEPARATOR . "{$className}.php";

	// get file if it is readable
	if (is_readable($file))
	{
		require_once $file;
	}
});
