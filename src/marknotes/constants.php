<?php

defined('_MARKNOTES') or die('No direct access allowed');

// -------------------------------------------------------------------------------------------------------------------------
//
// Can be overwritten in settings.json

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

// Default text, english
// Can be override : settings.json->languages->language_code (f.i. 'fr')
define('ERROR', 'Error');
define('FILE_NOT_FOUND', 'The file [%s] doesn\'t exists (anymore)');

// When images are too big, force a resize by css to a max-width of ...
// Can be override : settings.json->page->img_maxwidth (integer)
define('IMG_MAX_WIDTH', '800');

// Prefix to use to indicate a word as a tag
define('PREFIX_TAG', '§');

define('CHMOD_FOLDER', 0755);
define('CHMOD_FILE', 0644);

//
// -------------------------------------------------------------------------------------------------------------------------

// Max allowed size for the search string
define('SEARCH_MAX_LENGTH', 100);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('DEVMODE', '=== DEV MODE ENABLED ===');

// Libraries folders path
define('LIBS', dirname(__DIR__).DS.'libs'.DS);
define('TASKS', dirname(__DIR__).DS.'classes'.DS.'tasks'.DS);
define('TEMPLATES', dirname(__DIR__).DS.'templates'.DS);
