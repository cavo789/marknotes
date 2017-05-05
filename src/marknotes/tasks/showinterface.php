<?php
/**
* Get the main interface of the application
*
* @return string  html content
*/

namespace MarkNotes\Tasks;

defined('_MARKNOTES') or die('No direct access allowed');

class ShowInterface
{
    protected static $_Instance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$_Instance === null) {
            self::$_Instance = new ShowInterface();
        }

        return self::$_Instance;
    }

    public function run()
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (!$aeSettings->getShowTreeAllowed()) {
            echo $aeSettings->getText('show_tree_not_allowed', 'Access to this screen has been disallowed, sorry');
            return false;
        }

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();

        // Read the template
        $template = file_get_contents($aeSettings->getTemplateFile('screen'));

        if (strpos($template, '%ICONS%') !== false) {

            // Call plugins that are responsible to add icons to the treeview toolbar
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->loadPlugins('buttons', 'treeview');
            $buttons = '';
            $args = array(&$buttons);
            $aeEvents->trigger('add.buttons', $args);

            $template = str_replace('%ICONS%', $buttons, $template);
        }

        if (strpos($template, '<!--%LOGIN%-->') !== false) {
            // Get the login form
            $aeEvents = \MarkNotes\Events::getInstance();
            $aeEvents->loadPlugins('task', 'login');
            $form = '';
            $args = array(&$form);
            $aeEvents->trigger('get.form', $args);
            $template = str_replace('<!--%LOGIN%-->', $form, $template);
        }

        $html = $aeHTML->replaceVariables($template, '', null);

        // replace variables

        $html = str_replace('%EDT_SEARCH_PLACEHOLDER%', $aeSettings->getText('search_placeholder', 'Search...'), $html);
        $html = str_replace('%EDT_SEARCH_MAXLENGTH%', SEARCH_MAX_LENGTH, $html);

        // Initialize the global marknotes variable (defined in /templates/screen.php)
        $javascript =
        "marknotes.autoload=1;\n".
        "marknotes.message.allow_popup_please='".$aeSettings->getText('allow_popup_please', 'The new window has been blocked by your browser, please allow popups for your domain', true)."';\n".
        "marknotes.message.apply_filter='".$aeSettings->getText('apply_filter', 'Filtering to [%s]', true)."';\n".
        "marknotes.message.apply_filter_tag='".$aeSettings->getText('apply_filter_tag', 'Display notes containing this tag', true)."';\n".
        "marknotes.message.cancel='".$aeSettings->getText('cancel', 'Cancel', true)."';\n".
        "marknotes.message.display_that_note='".$aeSettings->getText('display_that_note', 'Display that note', true)."';\n".
        "marknotes.message.filesfound='".$aeSettings->getText('files_found', '"%s has been retrieved', true)."';\n".
        "marknotes.message.json_error='".$aeSettings->getText('json_error', 'The [%s] task has returned an invalid JSON result', true)."';\n".
        "marknotes.message.loading_tree='".$aeSettings->getText('loading_tree', 'Loading the list of notes, please wait...', true)."';\n".
        "marknotes.message.ok='".$aeSettings->getText('OK', 'Ok', true)."';\n".
        "marknotes.message.pleasewait='".$aeSettings->getText('please_wait', 'Please wait...', true)."';\n".
        "marknotes.message.search_no_result='".$aeSettings->getText('search_no_result', 'Sorry, the search is not successfull', true)."';\n".
        "marknotes.message.settings_clean_done='".$aeSettings->getText('settings_clean_done', 'The application\'s cache has been cleared', true)."';\n".
        "marknotes.message.tree_delete_file='".$aeSettings->getText('tree_delete_file', 'Delete the note [%s]', true)."';\n".
        "marknotes.message.tree_delete_folder='".$aeSettings->getText('tree_delete_folder', 'Delete the note [%s]', true)."';\n".
        "marknotes.message.tree_delete_file_confirm='".$aeSettings->getText('tree_delete_file_confirm', 'Are you really sure you want to delete the note [%s] ?', true)."';\n".
        "marknotes.message.tree_delete_folder_confirm='".$aeSettings->getText('tree_delete_folder_confirm', 'Are your really sure you want to kill everything in the folder [%s] and the folder itself ?', true)."';\n".
        "marknotes.message.tree_collapse='".$aeSettings->getText('tree_collapse', 'Collapse all', true)."';\n".
        "marknotes.message.tree_expand='".$aeSettings->getText('tree_expand', 'Expand all', true)."';\n".
        "marknotes.message.tree_rename='".$aeSettings->getText('tree_rename', 'Rename', true)."';\n".
        "marknotes.message.tree_new_folder='".$aeSettings->getText('tree_new_folder', 'Create a new folder', true)."';\n".
        "marknotes.message.tree_new_folder_name='".$aeSettings->getText('tree_new_folder_name', 'New folder', true)."';\n".
        "marknotes.message.tree_new_note='".$aeSettings->getText('tree_new_note', 'Create a new note', true)."';\n".
        "marknotes.message.tree_new_note_name='".$aeSettings->getText('tree_new_note_name', 'New note', true)."';\n".
        "marknotes.url='index.php';\n".
        "marknotes.settings.development=".($aeSettings->getDevMode()?1:0).";\n".
        "marknotes.settings.DS='".preg_quote(DS)."';\n".
        "marknotes.settings.locale='".$aeSettings->getLocale()."';\n".
        "marknotes.settings.search_max_width=".SEARCH_MAX_LENGTH.";\n".
        "marknotes.settings.use_localcache=".($aeSettings->getUseLocalCache()?1:0).";\n".
        "marknotes.treeview.defaultNode='".trim(str_replace("'", "\'", $aeSettings->getTreeviewDefaultNode('')))."';\n";

        $html = str_replace('<!--%MARKDOWN_GLOBAL_VARIABLES%-->', '<script type="text/javascript">'.$javascript.'</script>', $html);

        return $html;
    }
}
