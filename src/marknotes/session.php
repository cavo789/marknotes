<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace MarkNotes;

defined('_MARKNOTES') or die('No direct access allowed');

class Session
{
	protected static $hInstance = null;

	public function __construct(string $folder = '')
	{
		self::init($folder);

		return true;
	}

	public static function getInstance(string $folder = '')
	{
		if (self::$hInstance === null) {
			self::$hInstance = new Session($folder);
		}

		return self::$hInstance;
	}

	private function init(string $folder = '')
	{
		$aeSettings = \MarkNotes\Settings::getInstance($folder);

		if (!isset($_SESSION)) {
			// Store session informations in the /tmp/sessions/ folder
			// Create that folder if needed

			$folder = $aeSettings->getFolderTmp().DS.'sessions'.DS;
			if (!is_dir($folder)) {
				mkdir($folder, CHMOD_FOLDER);
			}

			//session_save_path will cause a white page on a few hosting company.
			@session_save_path($folder);
			try {
				if (session_id() == '') {
					session_start();
				}
			} catch (Exception $e) {
				// On some hoster the path where to store session is incorrectly set and this gives a fatal error
			   // Handle this and use the /tmp folder in this case.
				@session_destroy();
				session_save_path(sys_get_temp_dir());
				session_start();
			} // try

			self::set('marknotes', 1);
		}

		$arr = $aeSettings->getPlugins(JSON_OPTIONS_LOGIN);
		$login = isset($arr['username']) ? trim($arr['username']) : '';
		$password = isset($arr['password']) ? trim($arr['password']) : '';

		// If both login and password are empty (will probably be the
		// case on a localhost server), consider the user
		// already authenticated
		if (($login === '') && ($password === '')) {
			self::set('authenticated', 1);
		}

		return;
	}

   /**
	* Kill a session.
	*
	*/
	public function destroy()
	{
		session_destroy();
	}

   /**
	* Add a property in the Session object
	* @param type $name
	* @param type $value
	*/
	public function set(string $name, $value)
	{
		$_SESSION['MN_'.$name] = $value;
		return true;
	}

   /**
	* Return the $_SESSION object (when $value is set on null) or return a
	* specific property (when $value is initialized)
	* Return always null when the $_SESSION object doesn't exists yet or when
	* the $value is not found
	*
	* @param type $name
	* @param type $default
	* @return type
	*/
	public function get(string $name = null, $default = null)
	{
		$return = isset($_SESSION) ? ($name == null ? $_SESSION : (isset($_SESSION['MN_'.$name])?$_SESSION['MN_'.$name]:$default)) : null;
		return $return;
	}

	/**
	* The session has a timeout property.   By calling the extend() method,
	* the session timeout will be reset to the current time() and therefore,
	* his lifetime will be prolongated.
	*/
	public function extend()
	{
		session_regenerate_id();
		self::set('timeout', time());
		return;
	}
}
