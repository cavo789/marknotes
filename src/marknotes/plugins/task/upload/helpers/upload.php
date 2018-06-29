<?php

namespace MarkNotes\Plugins\Task\Upload\Helpers;

defined('_MARKNOTES') or die('No direct access allowed');

class Upload
{
	/**
	 * The function detectMimeType() will try to retrieve
	 * the mimetype from the content but, to do this, some Apache
	 * modules should be loaded.
	 *
	 * If, none are loaded, try to retrieve the mime from
	 * the extension, less secure.
	 *
	 * @param  string $ext [description]
	 * @return string			[description]
	 */
	private function getFromExtension(string $ext) : string
	{
		$mime_types = array(
			// text
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'7z' => 'application/x-7z-compressed',
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'docx' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'pptx' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
		);

		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		} elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		} else {
			return 'application/octet-stream';
		}
	}

	/**
	 * Detect file MIME Type by local path
	 * Get in plugins/task/elf/libs/php/elFinder.class.php
	 *
	 * @param  string $filename
	 * @param  string $ext 			extension of the file
	 * @return string file MIME Type
	 * @author Naoki Sawada
	 */
	public function detectMimeType(string $filename, string $ext) : string {

		if (class_exists('finfo', false)) {
			$tmpFileInfo = explode(';', finfo_file(finfo_open(FILEINFO_MIME), __FILE__));
		} else {
			$tmpFileInfo = false;
		}

		$regexp = '/text\/x\-(php|c\+\+)/';

		if ($tmpFileInfo && preg_match($regexp, array_shift($tmpFileInfo))) {
			$type = 'finfo';
			$finfo = finfo_open(FILEINFO_MIME);
		} elseif (function_exists('mime_content_type')
				&& preg_match($regexp, array_shift(explode(';', mime_content_type(__FILE__))))) {
					$type = 'mime_content_type';
		} elseif (function_exists('getimagesize')) {
			$type = 'getimagesize';
		} else {
			$type = 'none';
		}

		$mime = '';

		if ($type === 'finfo') {
			$mime = finfo_file($finfo, $filename);
		} elseif ($type === 'mime_content_type') {
			$mime = mime_content_type($filename);
		} elseif ($type === 'getimagesize') {
			if ($img = getimagesize($filename)) {
				$mime = $img['mime'];
			}
		}

		if ($mime) {
			$mime = explode(';', $mime);
			$mime = trim($mime[0]);

			if (in_array($mime, array('application/x-empty', 'inode/x-empty'))) {
				// finfo return this mime for empty files
				$mime = 'text/plain';
			} elseif ($mime == 'application/x-zip') {
				// http://elrte.org/redmine/issues/163
				$mime = 'application/zip';
			}
		}

		if (!$mime) {
			$mime = self::getFromExtension($ext);
		}

		return $mime;
	}
}
