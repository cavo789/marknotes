# Markdown - Templates

The settings.json file contains a node named `templates` with two entries : `screen` and `html`.

The `screen` template is used for the application itself : how the interface should looks like.  

The `html` template is used by the application for the generation of `.html` files.  This is done every time you click on a note : the note is stored on the disk.  The template used for this is defined here.

## Use your own

Just take a copy of the template (f.i.`templates/screen.php`), rename it (f.i. `myTemplate.php`) and specify the filename (without extension) in the settings.json file.

Edit settings.json, locate the `templates` node and replace the desired template name.  For instance, replace

```json
   "templates": {
      "screen": "screen",   
```

by

```json
   "templates": {
      "screen": "myTemplate",   
```

*Please note : the template's name can only contains letters (a-z and A-Z), figures (0-9) and three special characters : the dot, the minus or the underscore sign.*
