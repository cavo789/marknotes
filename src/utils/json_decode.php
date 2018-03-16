<?php
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

	$root = dirname(__DIR__);

	$file = $root.DS.'languages/marknotes-fr.json';

	if (is_file($file)) {
		$json = json_decode(file_get_contents($file), true);
		//header('Content-Type: application/json');
		
		// Show the French accents
		echo '<pre>'.print_r($json,true).'</pre>';
	} // if (is_file($file))
