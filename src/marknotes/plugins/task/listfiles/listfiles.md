# Task - listfiles

## task.listfiles.get

Don't return any visible output but initialize an array with the list of files that **the user can see** i.e. the get action won't display any protected folder as soon as the user can see it.

```php
$arrFiles = array();

// Call the listfiles.get event and initialize $arrFiles
$aeEvents = \MarkNotes\Events::getInstance();
$args=array(&$arrFiles);
$aeEvents->loadPlugins('task.listfiles.get');
$aeEvents->trigger('task.listfiles.get::run', $args);

$arrFiles = $args[0]['files'];

foreach ($arrFiles as $file) {
	echo "Dear visitor, the file " . $file . " is accessible to you</br>";
}
```

## task.listfiles.treeview
>index.php?task=task.listfiles.treeview

Return the `json` that will be used by the jsTree treeview.

```json
{
    "settings": {
        "root": "c:\\site\\marknotes\\docs\\"
    },
    "count": 7,
    "tree": {
        "id": "\\",
        "type": "folder",
        "icon": "folder",
        "text": "docs",
        "state": {
            "opened": 1,
            "disabled": 1
        },
        "data": {
            "url": "\/index.html"
        },
        "children": [
            {
				... (list of files and subfolders)
			}
		]
	}
}
```
