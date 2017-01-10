# Markdown v0 - December 2016

The idea behind this script is to display the list of all .md files of a specific folder and every subfolders.

The script is called `index.php` and just need to be put on a localhost website (refer to f.i. [WAMP](http://www.wampserver.com/)/[MAMP](https://www.mamp.info/en/) for learning how to create a local website)  

The output will look like this : 

<img src="https://github.com/cavo789/markdown/raw/master/docs/markdown/images/interface.png" width="680"/>

## Demo site

Please visit the demo site : [https://markdown.cavo789.com/](https://markdown.cavo789.com/)

## Installation

Just download the repository from GitHub here : [https://github.com/cavo789/markdown](https://github.com/cavo789/markdown).

Create a new localhost website (let's say `markdown`).

Uncompress the markdown.zip file to the "markdown" folder you've just created under your wwwroot.

In that folder, you'll file the `index.php` script, a `libs` folder and a `docs` one.  The `docs` folder is aimed to be the location of your markdown files.

## Folder "docs"

By default, the `index.php` script will scan the `docs` folder.  You can change this in the very first line of the source.

You can too, without changing anything, just create a symbolic links called "docs" but pointing to the folder where, already today, you're saving your .md files.

## Taking notes

Take a look on this `HowToUse.md` file : the syntax is the one of markdown (if you don't know this syntax, refer to the official GitHub guide : [https://guides.github.com/pdfs/markdown-cheatsheet-online.pdf](https://guides.github.com/pdfs/markdown-cheatsheet-online.pdf)).  You can also play online with [https://stackedit.io/](https://stackedit.io/).

Note that there are a lot of editors, free or commercial, to make life easier. At writing time, I'm using [MarkdownPad 2](http://markdownpad.com/) for Windows.

So, take a look on this `HowToUse.md` file : the syntax is standard except that I'm using an html `img` tag to display images.  Unless you made changes to the PHP script, please respect this : refers to local images that are stored in a subfolder called "images".  So, write your .md file in a folder (f.i. "`howtos`") and, if you need images, create a subfolder ("`howtos/images`") and put images there.   Just like you can see it here, in this file.

## Using the script

So, you've create a localhost website and you've call it "`markdown`".  To use the script, just start your local webserver and go to your `http://localhost/markdown` website.

If you've correctly configured your webserver and alias, you'll should see the interface.  Just after the installation of the script, you'll see something like this :

<img src="https://github.com/cavo789/markdown/raw/master/docs/markdown/images/howtouse.png" width="680"/>

-  In the left pane, you'll retrieve the list of .md files found in the /docs folder.
-  In the right pane, just after a click on a file, you'll get his HTML output (with image support).

### Left pane

The script will scan the /docs folder and will list every single .md files found there; in the root folder and in any subfolder.

The list will be displayed in a table : in the first column the folder structure and, in the second column, the name of the file.

By clicking onto the first column, you'll apply a filter : only files of that specific folder will be displayed.  You can also use the selection at the top of the first column.

By clicking on the second column, on a filename, that file will be displayed but not his markdown content but his html output, converted on the fly.  This conversion is done thanks the script `parsedown` of Emanuil Rusev ([http://erusev.com](http://erusev.com)).

### Right pane

By displaying the content, the script will automatically generate a .html file and store that file in the same folder of your .md file.

If you've modify something in your .md file, the .html file will be generated again (thanks to a md5 content comparaison).

The right pane use Bootstrap and contains jQuery lines of code for retrieving the first h1 of the .md content : that heading will be used to initialize the `<title>` of the page.

That preview pane also provide a links "`Open in a new window`" : click on that link and a new tab will be created on your browser and will display the .html file (no more the .md file).   "`Open in a new window`" will refers to the local file and no more the `index.php` script.

------

## Links to other notes

Every note has a "clipboard" displayed at the top right.  By clicking on that button, the link to the note is stored in the clipboard.

So it's easy to create a link : edit a note and copy/paste there the link.

Info : the `note_link` CSS class will be added to the hyperlink so it's also easy to define how intern links should be displayed on screen.

<img src="https://github.com/cavo789/markdown/raw/master/docs/markdown/images/links_notes.png"/>

## Print

If you wish to print your document, you don't need to open the file in a new tab, just press CTRL-P or click on the printer icon : thanks to special css style (`@media print`), only the right page and only the content will be sent to the printer.

The list of files and hyperlinks won't be printed. 

## Use your own style

Just rename the `custom.css.dist` file present in the root folder to `custom.css`.  

This done, just edit the `custom.css` and put your own styles there.  The `custom.css` file won't be overriden by taking a newer version of this script

## Settings.json

You'll find a `settings.json.dist` file in the application rootfolder.  To use that file, rename the file to `settings.json`.  Changes you'll made to the .json file won't be overwritte by getting a newer version of this script.

The `settings.json` allows you to define your own settings.

```json
{   
   "editor": "C:\\Windows\\System32\\notepad.exe",
   "folder":"docs",
   "language":"fr",
   "optimisation":{
      "cache":1
   },
   "templates":{
      "screen":"screen",
	  "html":"html"
   },
   "encryption": {
      "password":"",
      "method":"aes-256-ctr"
   },
   "export":{
      "save_html":1
   },
   "languages":{
      "en": {
         "apply_filter":"Filtering to [%s]",
         "confidential":"confidential",
         "edit_file":"Edit",
         "files_found":"%s has been retrieved",
         "is_encrypted":"This information is encrypted in the original file and decoded here for screen display", 	
         "open_html":"Open in a new window",	 		 
         "please_wait":"Please wait...",
         "search_no_result":"D&eacute;sol&eacute;, votre recherche n'a pas retourn&eacute; de r&eacute;sultat",
         "search_placeholder":"Sorry, the search is not successfull"
	  },
      "fr": {
         "apply_filter":"Limite la liste des notes à [%s]",
         "confidential":"confidentiel",
         "edit_file":"Éditer",
         "files_found":"%s fichiers ont été retrouvés",
         "is_encrypted":"Cette information, m&ecirc;me si elle est affich&eacute;e en clair, est crypt&eacute;e dans le fichier. Elle n'est pas donc pas accessible si on ouvre imm&eacute;diatement le fichier source.", 	
         "open_html":"Ouvrir dans une nouvelle fen&ecirc;tre",	 		 
         "please_wait":"Un peu de patience s&#x27;il vous pla&#xEE;t...",
         "search_no_result":"D&eacute;sol&eacute;, votre recherche n'a pas retourn&eacute; de r&eacute;sultat",
         "search_placeholder":"Tapez ici des mots-cléfs pour lancer une recherche"
	  }      
   },
   "page":{
      "google_font":"",
      "img_maxwidth":800
   }   
}
```

-  `editor`  : full path to your local text editor; for instance Notepad on a Windows machine.  If that variable is initialized with a valid program, the `edit` icon will be displayed when seeing a note.
-  `folder`: by default, your notes should be located in a folder called 'docs'. You can change the name here.
-  `language` : your interface language.  Be sure that strings are translated in that language.  You can see that (or translate) in the `languages` node.
-  `optimisation` :
   -  `cache` : do you wan to use the navigator cache ?  If so, specify `1` here so that the needed meta will be added
-  `templates` :
   -  `screen` : you can change the template used by the Markdown script here.   Just specify a filename without extension (so f.i. `MyTemplate`).  You'll need to create that template : go to the `templates` folder, make a copy of the `screen.php` file and rename it to, f.i. `MyTemplate.php`.   Edit and set the template to match yours needs.
   -  `html` : same for `screen` but the `html` template is used when the script generate .html files.
-  `encryption` :
   -  `password` : your password for the encryption.  **Don't forget it.**  
   -  `method` : the method used for the encryption.
-  `export`
   -  `save_html` : define if .html files should be stored on disk or not.
-  `languages` :
   -  `en` : here, you'll find each strings used by the application, in english.  You can create a new language just by addind a node for it, for instance `it` for Italian.  Copy/Paste every strings and start the translation.
-  `pages` :
   -  `google_font` : specify here a Google font name like f.i. `Roboto` if you want to use such font.  Please note : no fonts are downloaded.  The script will just add a call to the font and will generate css to use the font for h1 till h6.  You can do more by editing the custom.css file
   -  `img_maxwidth` : if you wish to make sure that images can be displayed on screen and on printed documents, specify here a max width like f.i. `800` (pixels).   If images will be bigger, they'll be resized in css.​

## Don't share your confidential data

>  Are you saving your notes on a Git service or on a cloud ?   Then it's a very (**very!**) bad idea to type sensitive informations in your notes...

... except if they are encrypted before.

This `markdown.php` script support a custom `<encrypt>` tag : by typing your notes, instead of just typing a password f.i. type this : `<encrypt>password</encrypt>`.   Save the note and display it through `markdown.php`.   The script will detect that the information isn't encrypted and will crypt it by using a SSL encryption method.  Then, the information will be stored again in your .md file like this : `<encrypt data-encrypt="true">very_encrypted_password</encrypt>`

**But first, you'll need to mention your password : rename the file settings.json.dist to settings.json and edit that file.  Locate the `"password"` node and type your password there.**   The password won't be asked again, you just need to put it there.  If you're synchronizing your notes on a cloud, once again, don't store the settings.json file.  Keep that file secret.

## Improve and share

>  Don't be afraid to propose improvements, for sure, a lot of things (like the graphical interface) can be done better.

The current script has been coded quickly, in a few hours, for helping me to works more efficiently with my .md files.  

------

## Editing tool

A very nice tool for editing markdown files is [Typora](http://www.typora.io/#windows) (both for Windows or Mac) or [MarkDownPad 2](http://markdownpad.com/).    Typora is still in a beta version (end 2016) but is a really, really nice tool.  *Give it a try.*

Need help for writing in Markdown ? 

Learn the syntax, for French speaking :

-  [http://blog.wax-o.com/2014/04/tutoriel-un-guide-pour-bien-commencer-avec-markdown/](http://blog.wax-o.com/2014/04/tutoriel-un-guide-pour-bien-commencer-avec-markdown/)
-  [https://fr.wikipedia.org/wiki/Markdown](https://fr.wikipedia.org/wiki/Markdown)
-  [https://michelf.ca/projets/php-markdown/syntaxe/](https://michelf.ca/projets/php-markdown/syntaxe/)
-  [https://openclassrooms.com/courses/redigez-en-markdown](https://openclassrooms.com/courses/redigez-en-markdown)

## Credits

Christophe Avonture | [https://www.aesecure.com](https://www.aesecure.com) 

Thank you to 

-  Emanuil Rusev (erusev) for the [Parsedown](https://github.com/erusev/parsedown) class ([http://erusev.com](http://erusev.com))
-  Sérgio Dinis Lopes for the [jQuery Flexdatalist](https://github.com/sergiodlopes/jquery-flexdatalist) plugin
-  Stanislav Sopov (stassop) for the jQuery highlite plugin (https://github.com/stassop/highlite)
-  Zero Rocha for the clipboard.js (https://clipboardjs.com/) plugin
-  The [Bootstrap](https://github.com/twbs/bootstrap) and [jQuery](https://github.com/jquery/jquery) teams