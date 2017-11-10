# Marknotes - Plugins

## Explanations

A plugin is a piece of code that implement (bind) a specific event.

For instance :

```php
public function bind(string $plugin) : bool
{
	$aeEvents = \MarkNotes\Events::getInstance();
	$aeEvents->bind('task.login', __CLASS__.'::run', $plugin);
	return true;
}
```

This example bind the task.login event and when that task will be fired by marknotes, the run() method of the plugin will then be called.

## Plugins

### buttons

#### content

#### page

##### treeview

Called once and only when the main page (the interface) is building.

Should implement the `add.buttons` event.

### content

The HTML rendering of the note is being build, plugins that will implement the `render.content` event will be able to interact with the HTML code of the note (once converted in HTML).

Should implement the `render.content` event.

Content plugins **CANNOT implement** `render.css` and `render.js` events since these events should be called only once (not one by note) and therefore should be put in `/plugins/page/html`

For instance :

```php
public static function doIt(&$html = null) : bool
{
	if (trim($html) === '') {
		return true;
	}

	// Do something with the content and update it with a newer content
	$html = str_replace("%A_TAG%", $value, $html);
}

public function bind(string $plugin) : bool
{
	$aeEvents = \MarkNotes\Events::getInstance();
	$aeEvents->bind('render.content', __CLASS__.'::run', $plugin);
	return true;
}
```

### markdown

Markdown plugins will be called when marknotes will read the .md file and before the HTML conversion.

The called event is `markdown.read`.

```php
public static function readMD(&$params = null)
{
	if (trim($params['markdown']) === '') {
		return true;
	}

	// ...

}

public function bind(string $plugin) : bool
{
	$aeEvents = \MarkNotes\Events::getInstance();
	$aeEvents->bind('markdown.read', __CLASS__.'::readMD', $plugin);
	return true;
}
```

### page

#### html

Called once, when the main page (the interface) is building and when a .html file is being generated.

Can implement one of the following :

* `render.css` : called by marknotes when the page is being built and when all .css are collected.

* `render.html` : the HTML of the page is being built and this event is called to allow the plugin to interact with the html source code.

* `render.js` : called by marknotes when the page is being built and when all .js are collected.

### task

Called when the action is something like `task.listFiles`, `task.login`, `task.logout`, ...

The plugin should implement that specific event (f.i. `task.login`)

The php file can be placed in a subdirectory so, the two approachs below will works :

* The logic is quite simple (f.i. `task.login`), you can place the .php file directly under the `/plugins/task` folder so `/plugins/task/login.php` and implement the `task.login` event.
* The logic is more complex or you want to group tasks so you can place the .php file in its "own" folder. Consider tasks like `task.export.html`, `task.export.pdf`, ... : it can be more efficient to put them in a `/plugins/task/export` subfolder and, there, place `html.php`, `pdf.php`, ... scripts.

#### export

This folder contains files and the `after` and `before` subfolders.

The script files like f.i. `html.php` will be called when the task will be `task.export.html` i.e. the name of the script will be the format asked by the task.

If you want the react to `task.export.csv`, just create `/plugins/task/export/csv.php` in that case and bind the `run` event.

##### after

The plugin should implement the `run` event and only that one.

Will be called by Marknotes when an exportation **has been** done (so after) like f.i. `export.html`.

##### before

The plugin should implement the `run` event and only that one.

Will be called by Marknotes when an exportation **is being** done (so before) like f.i. `export.html`.
