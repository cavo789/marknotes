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

        if (!class_exists('Debug')) {
            include_once dirname(dirname(__FILE__)).'/debug.php';
        }

        $aeDebug = \MarkNotes\Debug::getInstance();
        $aeFunctions = \MarkNotes\Functions::getInstance();
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();

        $template = file_get_contents($aeSettings->getTemplateFile('screen'));
        $html = $aeHTML->replaceVariables($template, '', null);

        // replace variables

        $html = str_replace('%EDT_SEARCH_PLACEHOLDER%', $aeSettings->getText('search_placeholder', 'Search...'), $html);
        $html = str_replace('%EDT_SEARCH_MAXLENGTH%', $aeSettings->getSearchMaxLength(), $html);

        $html = str_replace('%LOGINFORM%', $aeSettings->getText('loginform', 'Login form'), $html);
        $html = str_replace('%LOGIN%', $aeSettings->getText('login', 'Username'), $html);
        $html = str_replace('%PASSWORD%', $aeSettings->getText('password', 'Password'), $html);
        $html = str_replace('%SIGNIN%', $aeSettings->getText('signin', 'Sign in'), $html);
        $html = str_replace('%SITEMAP%', $aeSettings->getText('sitemap', 'Get the sitemap'), $html);
        $html = str_replace('%TIMELINE%', $aeSettings->getText('timeline', 'Display notes in a timeline view'), $html);
        $html = str_replace('%CLEAR_CACHE%', $aeSettings->getText('settings_clean', 'Clear cache'), $html);

        // Define the global markdown variable.  Used by the assets/js/marknotes.js script
        $javascript =
        "\nvar marknotes = {};\n".
        "marknotes.autoload=1;\n".
        "marknotes.message={};\n".
        "marknotes.message.allow_popup_please='".$aeSettings->getText('allow_popup_please', 'The new window has been blocked by your browser, please allow popups for your domain', true)."';\n".
        "marknotes.message.apply_filter='".$aeSettings->getText('apply_filter', 'Filtering to [%s]', true)."';\n".
        "marknotes.message.apply_filter_tag='".$aeSettings->getText('apply_filter_tag', 'Display notes containing this tag', true)."';\n".
        "marknotes.message.button_encrypt='".$aeSettings->getText('button_encrypt', 'Add encryption for the selection', true)."';\n".
        "marknotes.message.button_exit_edit_mode='".$aeSettings->getText('button_exit_edit_mode', 'Exit the editing mode', true)."';\n".
        "marknotes.message.button_save='".$aeSettings->getText('button_save', 'Submit your changes', true)."';\n".
        "marknotes.message.button_save_done='".$aeSettings->getText('button_save_done', 'The file has been successfully saved', true)."';\n".
        "marknotes.message.button_save_error='".$aeSettings->getText('button_save_error', 'There was an error while saving the file', true)."';\n".
        "marknotes.message.cancel='".$aeSettings->getText('cancel', 'Cancel', true)."';\n".
        "marknotes.message.copy_clipboard_done='".$aeSettings->getText('copy_clipboard_done', 'The note&#39;s content has been copied.  You can now paste the clipboard in your application.', true)."';\n".
        "marknotes.message.copy_link_done='".$aeSettings->getText('copy_link_done', 'The URL of this note has been copied into the clipboard', true)."';\n".
        "marknotes.message.display_that_note='".$aeSettings->getText('display_that_note', 'Display that note', true)."';\n".
        "marknotes.message.filesfound='".$aeSettings->getText('files_found', '"%s has been retrieved', true)."';\n".
        "marknotes.message.incorrect_login='".$aeSettings->getText('login_error', 'Incorrect login, please try again', true)."';\n".
        "marknotes.message.json_error='".$aeSettings->getText('json_error', 'The [%s] task has returned an invalid JSON result', true)."';\n".
        "marknotes.message.loading_tree='".$aeSettings->getText('loading_tree', 'Loading the list of notes, please wait...', true)."';\n".
        "marknotes.message.login_success='".$aeSettings->getText('login_success', 'Login successfull', true)."';\n".
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
        "marknotes.webroot='".rtrim($aeFunctions->getCurrentURL(true, false), '/')."/';\n".
        "marknotes.settings={};\n".
        "marknotes.settings.auto_tags='".$aeSettings->getTagsAutoSelect()."';\n".
        "marknotes.settings.debug=".($aeSettings->getDebugMode()?1:0).";\n".
        "marknotes.settings.development=".($aeSettings->getDevMode()?1:0).";\n".
        "marknotes.settings.DS='".preg_quote(DS)."';\n".
        "marknotes.settings.language='".$aeSettings->getLanguage()."';\n".
        "marknotes.settings.lazyload=".($aeSettings->getOptimisationLazyLoad()?1:0).";\n".
        "marknotes.settings.locale='".$aeSettings->getLocale()."';\n".
        "marknotes.settings.prefix_tag='".$aeSettings->getTagPrefix()."';\n".
        "marknotes.settings.search_max_width=".$aeSettings->getSearchMaxLength().";\n".
        "marknotes.settings.use_localcache=".($aeSettings->getUseLocalCache()?1:0).";\n".
        "marknotes.treeview={};\n".
        "marknotes.treeview.defaultNode='".trim(str_replace("'", "\'", $aeSettings->getTreeviewDefaultNode('')))."';\n";

        $html = str_replace('<!--%MARKDOWN_GLOBAL_VARIABLES%-->', '<script type="text/javascript">'.$javascript.'</script>', $html);

        // if present, add your custom stylesheet if the custom.css file is present. That file should be present in the root folder; not in /assets/js
        $html = str_replace('<!--%CUSTOM_CSS%-->', $aeFunctions->addStylesheet('custom.css'), $html);

        // Additionnal javascript, depends on user's settings
        $additionnalJS = '';
        if ($aeSettings->getOptimisationLazyLoad()) {
            $additionnalJS = '<script type="text/javascript" src="libs/lazysizes/lazysizes.min.js"></script> ';
        }

        $html = str_replace('<!--%ADDITIONNAL_JS%-->', $additionnalJS, $html);

        // if present, add your custom javascript if the custom.js file is present. That file should be present in the root folder; not in /assets/js
        $html = str_replace('<!--%CUSTOM_JS%-->', $aeFunctions->addJavascript('custom.js'), $html);

        return $html;
    }
}
