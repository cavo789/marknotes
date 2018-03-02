<?php

/**
 * Definition of a few constants. Almost all informations can be override in the settings.json file
 */

defined('_MARKNOTES') or die('No direct access allowed');

// Application name
define('APP_NAME', 'Notes management');

// Folder in this application where .md files are stored
// Can be override : settings.json->folder (string, name of the folder)
define('DOC_FOLDER', 'docs');

// Does a HTML version of the visualized note should be stored on the disk ?
// Can be override : settings.json->export->save_html (boolean, 0 or 1)
define('OUTPUT_HTML', true);

// Can be override : settings.json->editor
define('EDITOR', false); // enable online editing or not

define('DEFAULT_LANGUAGE', 'en');

define('DEFAULT_TIMEZONE', 'Europe/London');

define('DEBUG_LOG_NAME', 'debug.log');

// When the filename is long, very long, the width of the
// treeview can become really large (if set to auto).
// Here, we're defining the max length and if a filename has
// a greater size, the name will be truncated and followed by
// three dots.
define('TREEVIEW_MAX_FILENAME_LENGTH', 30);

// Default text, english
// Can be override : settings.json->languages->language_code (f.i. 'fr')
define('ERROR', 'Error');

// When images are too big, force a resize by css to a max-width of ...
// Can be override : settings.json->page->img_maxwidth (integer)
define('IMG_MAX_WIDTH', '800');

define('CHMOD_FOLDER', 0755);
define('CHMOD_FILE', 0644);

// Max allowed size for the search string
define('SEARCH_MAX_LENGTH', 100);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

// Will be overwritten in settings.json
// This value will only be used when there is immediatly
// an error like an incorrect version of PHP
define('GITHUB_REPO', 'https://github.com/cavo789/marknotes');

// Libraries folders path
define('LIBS', dirname(__DIR__).DS.'libs'.DS);
define('TASKS', dirname(__DIR__).DS.'classes'.DS.'tasks'.DS);
define('TEMPLATES', dirname(__DIR__).DS.'templates'.DS);

// Use when accessing to files / folders
define('FILE_IS_READONLY', -6);
define('FILE_NOT_FOUND', -5);
define('FOLDER_NOT_DELETED', -202);
define('FOLDER_IS_READONLY', -201);
define('FOLDER_NOT_FOUND', -200);
define('NO_ACCESS', -2);
define('ALREADY_EXISTS', -1);
define('FILE_ERROR', 0);
define('CREATE_SUCCESS', 1);
define('RENAME_SUCCESS', 2);
define('KILL_SUCCESS', 3);

// Force LF and not PHP_EOL when writting files on the filesystem
defined('PHP_LF') or define('PHP_LF', "\n");

// When the developper mode is set, in a few cases, a sentence will be outputted
// That sentence will start with the text below
// Used in the include, hierarchy and TOC plugin f.i.
// (Don't use the minus sign !!!)
define('DEV_MODE_PREFIX', 'MN_DEV_MODE | ');

// Each line outputted in the debug.log file will respect this template
// Unless override in settings.json debug->logfile->template
// @link https://github.com/Seldaek/monolog/blob/master/doc/message-structure.md
define('DEBUG_TEMPLATE', '[%datetime%] [%level_name%] %message% %context%');

// Where, in settings.json, to find informations about the ACLs plugin
define('JSON_OPTIONS_ACLS', 'plugins.options.task.acls');

// Where, in settings.json, to find informations about the encryption plugin
define('JSON_OPTIONS_ENCRYPT', 'plugins.options.markdown.encrypt');

// The encrypt markdown plugin will use this tag to inform the other
// content plugins (like the encrypt html plugin) that the portion between
// this tag is an encrypted one.
define('ENCRYPT_MARKDOWN_TAG', '$$@#@#@$$');

// Where, in settings.json, to find informations about the login plugin
define('JSON_OPTIONS_LOGIN', 'plugins.options.task.login');

// ... where are defined options for pandoc
define('JSON_OPTIONS_PANDOC', 'plugins.options.task.export.pandoc');

// ... where are defined options for font-awesome
define('JSON_OPTIONS_FONT_AWESOME', 'plugins.options.content.html.font-awesome');

// ... where are defined options for microdata
define('JSON_OPTIONS_MICRODATA', 'plugins.options.content.html.microdata');

// ... where are defined options for optimizations
define('JSON_OPTIONS_OPTIMIZE', 'plugins.options.task.optimize');

// ... where are defined options for the cache
define('JSON_OPTIONS_CACHE', 'plugins.options.task.optimize.cache');

// ... where are defined options for tags
define('JSON_OPTIONS_TAGS', 'plugins.options.content.html.tags');

// regex contains special regex like the code to put in
// a .md file for insterting a new slide / page break
define('JSON_OPTIONS_REGEX', '/regex');
