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
    protected static $hInstance = null;

    public function __construct()
    {
        return true;
    }

    public static function getInstance()
    {
        if (self::$hInstance === null) {
            self::$hInstance = new ShowInterface();
        }

        return self::$hInstance;
    }

    public function run()
    {
        $aeSettings = \MarkNotes\Settings::getInstance();

        if (!$aeSettings->getShowTreeAllowed()) {
            echo $aeSettings->getText('show_tree_not_allowed', 'Access to this screen has been disallowed, sorry');
            return false;
        }

        $aeEvents = \MarkNotes\Events::getInstance();
        $aeHTML = \MarkNotes\FileType\HTML::getInstance();
        $aeSession = \MarkNotes\Session::getInstance();

        // Read the template
        $template = file_get_contents($aeSettings->getTemplateFile('screen'));

        if (strpos($template, '%ICONS%') !== false) {

            // Call plugins that are responsible to add icons to the treeview toolbar
            $aeEvents->loadPlugins('buttons', 'treeview');
            $buttons = '';
            $args = array(&$buttons);
            $aeEvents->trigger('add.buttons', $args);

            // If all buttons are disabled, kill the toolbar button
            if (trim($buttons) === '') {
                $buttons = '<script>try { document.getElementById("toolbar-app").remove(); } catch (err) { }</script>';
            }

            $template = str_replace('%ICONS%', $buttons, $template);
        }

        if (strpos($template, '<!--%LOGIN%-->') !== false) {
            // Get the login form
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
        "marknotes.message.open_html='".$aeSettings->getText('open_html', 'Open in a new window', true)."';\n".
        "marknotes.message.pleasewait='".$aeSettings->getText('please_wait', 'Please wait...', true)."';\n".

        "marknotes.message.tree_collapse='".$aeSettings->getText('tree_collapse', 'Collapse all', true)."';\n".
        "marknotes.message.tree_expand='".$aeSettings->getText('tree_expand', 'Expand all', true)."';\n".
        "marknotes.url='index.php';\n".
        "marknotes.settings.authenticated=".($aeSession->get('authenticated', 0)?1:0).";\n".
        "marknotes.settings.development=".($aeSettings->getDevMode()?1:0).";\n".
        "marknotes.settings.DS='".preg_quote(DS)."';\n".
        "marknotes.settings.locale='".$aeSettings->getLocale()."';\n".
        "marknotes.treeview.defaultNode='".trim(str_replace("'", "\'", $aeSettings->getTreeviewDefaultNode('')))."';\n";

        $html = str_replace('<!--%MARKDOWN_GLOBAL_VARIABLES%-->', '<script type="text/javascript">'.$javascript.'</script>', $html);

        return $html;
    }
}
