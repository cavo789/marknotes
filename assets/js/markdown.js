
/* global markdown, custominiFiles, customafterDisplay, customafterSearch */

/*  Allow to easily access to querystring parameter like alert(QueryString.ParamName); */
var QueryString = function () {
  // This function is anonymous, is executed immediately and 
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  } 
  return query_string;
}();

// http://stackoverflow.com/a/11247412
// Check if an array contains a specific value
Array.prototype.contains = function(v) {
   for(var i = 0; i < this.length; i++) {
      if(this[i] === v) return true;
   }
   return false;
};
// Extract unique values of an array
Array.prototype.unique = function() {
   var arr = [];
   for(var i = 0; i < this.length; i++) {
      if(!arr.contains(this[i])) {
          arr.push(this[i]);
      }
   }
   return arr; 
}

// http://stackoverflow.com/a/2593661/1065340
RegExp.quote = function(str) {
    return (str+'').replace(/[.?*+^$[\]\\(){}|-]/g, "\\$&");
};

$(document).ready(function() {

   // On page entry, get the list of .md files on the server
   ajaxify({task:'listFiles',callback:'initFiles(data)'});

   // Size correctly depending on screen resolution
   $('#TDM').css('max-height', $(window).height()-10);
   $('#TDM').css('min-height', $(window).height()-10);

   // Maximise the width of the table of contents i.e. the array with the list of files
   $('#TOC').css('width', $('#TDM').width()-5);
   $('#search').css('width', $('#TDM').width()-5);
   
   $('#CONTENT').css('max-height', $(window).height()-10);
   $('#CONTENT').css('min-height', $(window).height()-10);
   $('#CONTENT').css('width', $('#CONTENT').width()-5);

   // When the user will exit the field, call the onChangeSearch function to fire the search
   $('#search').change(function(e) { onChangeSearch(); } );

}); // $( document ).ready()

/**
 * Run an ajax query
 * @param {json} $params
 *      task = which task should be fired
 *      param = (optional) parameter to provide for the calling task
 *      callback = (optional) Function to call once the ajax call is successfully done
 * 
 * @returns {undefined}
 */
function ajaxify($params) { 

   var $data = new Object;
   $data.task  = (($params.task==='undefined')?'':$params.task);
   $data.param = (($params.param==='undefined')?'':$params.param);

   var $target='#'+(($params.target==='undefined')?'TDM':$params.target);

   $.ajax({
      beforeSend: function() {
         $($target).html('<div><span class="ajax_loading">&nbsp;</span><span style="font-style:italic;font-size:1.5em;">'+markdown.message.pleasewait+'</span></div>');
      },// beforeSend()
      async:true,
      type:(markdown.settings.debug?'GET':'POST'),
      url: markdown.url,
      data: $data,
      datatype:'html',
      success: function (data) {     

         $($target).html(data); 

         /* jshint ignore:start */
         var $callback=($params.callback===undefined)?'':$params.callback;
         if($callback!=='') eval($callback);				  
         /* jshint ignore:end */
      }
   }); // $.ajax() 

} // function ajaxify()

/**
 * Add a new entry in the search box (append and not replace)
 * 
 * @param {json} $entry
 *      keyword           : the value to add in the search area
 *      reset (optional)  : if true, the search area will be resetted before (so only search for the new keyword)
 *      
 * @returns {Boolean}
 */
function addSearchEntry($entry) {

   $bReset  = (($entry.reset==='undefined') ? false : $entry.reset);

   $current=$('#search').val().trim();
   
   if (($current!='') && ($bReset==false)){
      // Append the new keyword only when bReset is not set or set to False
      var values = $current.split(','); 
      values.push($entry.keyword); 			   
      $('#search').val(values.join(',')); 
   } else {
      $('#search').val($entry.keyword);
   }
   
   return true;

} // function addSearchEntry()

/**
 * The ajax request has returned the list of files.  Build the table and initialize the #TOC DOM object
 *
 * @param {json} $data  The return of the ?task=listFiles request
 * @returns {Boolean}
 */
function initFiles($data) {
   
   //if (markdown.settings.debug) console.log($data.count);
   
   if($data.hasOwnProperty('count')) {
      // Display the number of returned files
      var $msg=markdown.message.filesfound;
      Noty({message:$msg.replace('%s', $data.count), type:'notification'});         
   }

   // Build the table
   if($data.hasOwnProperty('results')) {

      var tbl = document.createElement('table');
      
      tbl.id="tblFiles"
      tbl.setAttribute("class","table table-hover table-bordered");
      tbl.style.width = '100%';
      
      var tbdy = document.createElement('tbody');

      $.each($data['results'], function($id, $item) {     
         
         var tr = document.createElement('tr');
         
         var td = document.createElement('td');
         td.dataset.folder=$item.folder;
         td.appendChild(document.createTextNode($item.folder))
         tr.appendChild(td)
         
         var td = document.createElement('td');
         td.dataset.file=$item.file;
         td.appendChild(document.createTextNode($item.display))
         tr.appendChild(td)
        
         tbdy.appendChild(tr);
      
      });
  
      // The table is now complete, add it into the page
      tbl.appendChild(tbdy);    
      $('#TOC').html(tbl);

      $('#tblFiles > tbody  > tr > td').click(function(e) {

         // By clicking on the second column, with the data-file attribute, display the file content
         if ($(this).attr('data-file')) {
            
            // On the first click, remove the image that is used for the background.  No more needed, won't be displayed anymore
            if ($('#IMG_BACKGROUND').length) $('#IMG_BACKGROUND').remove();         
           
            var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).data('file'))));  
            
            if (markdown.settings.debug) console.log("Show note "+$(this).data('file'));  
            ajaxify({task:'display',param:$fname,callback:'afterDisplay($data.param)',target:'CONTENT'});
            $(this).addClass("selected");                  
         }

         // By clicking on the first column (with foldername), get the folder name and apply a filter to only display files in that folder
         if ($(this).attr('data-folder')) {    
            
            // retrieve the name of the folder from data-folder
            var $folder=$(this).data('folder').replace('\\','/');

            if (markdown.settings.debug) console.log("Apply filter for "+$folder);  
            
            // Set the value in the search area
            addSearchEntry({keyword:$folder});        
           
         } // if ($(this).attr('data-folder'))

      }); // $('#tblFiles > tbody  > tr > td').click()

   } // if($data.hasOwnProperty('results'))

   // initialize the search area, thanks to the Flexdatalist plugin
   if ($.isFunction($.fn.flexdatalist)){
      $('.flexdatalist').flexdatalist({
         toggleSelected: true,
         minLength: 2,
         valueProperty: 'id',
         selectionRequired: false,
         visibleProperties: ["name","type"],
         searchIn: 'name',
         data: 'index.php?task=tags',
         focusFirstResult:true,
         toggleSelected:true,
         noResultsText:markdown.message.search_no_result
      });
   
      // Add automatic filtering if defined in the settings.json file
      if(markdown.settings.auto_tags!=='') addSearchEntry({keyword:markdown.settings.auto_tags});
   }

   $('#search').css('width', $('#TDM').width()-5);
   $('.flexdatalist-multiple').css('width', $('#TDM').width()-5).show();
   $('#search-flexdatalist').css('width', $('#TDM').width()-35);
   
   // Interface : put the cursor immediatly in the edit box
   try {               
      $('#search-flexdatalist').focus();
   } catch(err) {         
   }

   // See if the custominiFiles() function has been defined and if so, call it
   if (typeof custominiFiles !== 'undefined' && $.isFunction(custominiFiles)) custominiFiles();

   return true;

} // iniFiles()

/**
 * Initialize each action buttons of the displayed note.  
 * These buttons should : 
 *    - Have an id starting with "icon_xxxx" (f.i. id="icon_preview")
 *    - Have a data-task attribute           (f.i. data-task="preview")
 *    
 * @returns {undefined}
 */
function initializeTasks() {

   // Initialise print preview plugin, should be done before clicking on the button
   if ($.isFunction($.fn.printPreview)) $('[data-task="printer"]').printPreview();
   
   // Get all DOM objects having a data-task attribute
   $("[data-task]").click(function() {
      
      var $task=$(this).data('task');

      var $fname=( $(this).attr('data-file') ? $(this).data('file') : '');
      var $tag=  ( $(this).attr('data-tag') ? $(this).data('tag').replace('\\','/')  : '');

      if($fname!=='') $fname=window.btoa(encodeURIComponent(JSON.stringify($fname)));   

      switch($task) {
         
         case 'clipboard':   
            
            // Initialize the Copy into the clipboard button, See https://clipboardjs.com/
            if (markdown.settings.debug) console.log('Clipboard -> copy the link of the current note in the clipboard');
            if(typeof Clipboard == 'function'){
               new Clipboard('*[data-task="clipboard"]');
               Noty({message:markdown.message.copy_link_done, type:'success'});
            } else {
               $(this).remove();
            }
            break;
            
         case 'display':
            
            // Display the file by calling the Ajax function. Display its content in the CONTENT DOM element
            if (markdown.settings.debug) console.log('Display -> show note ['+$fname+']');
            ajaxify({task:'display',param:$fname,callback:'afterDisplay($data.param)',target:'CONTENT'});            
            break;
            
         case 'edit':   
            
            //if (markdown.settings.debug) console.log('Edit -> start the associated edit program (like Notepad f.i.)');
            ajaxify({task:$task,param:$fname});
            break;
            
         case 'printer':   
            
            //if (markdown.settings.debug) console.log('Print -> start the print preview plugin');
            break;               
            
         case 'slideshow':
            
            //if (markdown.settings.debug) console.log('Slideshow -> open a new tab in the browser and show the markdown in a slideshow format');
            slideshow($fname);
            break;
            
         case 'tag':   
            
            if (markdown.settings.debug) console.log('Tag -> filter on ['+$tag+']');
            addSearchEntry({keyword:$tag, reset:true});      
            break;
            
         case 'window':   
            
            //if (markdown.settings.debug) console.log('Window -> Open the note in a new window');
            window.open($fname);
            break;
            
         default : 
            
            console.warn('Sorry, unknown task ['+$task+']');
            
      } // switch($task)
     
   }); // $("[data-task]").click(function()

   return true;
   
} // function initializeTasks()

/** 
 * If a note contains a link to an another note, use ajax and not normal links
 * @returns {Boolean}     
 */
function replaceLinksToOtherNotes() {
   
   try {
      
      if (markdown.settings.debug) console.log('Replace internal links to notes');

      var $text=$('#CONTENT').html();

      // Retrieve the URL of this page but only the host and script name, no querystring parameter (f.i. "http://localhost:8080/notes/index.php")
      var $currentURL=location.protocol + '//' + location.host + location.pathname;   

      // Define a regex for matching every links in the displayed note pointing to that URL
      var RegEx=new RegExp('<a href=[\'|"]' + RegExp.quote($currentURL)+ '\?.*>(.*)<\/a>', 'i');

      var $nodes=RegEx.exec($text);

      var $param=[];
      var $fname='';

      while ($nodes!=null) {

         $param=$nodes[0].match(/param=(.*)['|"]/);   // Retrieve the "param" parameter which is the encrypted filename that should be displayed         
         $fname=JSON.parse(decodeURIComponent(window.atob($param[1])));

         $sNodes='<span class="note" title="'+markdown.message.display_that_note+'" data-task="display" data-file="'+$fname+'">'+$nodes[1]+'</span>';

         $text=$text.replace($nodes[0], $sNodes);

         $nodes=RegEx.exec($text);

      } // while

      // Set the new page content
      $('#CONTENT').html($text);
   
   } catch(err) {         
      
      console.warn(err.message);
      
   }
   
   return; 
   
} // replaceLinksToOtherNotes()

/**
 * Try to find tags i.ex. §some_tag  (the tag is prefixed by the § character because # is meaningfull in markdown
 * 
 * @returns {undefined}
 */
function addLinksToTags() {
   
   var $text=$('#CONTENT').html();
   
   // markdown.settings.prefix_tag is set by markdown.php and, by default, equal to § 
   // Every words prefixed by § will be considered as a tag just like "#word" in social network.
   // The # character is used by markdown language so, use an another one.
   try {
      
      // Explanation of the regex
      // 
      // ( |,|;|\\.|\\n|\\r|\\t)*       Before : Allowed characters before the tag : a space, comma, dot comma, dot, carriage return, linefeed or tab, one or more (f.i. a carriage return and a linefeed are matched)
      // markdown.settings.prefix_tag   Symbol : Match the § character
      // ([a-zA-Z0-9]+)                 Tag    : a word composed of letters and figures  
      // ( |,|;|\\.|\\n|\\r|\\t|$)      Afeter : Allowed characters after the tag : space, comma, dot comma, dot, carriage return, linefeed or tab
      
      
      var RegEx=new RegExp('( |,|;|\\.|\\n|\\r|\\t)*'+markdown.settings.prefix_tag+'([a-zA-Z0-9]+)( |,|;|\\.|\\n|\\r|\\t)*', 'i');
      if (markdown.settings.debug) console.log('RegEx for finding tags : '+RegEx);
      
      var $tags=RegEx.exec($text);

      while ($tags!=null) {         

         if (markdown.settings.debug) console.log("Process tag "+$tags[0]);         
         
         $sTags=
            (($tags[1]!==undefined)?$tags[1]:'')+                                                                                           // Before the span
            '<span class="tag" title="'+markdown.message.apply_filter_tag+'" data-task="tag" data-tag="'+$tags[2]+'">'+$tags[2]+'</span>'+  // The span for tagging the word
            (($tags[3]!==undefined)?$tags[3]:'');                                                                                           // After the span
    
         $text=$text.replace(new RegExp($tags[0], "g"), $sTags);

         $tags=RegEx.exec($text);

      } // while

      // Set the new page content
      $('#CONTENT').html($text);

   } catch(err) {         
      
      console.warn(err.message);
      
   }

   return; 
} // function addLinksToTags()

/** 
 * Force links that points on the same server (localhost) to be opened in a new window
 * @returns {Boolean}     
 */
function forceNewWindow() {

   var $currentURL=location.protocol + '//' + location.host;   

   $('a[href^="http:"], a[href^="https:"]').not('[href^="'+$currentURL+'/"]').attr('target', '_blank');
    
   return true;      

} // function forceNewWindow()

/**
 * Add icons to .pdf, .xls, .doc, ... hyperlinks and for some extensions (like log, md, pdf, txt, ...) force to open in a new window
 */
function addIcons() {

   try {
      
      $("a").each(function() {

         $href=$(this).attr("href");   
         $sAnchor=$(this).text();

         if (/\.doc[x]?$/i.test($href)) { 
            // Word document
            $sAnchor+='<i class="icon_file fa fa-file-word-o" aria-hidden="true"></i>';            
            $(this).html($sAnchor).addClass('download');       
         } else if (/\.(log|md|markdown|txt)$/i.test($href)) { 
            // LOG - Open it in a new windows and not in the current one
            $sAnchor+='<i class="icon_file fa fa-file-text-o" aria-hidden="true"></i>';            
            $(this).html($sAnchor).addClass('download-link').attr('target', '_blank');
         } else if (/\.pdf$/i.test($href)) { 
            // PDF - Open it in a new windows and not in the current one
            $sAnchor+='<i class="icon_file fa fa-file-pdf-o" aria-hidden="true"></i>';            
            $(this).html($sAnchor).addClass('download-link').attr('target', '_blank');
         } else if (/\.ppt[x]?$/i.test($href)) { 
            // Powerpoint
            $sAnchor+='<i class="icon_file fa fa-file-powerpoint-o" aria-hidden="true"></i>';            
            $(this).html($sAnchor).addClass('download-link');       
         } else if (/\.xls[m|x]?$/i.test($href)) { 
            // Excel
            $sAnchor+='<i class="icon_file fa fa-file-excel-o" aria-hidden="true"></i>';            
            $(this).html($sAnchor).addClass('download-link');       
         } else if (/\.(7z|gzip|tar|zip)$/i.test($href)) { 
            // Archive
            $sAnchor+='<i class="icon_file fa fa-file-archive-o" aria-hidden="true"></i>';            
            $(this).html($sAnchor).addClass('download-link');    
         }

      });
      
    } catch(err) {         
      
      console.warn(err.message);
      
   }
   
   return true;  

} // function addIcons()

/**
 * Called after the ajax "display" request, the file is almost displayed
 */
function afterDisplay($fname) {

   try {

      // Remove functionnalities if jQuery librairies are not loaded
      if(typeof Clipboard !== 'function') $('[data-task="clipboard"]').remove();
      if (!$.isFunction($.fn.printPreview)) $('[data-task="printer"]').remove();

      // Try to detect email, urls, ... not yet in a <a> tag and so ... linkify them
      if ($.isFunction($.fn.linkify)) {
         if (markdown.settings.debug) console.log('linkify plain text');
         $('page').linkify();
      }

      // If a note contains a link to an another note, use ajax and not normal links
      replaceLinksToOtherNotes();

      // Add links to tags
      addLinksToTags();

      // Force links that points on the same server (localhost) to be opened in a new window
      forceNewWindow();

      // Add icons to .pdf, .xls, .doc, ... hyperlinks
      addIcons();  

      // Initialize each action buttons of the displayed note
      initializeTasks()

      // Retrieve the heading 1 from the loaded file 
      var $title=$('#CONTENT h1').text();				  
      if ($title!=='') $('title').text($title);

      var $fname=$('div.filename').text();				  
      if ($fname!=='') $('#footer').html('<strong style="text-transform:uppercase;">'+$fname+'</strong>');

      // Highlight common languages (html, javascript, php, ...)
      // @link : https://github.com/isagalaev/highlight.js
      try {
         $('pre code').each(function(i, block) {
            hljs.highlightBlock(block);
         });
      } catch(err) {
         if (markdown.settings.debug) console.warn(err.message);
      }

      // Interface : put the cursor immediatly in the edit box
      try {               

         $('#search').focus();

         // Get the searched keywords.  Apply the restriction on the size.
         var $searchKeywords = $('#search').val().substr(0, markdown.settings.search_max_width).trim();

         if ($searchKeywords!=='') {

            if ($.isFunction($.fn.highlight)){
               $("#CONTENT").highlight($searchKeywords);
            }
         }

      } catch(err) { 
         if (markdown.settings.debug) console.warn(err.message);
      }

      // See if the customafterDisplay() function has been defined and if so, call it
      if (typeof customafterDisplay !== 'undefined' && $.isFunction(customafterDisplay)) {
         customafterDisplay($fname);
      }

   } catch(err) {         
      
      console.warn(err.message);
      
   }
   
   // Just for esthetics purposes
   $('#CONTENT').fadeOut(1).fadeIn(3);         

   return true;

} // function afterDisplay()

/**
 * 
 * @returns {undefined}
 */
function onChangeSearch() {

   try {
      
      // Get the searched keywords.  Apply the restriction on the size.
      var $searchKeywords = $('#search').val().substr(0, markdown.settings.search_max_width).trim();

      var $bContinue=true;
      // See if the customonChangeSearch() function has been defined and if so, call it
      if (typeof customonChangeSearch !== 'undefined' && $.isFunction(customonChangeSearch)) {
         $bContinue=customonChangeSearch($searchKeywords);
      }

      if ($bContinue===true) {

         if ($searchKeywords!='') {
            $msg=markdown.message.apply_filter;
            Noty({message:$msg.replace('%s', $searchKeywords), type:'notification'});         
         }

         // On page entry, get the list of .md files on the server
         ajaxify({task:'search',param:window.btoa(encodeURIComponent($searchKeywords)), callback:'afterSearch("'+$searchKeywords+'",data)'});

      } else {

         if (markdown.settings.debug) console.log('cancel the search');

      }

   } catch(err) {         
      
      console.warn(err.message);
      
   }
   
   return true;

} // Search()

/*
 * Called when the ajax request "onChangeSearch" has been successfully fired.
 * Process the result of the search : the returned data is a json string that represent an 
 * array of files that matched the searched pattern.
 */
function afterSearch($keywords, $data) {

   try {

      // Check if we've at least one file
      if (Object.keys($data).length>0) {

         // Process every rows of the tblFiles array => process every files 
         $('#tblFiles > tbody  > tr > td').each(function() {

            // Be sure to process only cells with the data-file attribute.
            // That attribute contains the filename, not encoded
            if ($(this).attr('data-file')) {

               // Get the filename (is relative like /myfolder/filename.md)
               $filename=$(this).data('file');
               $tr=$(this).parent();

               // Default : hide the filename
               $tr.hide();                     

               // Now, check if the file is mentionned in the result, if yes, show the row back
               $.each($data, function() {
                  $.each(this, function($key, $value) {                           
                     if ($value===$filename) {
                        $tr.show();
                        return false;  // break
                     }
                  });
               }); // $.each($data)

            }
         }); // $('#tblFiles > tbody  > tr > td')

      } else {

         if ($keywords!=='') {

            noty({message:markdown.message.search_no_result, type:'success'});         

         } else { // if ($keywords!=='')

            // show everything back
            $('#tblFiles > tbody  > tr > td').each(function() {
               if ($(this).attr('data-file')) {
                  $(this).parent().show();
               }
            });

         } // if ($keywords!=='')

      } // if (Object.keys($data).length>0)

      // See if the customafterSearch() function has been defined and if so, call it
      if (typeof customafterSearch !== 'undefined' && $.isFunction(customafterSearch)) {
         customafterSearch($keywords, $data);
      }
      
   } catch(err) {         
      
      console.warn(err.message);
      
   }

} // function afterSearch()

/**
 * Open the "slideshow" view
 * 
 * @param {type} $fname
 * @returns {Boolean}
 */
function slideshow($fname) {
     
   try {
      
      var $data = new Object;
      $data.task  = 'slideshow';
      $data.param = $fname;

      $.ajax({
         async:true,
         type:(markdown.settings.debug?'GET':'POST'),
         url: markdown.url,
         data: $data,
         datatype:'json',
         success: function (data) {  

            // data is a URL pointing to the HTML version of the slideshow so ... just display
            var w = window.open(data, "slideshow");   

            if(w==undefined) {
               Noty({message:markdown.message.allow_popup_please, type:'notification'});         
            }

         } // success

      }); // $.ajax() 
      
   } catch(err) {         
      
      console.warn(err.message);
      
   }
   return true;
  
} // function slideshow()

/**
 * 
 * @param {json} $params
 *      message : the message to display
 *      type    : success, error, warning, information, notification
 *      
 * @returns {undefined}
 */
function Noty($params) {
   
   if ($.isFunction($.fn.noty)) {
      
      if($params.message==='') return false;

      $type = (($params.type==='undefined')?'info':$params.type);

      // More options, see http://ned.im/noty/options.html
      var n = noty({
         text: $params.message,
         theme: 'relax',
         timeout: 2400,
         layout: 'bottomRight',
         type: $type
      }); // noty() 
      
   }
   
} // function Noty()