<?php
/**
 * Add the Mermaid button into the toolbar of the editor
 */
namespace MarkNotes\Plugins\Buttons;

defined('_MARKNOTES') or die('No direct access allowed');

class MermaidButton extends \MarkNotes\Plugins\Button\Plugin
{
	protected static $me = __CLASS__;
	protected static $json_settings = 'plugins.editor.mermaid';
	protected static $json_linked = 'plugins.content.html.mermaid';
	protected static $json_options = '';
}
