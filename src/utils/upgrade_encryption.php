<?php
/**
 * Get the list of every .md files under /docs and if encrypted,
 * get the unencrypted version so we can encrypt them again with
 * lockbox (https://github.com/starekrow/lockbox)
 *
 * THIS SCRIPT CAN BE LONG AS SOON AS YOU'VE A LOT OF NOTES.
 * THERE IS NO AJAX OUTPUT LIKE F.I. A PROGRESS BAR SO, PLEASE,
 * JUST BE PATIENT.
 *
 * REMARK : THIS SCRIPT CAN ONLY RUNS WITH PHP 7.0 OR PHP 7.1 AND
 * ONLY THESE TWO VERSIONS. WON'T RUN WITH PHP 7.2+
 *
 * Files will first be saved into the /tmp folder before modifying
 * something to be sure that, in case of problem, we still have the
 * version before the change
 */

namespace MarkNotes;
define('_MARKNOTES', 1);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// Get the list of .md files in the documentation folder
function getFiles() : array
{
	$arrFiles = array();

	// Call the listfiles.get event and initialize $arrFiles
	$aeEvents = \MarkNotes\Events::getInstance();
	$args=array(&$arrFiles);
	$aeEvents->loadPlugins('task.listfiles.get');
	$aeEvents->trigger('task.listfiles.get::run', $args);

	return $args[0];
}

// Initialize marknotes and classes
function init()
{
	$dir = dirname(__DIR__).DS;

	include_once $dir.'marknotes/includes/debug_show_errors.php';

	include_once $dir.'marknotes/includes/initialize.php';
	$aeInitialize = new Includes\Initialize();

	// Get the webroot folder
	$webRoot=trim(dirname(dirname($_SERVER['SCRIPT_FILENAME'])), DS);
	$webRoot=str_replace('/', DS, $webRoot).DS;

	$aeInitialize->init($webRoot);
	$aeInitialize->setDocFolder();

	unset($aeInitialize);

	return;
}

// Make a backup of the file
function makeBackup(string $filename) : bool
{
	$aeFiles = \MarkNotes\Files::getInstance();
	$aeFolders = \MarkNotes\Folders::getInstance();
	$aeSettings = \MarkNotes\Settings::getInstance();

	// -----------------------------------------------
	// Make a backup of the file before doing anything
	$sTmpFolder = $aeSettings->getFolderTmp();
	$sDocs = dirname($aeSettings->getFolderDocs());
	$sTmpFile = str_replace($sDocs, $sTmpFolder, $filename);

	// Create the directoy in the temp folder
	$aeFolders->create(dirname($sTmpFile));

	$aeFiles = \MarkNotes\Files::getInstance();
	$content = $aeFiles->getContent($filename);

	return $aeFiles->rewrite($sTmpFile, $content);
}

// Process the file, unencrypt and encrypt it again with the new
// encryption method
function processFile(string $filename)
{
	$html = '';

	$aeSettings = \MarkNotes\Settings::getInstance();
	$docs = $aeSettings->getFolderDocs();

	$aeFiles = \MarkNotes\Files::getInstance();

	if (!$aeFiles->exists($filename)) {
		// Should be impossible, the file exists but, on PHP 7.0
		// and PHP 7.1, there is a problem with accentuated characters
		$filename = utf8_decode($filename);
	}

	$aeSession = \MarkNotes\Session::getInstance();
	$aeSession->set('task','task.edit.form');
	$aeSession->set('authenticated','1');

	$aeEvents = \MarkNotes\Events::getInstance();
	$aeEvents->loadPlugins('task.encrypt.encrypt');

	$params = array();
	$params['markdown'] = $aeFiles->getContent($filename);

	// Ask to keep encrypted data unencrypted
	$params['encryption'] = 0;

	$aeMD = \MarkNotes\FileType\Markdown::getInstance();
	$markdown = $aeMD->read($filename, $params);

	$pattern = preg_quote(ENCRYPT_MARKDOWN_TAG).
		// ([\\S\\n\\r\\s]*?)  : match any characters, included new lines
		'([\\S\\n\\r\\s]*?)'.
		preg_quote(ENCRYPT_MARKDOWN_TAG);

	if (preg_match_all('/'.$pattern.'/mi', $markdown, $matches)) {
		list($tag, $confidential) = $matches;

		// Create an URL to the .html version of the note
		$aeFunctions = \MarkNotes\Functions::getInstance();
		$url = dirname($aeFunctions->getCurrentURL());

		$aeSettings = \MarkNotes\Settings::getInstance();
		$docs = dirname($aeSettings->getFolderDocs());

		$aeFiles = \MarkNotes\Files::getInstance();
		$no_ext = str_replace('.md', '', utf8_encode($filename));

		$url = $url.str_replace($docs, '', $no_ext).'.html';
		$url = str_replace(DS, '/', $url);

		$aeFolders = \MarkNotes\Folders::getInstance();

		// Make a backup of the file before doing anything
		$bContinue = makeBackup($filename);

		// Only when the backup was successfull made
		if ($bContinue) {
			$html = '<li>Process <a href="'.$url.'" '.
				'target="_blank">'.utf8_encode($filename).'</a></li>';

			// Add the encrypt tag before the confidential content
			for ($i=0; $i<count($tag); $i++) {

				$info=array('data'=>$confidential[$i]);
				$args=array(&$info);

				// Call the encryption tool; method : encrypt
				$aeEvents->trigger('task.encrypt.encrypt::encrypt', $args);

				// And get the encrypted data
				$encrypted = $args[0]['data'];

				// Rebuild the note's content
				// data-mode="1" ==> encrypted with LockBox
				// data-mode="0" (or not mentionned) ==> encrypted
				//		by marknotes v1.x
				$markdown = str_replace($tag[$i],
					'<encrypt data-encrypt="true" data-mode="1">'.
					$encrypted.'</encrypt>',
					$markdown);
			}

			// The note is now encrypted with LockBox
			// and his content contains portions like :
			// Login	: <encrypt data-encrypt="true">XXXXXX</encrypt>
			// Password : <encrypt data-encrypt="true">YYYYYY</encrypt>
			$aeFiles->rewrite($filename, $markdown);

			// Unencrypt the note
			$aeEvents->loadPlugins('task.encrypt.unencrypt');

			$pattern = '/<encrypt[[:blank:]]*'.
				// match the presence of attributes like
				// data-encrypt="true" f.i.
				'([^>]*)'.
				'>'.
				// ([\\S\\n\\r\\s]*?) : match any characters,
				// included new lines
				'([\\S\\n\\r\\s]*?)'.
				'<\/encrypt>/';

			if (preg_match_all($pattern, $markdown, $matches)) {
				list($tag, $attributes, $confidential) = $matches;

				// Add the encrypt tag before the confidential content
				for ($i=0; $i<count($tag); $i++) {

					$info = array('data'=>$confidential[$i]);
					$args=array(&$info);

					// Call the encryption tool; method : encrypt
					$aeEvents->trigger('task.encrypt.unencrypt::unencrypt', $args);

					// And get the unencrypted data
					$unencrypted = $args[0]['data'];

					$markdown = str_replace($tag[$i],
						'<encrypt>'.$unencrypted.'</encrypt>',
						$markdown);
				} // for()

			} // if (preg_match_all())
		} else { // if ($bContinue)
			die('Backup of '.$filename.' has failed. The process is '.
				'stop to be sure to not break something');
		}
	} // if (preg_match_all())

	return $html;
}

function getHTML() : string
{
	$html='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'.
	 	'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">'.
		'<head>'.
		'<meta charset="utf-8" />'.
		'<meta http-equiv="Content-Type" content="text/html;'.
		'charset=UTF-8" />'.
		'<meta http-equiv="X-UA-Compatible" content="IE=edge" />'.
		'<meta name="viewport" content="width=device-width,'.
		'initial-scale=1" />'.
		'<link href="../libs/bootstrap/css/bootstrap.min.css" '.
		'rel="stylesheet" />'.
		'<link href="../libs/font-awesome/css/font-awesome.min.css" '.
		'rel="stylesheet" />'.
		'<title>Convert encryption</title>'.
		'</head>'.
		'<body class="container">'.
		'<h1 class="jumbotron">marknotes - Convert encryption</h1>'.
		'<strong>Notes have been saved before any changes in '.
		'the /tmp folder so if you need to retrieve them...</strong>'.
		'<div class="row">%CONTENT%</div>'.
		'</body>'.
		'<script src="../libs/jquery/jquery.min.js"></script>'.
		'<script src="../libs/bootstrap/js/bootstrap.min.js"></script>'.
		'</html>';
	return $html;
}

	if ((version_compare(PHP_VERSION, '7.0.0') < 0) ||
		(version_compare(PHP_VERSION, '7.2.0') >= 0)) {
		die('Sorry, we need to use PHP 7.0 or PHP 7.1, not lower '.
		'and not greater, only one of these two versions.');
	}

	// Initialize Marknotes
	init();

	// No timeout
	set_time_limit(0);

	$aeFiles = \MarkNotes\Files::getInstance();
	$aeSettings = \MarkNotes\Settings::getInstance();
	$aeSession = \MarkNotes\Session::getInstance();
	$aeSession->set('Allow_Deprecated_Code', 1);

	$arrSettings = $aeSettings->getPlugins('plugins.options.markdown.encrypt');

	// Only if we've a password for the encryption
	if (trim($arrSettings['password'])!=='') {
		// Get the webroot folder
		$root = $aeSettings->getFolderWebRoot();

		$content = '';

		// Get the list of .md files
		$arr = getFiles();

		if ($arr!==array()) {
			// and process them one by one
			foreach ($arr as $file) {
				// Make filename absolute and process the file
				$content .= processFile(str_replace('/', DS, $root.$file));
			} // foreach

			$content = '<h3>Processed files :</h3>'.
				'<ol>'.$content.'</ol>';
		} // if ($arr!==array())
	} // if (trim($arrSettings['password'])!=='')

	$aeSession->remove('Allow_Deprecated_Code');

	$content = str_replace('%CONTENT%', $content, getHTML());

	echo $content;
