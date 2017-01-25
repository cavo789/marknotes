# Markdown v1 - January 2017


![Build Status](https://travis-ci.org/cavo789/markdown.svg?branch=master) ![License MIT](https://markdown.cavo789.com/docs/license.png) ![PHP 7 ready](http://php7ready.timesplinter.ch/cavo789/markdown/master/badge.svg)

## Table of contents

- [Introduction](#introduction)
- [Demo](#demo)
- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Structure](#structure)
- [Saving your own notes](#docs)
- [Taking notes](#taking-notes)
- [Using the program](#using-the-program)
- [Toolbar](#toolbar)
  - [Fullscreen](#toolbar-fullscreen)
  - [Refresh](#toolbar-refresh)
  - [Copy note's content in the clipboard](#toolbar-clipboard)
  - [Print](#toolbar-print)
  - [Export as pdf](#toolbar-export-pdf)
  - [Copy link](#toolbar-copy)
  - [Slideshow](#toolbar-slideshow)
  - [Open in a new window](#toolbar-window)
  - [Edit](#toolbar-edit)
- [Customize to fit your needs](#customize)
- [Define your preferences](#settings)
- [Define your tags](#tags)
- [Use your own templates](#templates)
- [Use your own css and javascript](#custom)
- [Encrypt your private data](#encrypt)
- [Contributing](#contributing)
- [Credits](#credits)

## <a name="introduction"></a>Introduction
Do you've notes on your computer or in a cloud application ?   During years, I've used *Evernote* and was happy with.  But, in 2016, the free version was more restrictive than ever (only two devices were allowed) and by the end of the year, an announcement was made : a few (how many?) employees of Evernote will be able to read customer's notes.   With this new changes, I've taken my decision : my notes are my own property and I don't want to other people can access to them. Evernote is a commercial entreprise and it was time to migrate to an another system.  But which one ?

After having tried a few ones and because I'm a coder, my decision was quickly made : I'll create my own system and share it for free for anyone who can be interested.

My needs were :

* I wish a very **fast system** (no need to connect to an adminstration interface, just put a file in a folder and that's it),
* **Simplicity** (only one screen, no hidden feature),
* **Flexible** (notes are taken in the markdown language (files with `.md` as extension)),
* **Scalable** (i.e. being able to do more with the notes),
* **Opened** (don't want proprietary format, notes should be readable in a lot of programs,
* **Configurable** (by using a `settings.json`file, allow the user to define his own parameters),
* And, __thanks to the great Open Source community__, using existing libraries to do more with very few effort.

This current **Markdown** application was born.

## <a name="demo"></a>Demo

Do you want to see a live demo of the program ? 

You can find one here : [https://markdown.cavo789.com/](https://markdown.cavo789.com/)

## <a name="requirements"></a>Requirements

This is a web application, coded with PHP 7 so

* You'll need to a webserver (on your local machine or hosted on the internet)
* You'll need to configure your website for using PHP 7.0 at least.

*Why PHP 7 ?*  For security reasons and optimisation.  PHP 5.6 is dead since the 19 january 2017 and won't be supported anymore by the EOY 2018.   PHP 7 is available since end 2015 and it's time to code for that version.

## <a name="quick-start"></a>Quick Start

For clarity, this quick start is for a localhost system.  

The last version of the program can be found here : https://github.com/cavo789/markdown

1. Copy this repository (by downloading the ZIP archive f.i.) onto your computer,
2. Create f.i. a folder called `markdown` and unzip the archive there,
3. If needed, create an alias that will point to that folder (let's say `http://localhost/markdown`),
4. Be sure to enable PHP 7 for that site,
5. Access to `http://localhost/markdown` to check that configuration is OK : the application should display his interface.

*If everything goes fine, you'll see something like this (the screen and/or text can be a small different depending of the application's version)*

![After installation](https://markdown.cavo789.com/docs/after_installation.png)

### Possible problems
* if, by accessing the URL, the index.php file is being download and not displayed, this is because you're not using PHP 7 so, please force PHP 7 for that site
* if, the version was well PHP 7, edit the `.htaccess` that you'll find in the root folder and remove the first line i.e. `AddHandler application/x-httpd-php70 .php` 

## <a name="structure"></a>Structure

```
assets/      javascript, css and images of the application
classes/     php classes, for intern use (no access by URLs)
docs/        documentation, default folder where to put your notes
templates/   layouts for the screen, slideshow and output as vendor/      external libraries, open source ones
html page
```

In the root folder, you'll find, among others, 

```
.htaccess           Apache configuration file
custom.css          Your own css definition (see below)
custom.js           Your own javascript functions (see below)
index.php           entry point
settings.json.dist  Application's configuration file (see below)
```

## <a name="docs"></a>Saving your own notes

The `docs` folder is where to put your own notes.  A note is just a text file having the `.md` extension (***should have that extension!***)

> __Try it__ : with your file's explorer program, go to the /docs folder, create a subfolder and create there a new file having the .md extension.  You can create such file with any text editor program.  For instance, type the following line in that file, save and refresh your page.  You'll see it.
>
```markdown
# Hello world
```

The `docs` folder can be organized just like you want : create as many subfolders you want and organize your notes in that subfolders, just like you already do since years with files and folders on your computer.  Nothing to learn here.

Don't forget : notes should be taken by using the Markdown format and files should have .md file's extension.

By refreshing the interface, the index.php script of this application will scan the entire folder and display its content in a "treeview" as illustrated here below.  By clicking one a file, it's "hmtl" representation will be displayed in the right side

![Main interface](https://markdown.cavo789.com/docs/interface.png)

### Tips

* If you want to use an another folder, just edit the `settings.json` file (see below) and update the `folder` variable.
* You can also, without changing anything, kill the current `docs` folder and create a symbolic links called "docs" but pointing to an existing folder on your system where, already today, you're saving your .md files.  Under WIndows, you can create symbolic links easily thanks the `mklink` command (f.i. `mklink /D docs c:\documents`) ([read more on howtogeek.com](http://www.howtogeek.com/howto/16226/complete-guide-to-symbolic-links-symlinks-on-windows-or-linux/))

## <a name="taking-notes"></a>Taking notes

Take a look on this `readme.md` file : open this file with a text editor (like Notepad on Windows).   The file is "just" text, with a very few tags (like the presence of hash tags).

The advantage of Markdown is to be able to quickly type a note, that doens't need a client like a web browser to read it.  The Markdown language is self-describing and writing such file is fast : just type your text.

Markdown offers tags to be able to create headings (heading 1 till 6), quotes, bold, italic, ...  You can type lists, insert images, ... easily, almost naturally.

Writing in Markdown ask a very few learning.  If you don't know this syntax, refer to the official GitHub guide : [https://guides.github.com/pdfs/markdown-cheatsheet-online.pdf](https://guides.github.com/pdfs/markdown-cheatsheet-online.pdf)).  You can also play online with [https://stackedit.io/](https://stackedit.io/).

There are a lot of dedicated editors, free or commercial, to make life easier. At writing time, I'm using [Typora](https://typora.io/), before it, I've used [MarkdownPad 2](http://markdownpad.com/).

## <a name="using-the-program"></a>Using the program

Once you've install the program and add your own notes in the `docs`folder, go to  `http://localhost/markdown` and, if needed, refresh your screen.

You'll obtain a screen with two parts :

1. Your folder structure
2. Your note

### 1. Your folder structure 

The left part will of the screen is the `treeview` : you'll find there any subfolders where .md files have been found (*so, a folder with no .md file won't be mentionned*).  You can open a folder by just double-clicking on it, like you do everywhere else.

Then, apart of the folders, you'll find files.  Just click one on a file and the system will display its `html representation` at the right.

```
html representation = the note is well written in Markdown but, for displaying it in a nice way, a translation will be made 'on-the-fly' and a .html file will be generated.  Every Markdown tags (like # A title) will be translated into his html value (<h1>A title</h1>)
```
![Main interface](https://markdown.cavo789.com/docs/interface.png)

*[jsTree](https://github.com/vakata/jstree) is used here*

At the top of that left part, you'll find a search box. By typing a word, you'll be able to restrict the list in the treeview for notes that contains that word. 

Note : you can type several keywords.  The logic behind is a AND operator : notes should contains every keywords to be displayed.

*[jQuery Flexdatalist](https://github.com/sergiodlopes/jquery-flexdatalist)  is used for the search functionnality.*

### 2. Your content

Just click on a file from the left part and you'll see its content in the right part like illustrated here above.

In the top of the right part, you'll find a toolbar with a few icons.

*The HTML conversion is based on the [Parsedown](https://github.com/erusev/parsedown) class*

To make the note smarter and his use more comfortable, a few jQuery plugins will be automatically fired : 

* [DataTables](https://github.com/DataTables/DataTables) is used to transform html tables into dynamic table with sorting, filtering, pagination, ... feature,
* [jquery.highlight.js](http://bartaz.github.io/sandbox.js/jquery.highlight.html) is used to highlight the keyword used in the search engine,
* [lazysizes](https://github.com/aFarkas/lazysizes) will be used for optimization reasons : only images that are "near" the visible content will be loaded.  So, if you've a very long page with a lot of images, by displaying the first "page", only images that appears on that page and the second page will be loaded.  By scrolling down, images will be loaded only on that time (remark : lazysizes is disabled if the lazyload setting of settings.json is set to 0),
* [linkifyjs](https://github.com/SoapBox/linkifyjs) allow to convert plain text emails and URLs into clickable ones, 
* [Prism](http://prismjs.com) is used for syntax coloring (based on the language, Prism will put language's instruction in color),
* ... and a few own made javascript script will enhance the page like the "[tags](#tags)" script

## <a name="toolbar"></a>Toolbar

![Toolbar](https://markdown.cavo789.com/docs/toolbar.png)

### <a name="toolbar-fullscreen"></a>Fullscreen

Give the maximum width to your content : hide the list of files, hide the search engine and also every buttons except the fullscreen and refresh ones.

Your note's content will be displayed in fullscreen.

**Note : don't press the `escape` key on your keyboard to leave that mode but press the fullscreen button once more.**

### <a name="toolbar-refresh"></a>Refresh

Reload the note.  Make easier to reresh it's content if you've modified it outside the application.

### <a name="toolbar-clipboard"></a>Copy note's content in the clipboard

Copy the content of the note, formatting included, in the clipboard.  Making things easier if you wish to put that content in a mail f.i. : just press that button, go to your email editor program and press `CTRL-V` (Windows) / `Cmd-V` (Mac)

### <a name="toolbar-print"></a>Print

This feature will display a `print preview` rendering of the note.  That rendering is using a special css file (*you can found that file in the /assets/ folder*) for better print rendering.

Note : you can also just press `CTRL-P` / `Cmd-V`  shortcut, the interface is parametrized so that only the note will be printed and not the treeview or the toolbar f.i.; only the note's content.

*This preview is based on the [jquery-print-preview](https://github.com/etimbo/jquery-print-preview-plugin) plugin.*

### <a name="toolbar-export-pdf"></a>Export as pdf

Convert the content as a .pdf file.

Note : a better result can be achieved by using the "Print" feature of your browser and by printing to a PDF printer.

*This exportation is based on [jsPDF] (https://github.com/MrRio/jsPDF).*

### <a name="toolbar-copy"></a>Copy note link

Click on that button to copy the link to the currently displayed note in the clipboard.

You can then f.i. open a new tab and paste the URL to get a immediate link.

The aim here is to be able to create links between notes : 

* Display a note and copy the link i.e. just click on the `Copy note link` button
* Edit an another note and paste there the link (so just press `CTRL-V` / `Cmd-V`)
* Save the file and display it.   You see that by clicking on the link, you stay in the interface and the note is well opened "in the context".

You can create as many links you want; there is no restriction.

Info : the `note_link` CSS class will be added to the hyperlink so it's also easy to define how intern links should be displayed on screen.

*The copy link feature is using the [clipboard.js](https://github.com/zenorocha/clipboard.js) plugin.*

### <a name="toolbar-slideshow"></a>Slideshow

Open a new window and display the note just like if you've made a slideshow.

The note will be "translated" on-the-fly in slides : you'll have as many slides that you've headings in your file.

To get a new slide, just add a new title in your document.  A title, in the Markdown language, is created by starting a line with hash tags (from 1 till 6).

*The slideshow is based on [remark](https://github.com/gnab/remark)* 

### <a name="toolbar-window"></a>Open in a new window

Don't want to see the note within the interface ?  Just click on the "Open in a new window" to create a new tab and see a html representation of the note.

Everytime you'll make a change in the note, the "Open in a new window" feature will re-create the html file so you'll be sure to always have the latest version.

### <a name="toolbar-edit"></a>Edit

Need to make a few changes in the note's content ?  Click on the Edit button to get a very powerfull interface were you'll be able to update the text and save it on the server.

![Editing notes](https://markdown.cavo789.com/docs/edit_mode.png)

*This feature is based on the tremendous work of [simplemde-markdown-editor](https://github.com/NextStepWebs/simplemde-markdown-editor)*

## <a name="customize"></a>Customize to fit your needs

### <a name="settings"></a> Define your preferences

You'll find a `settings.json.dist` file in the application rootfolder. To be able to use your own settings, make a copy of that file and name the new file `settings.json`.

*Note : it's better to make a copy of the file and not just rename it*

*Changes you'll made to your `settings.json` file won't be overwritten by getting a newer version of this script but changes done into `settings.json.dist` well so it's a very bad idea to modify that file.*

For editing the `settings.json` file, be sure to use a text editor like Notepad++ (under Windows) i.e. a program that will edit text file without adding markups (like Word do f.i.).  And, second thing, be sure to **always** save the file with a UTF8-NoBom encoding.   In Notepad++, take a look on the Encoding menu to select the good one.

The file will looks like this : (january 2017)

```json
{
	"debug": 0,
	"development": 0,
	"editor": 0,
	"encryption": {
		"password": "",
		"method": "aes-256-ctr"
	},
	"export": {
		"save_html": 1
	},
	"folder": "docs",
	"language": "fr",
	"list": {
		"opened": 0,
		"auto_open": []
	},
	"optimisation": {
		"cache": 1,
		"lazyload": 1
	},
	"page": {
		"google_font": "",
		"img_maxwidth": 800
	},
	"tags": [],
	"templates": {
		"screen": "screen",
		"html": "html"
	},
	"languages": {
		"en": {
			"apply_filter": "Filtering to [%s]",
			"apply_filter_tag": "Display notes containing this tag",
			"button_encrypt": "Add encryption for the selection",
			"button_exit_edit_mode": "Exit the editing mode",
			"button_save": "Submit your changes",
			"button_save_done": "The file has been successfully saved",
			"button_save_error": "There was an error while saving the file",
			"copy_link": "Copy the link to this note in the clipboard",
			"copy_link_done": "The URL of this note has been copied into the clipboard",
			"confidential": "confidential",
			"display_that_note": "Display that note",
			"edit_file": "Edit",
			"error": "Error",
			"export_pdf": "Export the note as a PDF document",
			"file_not_found": "The file [%s] doesn\\'t exists (anymore)",
			"files_found": "%s has been retrieved",
			"is_encrypted": "This information is encrypted in the original file and decoded here for screen display",
			"no_save_allowed": "Error, saving notes isn't allowed.",
			"open_html": "Open in a new window",
			"please_wait": "Please wait...",
			"print_preview": "Print preview",
			"search_no_result": "Sorry, the search is not successfull",
			"search_placeholder": "Search...",
			"slideshow": "slideshow"
		},
		"fr": {
			"apply_filter": "Limite la liste des notes à [%s]",
			"apply_filter_tag": "Affiche les notes reprennant ce mot",
			"button_encrypt": "Encrypte la s&eacute;lection",
			"button_exit_edit_mode": "Quitte le mode &eacute;dition",
			"button_save": "Enregistre vos modifications sur le serveur",
			"button_save_done": "Le fichier a &eacute;t&eacute; enregistr&eacute; avec succ&egrave;s",
			"button_save_error": "Une erreur a &eacute;t&eacute; rencontr&eacute;e lors de la sauvegarde",
			"copy_link": "Copier le lien vers cette note dans le presse-papier",
			"copy_link_done": "L'URL vers cette note a &eacute;t&eacute; copi&eacute;e dans le presse-papier.",
			"confidential": "confidentiel",
			"display_that_note": "Afficher cette note",
			"edit_file": "Éditer",
			"error": "Erreur",
			"export_pdf": "Exporter cette note au format pdf",
			"file_not_found": "Le fichier [%s] n\\'existe pas (ou plus)",
			"files_found": "%s fichiers ont été retrouvés",
			"is_encrypted": "Cette information, m&ecirc;me si elle est affich&eacute;e en clair, est crypt&eacute;e dans le fichier. Elle n'est pas donc pas accessible si on ouvre imm&eacute;diatement le fichier source.",
			"no_save_allowed": "Erreur, l'&eacute;dition des notes n'est pas autoris&eacute;e.",
			"open_html": "Ouvrir dans une nouvelle fen&ecirc;tre",
			"please_wait": "Un peu de patience s&#x27;il vous pla&#xEE;t...",
			"print_preview": "Aper&ccedil;u avant impression",
			"search_no_result": "D&eacute;sol&eacute;, votre recherche n'a pas retourn&eacute; de r&eacute;sultat",
			"search_placeholder": "Recherchez...",
			"slideshow": "Affiche le document au format 'pr&eacute;sentation'"
		}
	}
}
```

-  `debug` : 0 / 1.  Enable or not debuging i.e. get extra logging informations,
-  `development` : 0 / 1.  Enable or not the development mode.  **That mode can break the normal way of working of the application f.i. stop functionnalities before doing it (f.i. rewrite files).  Don't activate that mode unless you really know what you're doing.**   For coders only,
-  `editor`  : 0 / 1.  Allow or not editing through the interface.  When editor is set on 0, the edit icon won't be displayed,
-  `encryption` :
   -  `password` : your password for the encryption.  **Don't forget it ! There is no way to recoved it.**
   -  `method` : the method used for the encryption,
-  `export`
   -  `save_html` : define if .html files should be stored on disk or not.  The .html file is the html representation of your .md file and is stored in the same folder of the note,
-  `folder`: by default, your notes should be located in a folder called 'docs'. You can change the name here,
-  `language` : your interface language.  Be sure that strings are translated in that language.  You can see that (or translate) in the `languages` node.  So, if you wish the interface in French, because 'fr' is already available under languages, just type 'fr' here,
-  `list` :
   -  `opened` : 0 / 1.  By displaying the application interface, how the treeview at the left should be displayed ?  Every folders opened or only the first level ?  If you set `opened` to 1, every folders will be opened, to the depest level.  By putting 0, only the first level will be opended,
   -  `auto_open` : (optional, can stay empty) a list of folder name that should be automatically opened when `opened` is set to 0.  Just mention the relative folder name so s.i. 'folder1', 'folder2', 'folder3/subfolder1', ...  Relative : name compared to `folder` mentionned here above,.  *Note : you can type more than one,*
-  `optimisation` :
   -  `cache` : do you want to use the navigator cache ?  If so, specify `1` here so that the needed meta will be added,
   -  `lazyload`: do you wish to load images only when they are about to be displayed on the screen i.e. not every images during the load time of the page but only when needed,
-  `tags` : (optional) a list of tags that you want to automatically select when the page is displayed so, if you wish to immediatly select a tag by opening the application, just type the tag here. *Note : you can type more than one,*
-  `templates` :
   -  `screen` : you can change the template used by the Markdown script here.   Just specify a filename without extension (so f.i. MyTemplate).  You'll need to create that template : go to the templates folder, make a copy of the screen.php file and rename it to, f.i. MyTemplate.php.   Edit and set the template to match yours needs.
   -  `html` : same for `screen` but the `html` template is used when the script generate .html files,
-  `languages` :
   -  `en` : here, you'll find each strings used by the application, in english. 
   -  `fr` : the French translation.
   -  `xx` : add yours.   You can create a new language just by addind a node for it, for instance `it` for Italian.  Copy/Paste every strings and start the translation.  *Be sure that the encoding is correct i.e. you should escape every accentuated characters; to be sure, use an "html encoder" site and check your new .json file with f.i. jsonlint.com to be sure it's correctly encoded*, 
-  `pages` :
   -  `google_font` : specify here a Google font name like f.i. Roboto if you want to use such font.  Please note : no fonts are downloaded.  The script will just add a call to the font and will generate css to use the font for h1 till h6.  You can do more by editing the custom.css file
   -  `img_maxwidth` : if you wish to make sure that images can be displayed on screen and on printed documents, specify here a max width like f.i. 800 (pixels).   If images will be bigger, they'll be resized in css.

### <a name="tags"></a>Define your tags

The search engine at the top left of the screen will suggest entries based on :

- folder name : if you've created a structure like /private/home/invoice/2017 f.i., "private", "home", "invoice" and "2017" will be used as entries of the entry auto-completion since these terms are important for you
- tags : in your root folder, you'll found a tags.json file.  Edit that file and type your own tags i.e. important words for your work.  

Here a sample for a tags.json file : 

```json
[
   "Bootstrap",
   "Javascript",
   "Markdown",
   "Web development"
]
```

These keywords will be proposed in the search engine (for auto-completion) but, too, will be highlighted in the notes : by clicking on a tag, from a note, all notes with that tags will be selected in the treeview.

### <a name="templates"></a>Use your own templates

The look&feel of the main interface can be changed to fit your need : just go to the /templates folder and make a copy of the `screen.php`file.   Name that file f.i. `MyScreen.php`, then edit the settings.json file : go to the `templates` part and type your name (`MyScreen`) for the `screen` setting.

You can do the same for the html template i.e. the file used by the "Open in a new window" feature.

### <a name="custom"></a>User your own css and javasript

In the root folder, you'll found two files :  `custom.css.dist` fand `custom.js.dist`.

These files are foreseen to allow you to add your own css or javascript instructions.

Just rename `custom.css.dist` in `custom.css` and from now, you custom.css will be loaded by the interface like the last css file so your definition wil be taken instead of standard ones.

Rename `custom.js.dist` in `custom.js` and you'll be able to add your own script to the application.

Your files won't be overwritten by getting a newer version of this program.

## <a name="encrypt"></a>Encrypt your private data

>  Are you saving your notes on a Git service,  a cloud or your hosting company ?   Then it's a very (**VERY!**) bad idea to type sensitive informations in your notes...

Don't type, in plain text, password, FTP credentials, private information like your address, birthdate, ... in your file but encrypt them.

Encryption is really easy : just type your confidential info in a `encrypt` tag. 

Here a fictive example : login and password for the demo site.  The screen capture has been taken from the  [Typora](https://typora.io/) editing tool.  

The idea is thus : put between  `encrypt` tag any sensitive informations.

![Encrypt tag](https://markdown.cavo789.com/docs/encrypt.png)

Then, by displaying that note through the interface, every `encrypt` content will be processed and encrypted by using your password (see the [settings.json](#settings) encryption node)

The result will looks like : 

![Encrypt tag](https://markdown.cavo789.com/docs/encrypt_display.png)

The presence of the lock icon is an indicator that the displayed text **is not stored anymore** on the filesystem.

By opening back your note in a text editor, you'll see that any `encrypt` content has been encrypted.

![Encrypt tag](https://markdown.cavo789.com/docs/encrypt_note.png)

The note is now safe.

*Note : by editing the note through the Edit mode, just use the Encrypt button, it's easier.  If your site is using a SSL certificate, the data is never stored or transmitted in plain text.  By submitting the change, your browser is sending encrypted content (thanks to https), your webserver is receiving it, decrypt it and give it to the Markdown script who immediatly encrypt it before saving the note on the disk.  So, at no time, the sensitive information enclosed in `encrypt` tags is stored or transmitted in a non-secure way*

**But first, you'll need to mention your password : edit your settings.json file, locate the `encryption` node and type a value for the `password` variable.**   The password won't be asked again, you just need to put it there.  If you're synchronizing your notes on a cloud, once again, don't store the settings.json file.  Keep that file secret.

## <a name="contributing"></a>Contributing

> Don't be afraid to propose improvements, for sure, a lot of things (like the graphical interface) can be done better.

## <a name="credits"></a>Credits

Christophe Avonture | [https://www.aesecure.com](https://www.aesecure.com) 

### Thank you to

- Alexander Farkas for [lazysizes](https://github.com/aFarkas/lazysizes)
- Bartek Szopka for [jquery.highlight.js](http://bartaz.github.io/sandbox.js/jquery.highlight.html)
- Contributors of [DataTables](https://github.com/DataTables/DataTables)
- Contributors of [Prism](http://prismjs.com)
- Contributors of [remark](https://github.com/gnab/remark)
- Contributors of [simplemde-markdown-editor](https://github.com/NextStepWebs/simplemde-markdown-editor)
- Emanuil Rusev (erusev) for the [Parsedown](https://github.com/erusev/parsedown) class ([http://erusev.com](http://erusev.com))
- [Font Awesome](https://github.com/FortAwesome/Font-Awesome) contributors
- Ivan Bozhanov (vakata) for [jsTree](https://github.com/vakata/jstree)
- James Hall (MrRio) for [jsPDF] (https://github.com/MrRio/jsPDF)
- Nedim Arabaci for the [noty](https://github.com/needim/noty) plugin
- Sérgio Dinis Lopes for the [jQuery Flexdatalist](https://github.com/sergiodlopes/jquery-flexdatalist) plugin
- SoapBox Innovations for [linkifyjs](https://github.com/SoapBox/linkifyjs)
- Tim Connell for [jquery-print-preview](https://github.com/etimbo/jquery-print-preview-plugin) plugin
- Zero Rocha for the [clipboard.js](https://github.com/zenorocha/clipboard.js) plugin
- The [Bootstrap](https://github.com/twbs/bootstrap) and [jQuery](https://github.com/jquery/jquery) teams