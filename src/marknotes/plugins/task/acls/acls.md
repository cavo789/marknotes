# Task - acls

## task.acls.cansee

Don't return any visible output but initialize an array with a return True/False boolean to indicate if the user can see or not the folder / note.

```php
$aeEvents = \MarkNotes\Events::getInstance();
$aeEvents->loadPlugins('task.acls.cansee');

// Note : the folder should start and end with the slash
$arr = array(
	 'folder' => '/private_folder/',
	 'return' => true);

$args = array(&$arr);

$aeEvents->trigger('task.acls.cansee::run', $args);

echo 'The folder '.$args[0]['folder'].' is '.
	($args[0]['return']?'accessible':'prohibited');
```

## task.acls.filter_list

Don't return any visible output. Used by task.listfiles.get

```php
// List of .md files under the /docs folder
$arrFiles = $aeFunctions->array_iunique($aeFiles->rglob('*.md', $docs));

// And don't keep in that list notes not accessible to the connected user
$aeEvents->loadPlugins('task.acls.filter_list');
$args=array(&$arrFiles);
$aeEvents->trigger('task.acls.filter_list::run', $args);

// Retrieve the filtered array i.e. that Files
// well accessible to the current user
$arrFiles=$args[0];
```

## task.acls.load

Don't return any visible output but load the list of protected folder in a Session variable.
