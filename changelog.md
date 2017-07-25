# Changelog - marknotes

[https://github.com/cavo789/marknotes](https://github.com/cavo789/marknotes)

## 1.1

### 2017-07-25

```diff
+ Update of third parties libraries
+ Plugin List Of Files - new markdown plugin that will scan a folder and retrieve the list of files in it
+ Plugin Carrousel - Also for html rendering (and Reveal too)
+ Add support for the dynamic page 'index.html'
+ Migrate the clear cache button from core to a plugin
+ Migrate the remaining js script for the search functionnality to the search plugin
+ Plugin search improved
    + The highlight process when the note is displayed support now more than one keyword
    - Update the jquery-flexdatalist library, this solve an issue by removing keywords
    - Solve by searching in files with accentuated characters in their name	
+ The authentication plugin won't fired if the settings doens't contains a login/passord i.e. it's possible to immediatly edit notes without need to make a login (f.i. on a localhost)
+ Rewrite share plugin to use the same way to display functionnalities i.e. thanks to jquery-toolbar (need to first click on the "share" button and then choose a sharing feature)
+ Plugin gTranslate - Add a parameter in settings.json to be able to not load the plugin on localhost
+ Plugin ganalytics - Add a parameter in settings.json to be able to not load the plugin on localhost
+ The treeview options button won't appear anymore if all treeview plugins are disabled
+ Add an entry "Edit note" in the treeview contextual menu
+ Add the ACLs plugin : allow to defined one or more folder that will be hidden to unauthorized people
- Solve issue when creating, from the interface, folders/files with accentuated characters in their name
- Solve bug in settings.json override when the change was for a plugin (into a array like plugin->content->html). Now, json only contains key and no more arrays.
- The `folder` setting was incorrectly read; solved.
- localStorage :
    - solve an issue when the list was empty; in that case localStorage is bypass and the server is querying for that list.
    - invalidate store.js when adding / renaming / removing a note / folder
```

## 1.0.8

### 2017-04-17

```diff
+ Add content plugins : Google Analytics, Google Translate, JoliTypo (coded now as a plugin), Share on social networks and SEO
+ The "language" attribute in templates is now derived from the settings.json file (and no more "en" by default)
+ Add stylisation for tables in the html View
+ Autoclose the login popup after submitting and simulate click on the login button when pressing the Enter key on the password field
- Remove a bug with dompdf when rendering pdf layout
```

## 1.0.7

### 2017-04-15

```diff
+ Add Expand All / Collapse All options in the treeview menu
+ Add support of Pandoc on Windows system and allow to convert to .docx and .pdf with this converter
+ Add a login screen before being able to add, delete or edit notes, when the settings.json doesn't provide a login / password, no login will then be asked (usefull on a localhost system)
+ Improve performance by using the session object on the server side (and no more only localStorage on the client site)
+ Add deckTape support (on Windows server) (https://github.com/astefanutti/decktape)
+ Add custom settings for a given note or its parent folder (https://github.com/cavo789/marknotes/wiki/5.6-Settings-override)
+ Add the support of reveal.js and no more only remark.js
+ Code review (refactoring, new classes, reduce number of PHPMD warnings, ...)
+ Add a timeline task (?task=timeline) (based on [https://github.com/Albejr](https://github.com/Albejr)) (https://github.com/cavo789/marknotes/wiki/5.3-routing)
+ Add a dynamic sitemap (https://github.com/cavo789/marknotes/wiki/5.3-routing)
+ Add the support of jquery-toolbar (https://github.com/paulkinzett/toolbar)
+ Routeur add support to /a-note.pdf i.e. support the extension
+ Add new settings in settings.json.dist for the animation part
+ Add a menu toolbar in the treeview side and move there application's actions like clearing the cache
+ Add a button for the sitemap
+ Update libraries (folder libs)
```

## 1.0.6

### 2017-03-24

```diff
+ Add a router
   + accessing to http://localhost/notes/docs/Development/atom/Plugins.html will display the html rendering of the docs/Development/atom/Plugins.md page even if the .html file doesn't exists) (?format=html is the default format)
   + ?format=slides will display a slideshow version
   + ?format=pdf will download a PDF version
+ Add JolyTypo library (https://github.com/jolicode/JoliTypo) - Web Microtypography fixer
+ Files and folders starting with a dot (like .images) won't be listed anymore in the treeview
+ Add a new template : pdf. This way it's possible to customize the look&feel of the pdf version and, also, remove unneeded calls like the javascript files (unneeded for a PDF)
- Remove fatal error "Call to undefined function AeSecure\mcrypt_get_iv_size()", mcrypt_get_iv_size is a global function loaded by php and not part of the AeSecure namespace.
- Remove bug of not refreshing the Treeview after a folder/note creation (right-click on the treeview); use localStorage bypass.
- Remove a js warning when the tag contains special characters (use RegExp.quote)
```

## 1.0.5

### 2017-03-18

```diff
+ Add right clic on the treeview : allow to create new folder/note, remove folder/note or rename them
+ Refactoring of classes/markdown.php
+ Exporting task's logic into classes/tasks/ files
+ Refactoring of assets/markdown.js
+ Exporting jstree's logic into assets/js/jstree.js
+ Exporting fullscreen's logic into assets/js/fullscreen.js
+ store.js (https://github.com/marcuswestin/store.js) - Store informations in the client's navigator cache to speed up page display
+ Improve the search engine by using jsTree - Search plugin

## 1.0.4

### 2017-02-09

```diff
+ Dompdf - Use Dompdf for PDF generation
- Remove jsPDF
```

## 1.0.3

### 2017-02-07

```diff
+ jsTree - Open folder on the single click
+ Read version number and github link from package.json
```

## 1.0.2

### 2017-01-27

```diff
+ Add Gulp support to allow easier testing before submitting to git
```

## 1.0.1

### 2017-01-26

```diff
+ Include php-error (https://github.com/JosephLenton/PHP-Error) when the development is enabled (in settings.json)
+ Add additionnals error handling in javascript
```

### 2017-01-25

```diff
+ Add a fullscreen button (on/off)
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
