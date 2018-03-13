<?php
// --- INITIALIZE MARKNOTES ---
//
require_once('../../initialize.php');

$arrMN = \MarkNotes\Plugins\Task\Elf\Initialize::getSettings();

if ((trim($arrMN['root'])==='') || (trim($arrMN['docs'])==='') || (trim($arrMN['url'])==='')) {
	die('FATAL ERROR - ELF configuration incorrect; marknotes '.
		'folders not initialized');
}
//
// --- INITIALIZE MARKNOTES ---
// ELF - Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
error_reporting(0); // Set E_ALL for debuging
// elFinder autoload
require './autoload.php';
// Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';
/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string	$attr	attribute name (read|write|locked|hidden)
 * @param  string	$path	absolute file path
 * @param  string	$data	value of volume option `accessControlData`
 * @param  object	$volume  elFinder volume driver object
 * @param  bool|null $isDir	path is directory (true: directory, false: file, null: unknown)
 * @param  string	$relpath file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume, $isDir, $relpath) {
	$basename = basename($path);
	return $basename[0] === '.'				  // if file/folder begins with '.' (dot)
			 && strlen($relpath) !== 1			// but with out volume root
		? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
		:  null;								 // else elFinder decide it itself
}
$opts = array(
	'debug' => $arrMN['debug'],
	'roots' => array(
		// Items volume
		array(
			// driver for accessing file system
			'driver' => 'LocalFileSystem',
			// path to the documentation folder of marknotes
			'path' => $arrMN['docs'],
			//'attributes' => array(),
			// path to the quarantine folder (in the temporary
			// folder of marknotes
			'quarantine' => $arrMN['root'].'tmp/.elf_quarantine',
			// path to the thumbnails temp folder (in the temporary
			// folder of marknotes
			'tmbPath' => $arrMN['root'].'tmp/.elf_thumbnails',
			'URL' => $arrMN['url'],
			// to make hash same to Linux one on windows too
			'winHashFix'	=> DIRECTORY_SEPARATOR !== '/',
			// All Mimetypes not allowed to upload
			'uploadDeny'	=> array('all'),
			// Mimetype `image` and `text/plain` allowed to upload
			// Allow .docx files too
			'uploadAllow'	=> array('image', 'text/plain', 'text/x-markdown', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
			// allowed Mimetype : the one specified here above
			'uploadOrder'	=> array('deny', 'allow'),
			// Set locale. Currently only UTF-8 locales are supported.
			'locale' => $arrMN['locale']
			// disable and hide dot starting files
			// Don't show files or folders like .files, .images, ...
			//'accessControl' => 'access'
		)
	)
);
// We can't never access to these folders :
$protected = '.git|.htaccess|.htpasswd|';
// $arrMN['acls'] is set by the Elf\Initialize::getSettings();
// function
if (isset($arrMN['acls'])) {
	$acls = json_decode($arrMN['acls'], true);
	// f.i. "christophe"
	$username = trim($arrMN['username']);
	foreach ($acls as $folder => $users) {
		// $users is an array and contains the list of
		// people who can access the folder. If the $username is
		// not in the array, then the user is not allowed to access
		// to the folder
		if (!in_array($username, $users)) {
			$protected .= $folder .'|';
		}
	} // foreach
	$protected = rtrim($protected, '|');
} // if (isset($arrMN['acls']))
if (trim($protected)!=='') {
	$arr=
		array(
			array(
				'pattern' => '/('.$protected.')/',
				'read'	=> false,
				'write'	=> false,
				'locked'  => true
			)
		);
	$opts['roots'][0]['attributes'] = $arr;
} // if (trim($protected)!=='')
// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();
