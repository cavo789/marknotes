# Changelog - Markdown

https://github.com/cavo789/markdown

## 1.0.1

### 2017-01-25

```diff
+ Add a copy the note's content in the clipboard, with formatting
+ Add a refresh button to reload the note more easily
+ Change the current "Copy note's link" icon to an anchor
- Remove a bug while searching in a note with encrypted data, simplify the function
```

### 2017-01-24

```diff
+ Implement composer for updating dependencies
+ Add the current version number in the bottom left of the screen with a link to the GitHub repository
```


## 1.0

### 2017-01-22

```diff
+ Code reorganization
+ The edit form show unencrypted infos to let the user to update them
+ add jsPDF for Javascript pdf exportation
+ finalization version 1.0
```
### 2017-01-21
```diff
+ Add Encrypt button in the editor
```

### 2017-01-20
```diff
+ Edit mode 
+ Sanitize filename
+ Add .htaccess security, no script execution in /docs
```

### 2017-01-19
```diff
+ Add settings->list->opened
+ First initialize the program by reading settings.json.dist file
+ Replace highlight.js by Prism (for language syntax highlighting)
- Remove tagging in the .html file, do it only "on-the-fly"
```

### 2017-01-18
```diff
+ Keep jsTree as compact as possible : after a search, close all nodes and show only ones with selected node
```

### 2017-01-17
```diff
+ Add "table" class to tables
+ Add jsTree
+ Add DataTables plugin
- Don't rewrite the .md file anymore for adding tags; do it only on-the-fly
```

### 2017-01-16
```diff
+ Add aeSecureJSON class for better JSON handling (error handling)
+ If translated string isn't in settings.json, use the one of settings.json.dist
+ Add images lazyload (see settings.json -> optimisation -> lazyload
```

### 2017-01-15
```diff
+ Add Debug and Development entries in settings.json
+ Replace editor by a boolean in settings.json
+ Auto tagging regex improved
```

### 2017-01-14
```diff
+ Add automatically known tags in markdown existing files
```

### 2017-01-13
```diff
+ Javascript improvements
+ CSS improvements
+ Add a Slideshow button to display the note like a slideshow
+ libs folder reorganization
+ add linkify.js to convert plain text email, urls, ... into clickable ones
+ search : add a auto keyword i.e. a filtering that is immediatly done when showing the application screen
- remove highlite and replace by jquery.highlight.js
```

### 2017-01-12
```diff
+ Tags : maintain automatically the tags.json file. Just need to put §Some_Tag in a document (une § and not #)
+ Tags : detect tags in JS and allow to click on it for filtering
+ Wallpaper image : only visible for large screen
```

### 2017-01-11
```diff
+ Search : allow to specify more than one term (boolean operator is AND)
+ Add highlight.js for syntax color
```

### 2017-01-10
```diff
+ Add Flexdatalist jquery
- Remove selective.js
```

### 2017-01-09
```diff
+ Add support of links toward another notes 
+ Add the copy the link in the clipboard feature
```

### 2017-01-06
```diff
+ Move assets from inline to /assets folder
+ Move classes into /classes folder
+ Move HTML inline to /templates folder
+ Add support for custom template (property template in settings.json)
```

### 2017-01-05
```diff
+ Add custom.js to allow the end user to add his own script
+ Add a IMG_MAX_WIDTH constant to be sure that images will be correctly resized if too big
+ Add selectize.js
```

### 2017-01-04
```diff
+ Add Print preview feature (thanks to https://github.com/etimbo/jquery-print-preview-plugin)
+ Remove icons (images) and use font-awesome instead
```

### 2017-01-03
```diff
+ Improve add icons (based on jQuery and no more pure css)
+ Add filtering on folder name : just click on a folder name and the list will be limited to that folder
+ Start editing code
+ Remove leading / ending spaces before searching
+ Add Google font support (node "page::google_font" in the settings.json file)
```

### 2016-12-30
```diff
+ Search supports encrypted data now
```

### 2016-12-21
```diff
+ Add search functionality, add comments, add custom.css, 
+ Add change a few css to try to make things clearer, force links (<a href="">) to be opened in a new tab
```

### 2016-12-19
```diff
+ Add support for encryption (tag <encrypt>)
```

### 2016-12-14
```diff
+ First version
```