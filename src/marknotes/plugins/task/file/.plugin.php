<?php

namespace MarkNotes\Plugins\Task;

defined('_MARKNOTES') or die('No direct access allowed');

require_once(dirname(dirname(__FILE__)).DS.'.plugin.php');

class File extends \MarkNotes\Plugins\Task\Plugin
{
	public static function run(&$params = null) : bool
	{
		return $this->run($params);
	}

	/**
	 * Convert the $arr parameter into a json object and return the string
	 */
	protected static function returnInfo(array $arr) : string
	{
		$aeJSON = \MarkNotes\JSON::getInstance();
		return $aeJSON->json_encode($arr);
	}

	/**
	 * Be sure that filenames doesn't already start with the /docs folder (otherwise will
	 * be mentionned twice)
	 *
	 * $params['oldname'] and $params['filename'] will contains absolute filenames
	 */
	protected static function cleanUp(array &$params = null) : bool
	{
		$aeSettings = \MarkNotes\Settings::getInstance();

		$docs = $aeSettings->getFolderDocs(false);

		// oldname is the actual file/folder name, before renaming it f.i.
		if (isset($params['oldname'])) {
			if (substr($params['oldname'], 0, strlen($docs)) == $docs) {
				$params['oldname'] = substr($params['oldname'], strlen($docs));
			}
			$params['oldname'] = $aeSettings->getFolderDocs(true).$params['oldname'];
		}

		// newname is the actual file/folder name, before renaming it f.i.
		if (isset($params['newname'])) {
			if (substr($params['newname'], 0, strlen($docs)) == $docs) {
				$params['newname'] = substr($params['newname'], strlen($docs));
			}
			$params['newname'] = $aeSettings->getFolderDocs(true).$params['newname'];
		}

		// filename is the new file/folder name or the name of the newly created file/folder
		if (isset($params['filename'])) {
			if (substr($params['filename'], 0, strlen($docs)) == $docs) {
				$params['filename'] = substr($params['filename'], strlen($docs));
			}
			$params['filename'] = $aeSettings->getFolderDocs(true).$params['filename'];
		}

		return true;
	}

	/**
	 * Determine if this plugin is needed or not
	 */
	final protected static function canRun() : bool
	{
		$bCanRun = parent::canRun();

		if ($bCanRun) {
			// Only when the user is connected, we will not provide operating system functions
			// like create / delete / rename files/folders if the user isn't connected.
			$aeSession = \MarkNotes\Session::getInstance();
			$bCanRun = ($aeSession->get('authenticated', 0) === 1);
		}

		return $bCanRun;
	}
}
