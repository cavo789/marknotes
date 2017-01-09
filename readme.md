# Markdown v0 - December 2016 #

The idea behind this script is to display the list of all .md files of a specific folder and every subfolders.

The script is called `index.php` and just need to be put on a localhost website (refer to f.i. [WAMP](http://www.wampserver.com/)/[MAMP](https://www.mamp.info/en/) for learning how to create a local website)  

The output will look like this : 

<img src="https://github.com/cavo789/markdown/raw/master/docs/markdown/images/interface.png" width="680"/>

[Read more](https://github.com/cavo789/markdown/blob/master/docs/markdown/HowToUse.md)

## Links to other notes

Every note has a "clipboard" displayed at the top right.  By clicking on that button, the link to the note is stored in the clipboard.

So it's easy to create a link : edit a note and copy/paste there the link.

Info : the `note_link` CSS class will be added to the hyperlink so it's also easy to define how intern links should be displayed on screen.

<img src="https://github.com/cavo789/markdown/raw/master/docs/markdown/images/links_notes.png" width="680"/>

## Encrypt your note (or part of)

If you wish to encrypt something, just put that information in a `<encrypt>` tag.

For instance  `<encrypt>MyPassword</encrypt>`.

Save the note and **display it** once : the note will be rewritten and every informations that should be encrypted will becomes something like `<encrypt data-encrypt=true>A LONG ENCRYPTED INFO</encrypt>`.

**Warning** : First, edit the `settings.json` file and specify your password.  Set it in the `encryption->password`  note.   Be sure to never forget it, it won't be possible to decrypt information without that password.

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

*  `editor`  : full path to your local text editor; for instance Notepad on a Windows machine.  If that variable is initialized with a valid program, the `edit` icon will be displayed when seeing a note.
*  `folder`: by default, your notes should be located in a folder called 'docs'. You can change the name here.
*  `language` : your interface language.  Be sure that strings are translated in that language.  You can see that (or translate) in the `languages` node.
*  `optimisation` :
   *  `cache` : do you wan to use the navigator cache ?  If so, specify `1` here so that the needed meta will be added
*  `templates` :
   *  `screen` : you can change the template used by the Markdown script here.   Just specify a filename without extension (so f.i. `MyTemplate`).  You'll need to create that template : go to the `templates` folder, make a copy of the `screen.php` file and rename it to, f.i. `MyTemplate.php`.   Edit and set the template to match yours needs.
   *  `html` : same for `screen` but the `html` template is used when the script generate .html files.
*  `encryption` :
   *  `password` : your password for the encryption.  **Don't forget it.**  
   *  `method` : the method used for the encryption.
*  `export`
   *  `save_html` : define if .html files should be stored on disk or not.
*  `languages` :
   *  `en` : here, you'll find each strings used by the application, in english.  You can create a new language just by addind a node for it, for instance `it` for Italian.  Copy/Paste every strings and start the translation.
*  `pages` :
   *  `google_font` : specify here a Google font name like f.i. `Roboto` if you want to use such font.  Please note : no fonts are downloaded.  The script will just add a call to the font and will generate css to use the font for h1 till h6.  You can do more by editing the custom.css file
   *  `img_maxwidth` : if you wish to make sure that images can be displayed on screen and on printed documents, specify here a max width like f.i. `800` (pixels).   If images will be bigger, they'll be resized in css.

## Read more ##

A very nice tool for editing markdown files is [Typora](http://www.typora.io/#windows) (both for Windows or Mac) or [MarkDownPad 2](http://markdownpad.com/).    Typora is still in a beta version (end 2016) but is a really, really nice tool.  *Give it a try.*

Need help for writing in Markdown ? 

Learn the syntax, for French speaking :

* [http://blog.wax-o.com/2014/04/tutoriel-un-guide-pour-bien-commencer-avec-markdown/](http://blog.wax-o.com/2014/04/tutoriel-un-guide-pour-bien-commencer-avec-markdown/)
* [https://fr.wikipedia.org/wiki/Markdown](https://fr.wikipedia.org/wiki/Markdown)
* [https://michelf.ca/projets/php-markdown/syntaxe/](https://michelf.ca/projets/php-markdown/syntaxe/)
* [https://openclassrooms.com/courses/redigez-en-markdown](https://openclassrooms.com/courses/redigez-en-markdown)

## Credits ##

Christophe Avonture | [https://www.aesecure.com](https://www.aesecure.com) 

Thank you to 

* Brian Reavis & Contributors for the [Selectize.js](https://github.com/selectize/selectize.js) plugin
* Emanuil Rusev (erusev) for the [Parsedown](https://github.com/erusev/parsedown) class ([http://erusev.com](http://erusev.com))
* Stanislav Sopov (stassop) for the jQuery highlite plugin (https://github.com/stassop/highlite)
* Zero Rocha for the clipboard.js (https://clipboardjs.com/) plugin
* The [Bootstrap](https://github.com/twbs/bootstrap) and [jQuery](https://github.com/jquery/jquery) teams