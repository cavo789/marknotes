
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

$(document).ready(function() {
   
   // Add keybinding (not recommended for production use)
   /*$(document).bind('keydown', function(e) {
       var code = (e.keyCode ? e.keyCode : e.which);
       if (code == 80 && !$('#print-modal').length) {
           $.printPreview.loadPrintPreview();
           return false;
       }            
   });*/

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

function addSearchEntry($newValue) {

   $current=$('#search').val().trim();
   
   if ($current!='') {
      var values = $current.split(','); 
      values.push($newValue); 			   
      $('#search').val(values.join(',')); 
   } else {
      $('#search').val($newValue);
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
            
            // Just for esthetics purposes
            $('#CONTENT').fadeOut(1).fadeIn();            
            
            // On the first click, remove the image that is used for the background.  No more needed, won't be displayed anymore
            if ($('#IMG_BACKGROUND').length) $('#IMG_BACKGROUND').remove();         
           
            var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).data('file'))));  
            
            if (markdown.settings.debug) console.log("Show note "+$(this).data('file'));  
            ajaxify({task:'display',param:$fname,callback:'afterDisplay()',target:'CONTENT'});
            $(this).addClass("selected");                  
         }

         // By clicking on the first column (with foldername), get the folder name and apply a filter to only display files in that folder
         if ($(this).attr('data-folder')) {    
            
            // retrieve the name of the folder from data-folder
            var $folder=$(this).data('folder').replace('\\','/');

            if (markdown.settings.debug) console.log("Apply filter for "+$folder);  
            
            // Set the value in the search area
            addSearchEntry($folder);        
            
            //$('#search').val($folder);
           
         } // if ($(this).attr('data-folder'))

      }); // $('#tblFiles > tbody  > tr > td').click()

   } // if($data.hasOwnProperty('results'))

   // initialize the search area, thanks to the Flexdatalist plugin
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
 * If a note contains a link to an another note, use ajax and not normal links
 * @returns {Boolean}     
 */
function replaceLinksToOtherNotes() {
   
   // Retrieve the URL of this page but only the host and script name, no querystring parameter (f.i. "http://localhost:8080/notes/index.php")
   var $currentURL=location.protocol + '//' + location.host + location.pathname;   
   
   // Define a regex for matching every links in the displayed note pointing to that URL
   var searchPattern = new RegExp('^' + $currentURL, 'i');

   $('a').each(function() {	
      if (searchPattern.test(this.href)) {

         // We've found an internal link; can be a link to an another note 
         
         // Add the "note_link" class so it's possible to stylize links to other notes
         $(this).addClass('note_link');
         
         // Extract only the querystring (f.i. task=display&param=xxxxxx)
         $queryString=this.href.replace($currentURL+'?', '');

         $task = $queryString.match(/task=([^&]*)/);     // The task is more probably 'display'
         $param = $queryString.match(/param=([^&]*)/);   // Retrieve the "param" parameter which is the encrypted filename that should be displayed

         $(this).click(function(e) {
            e.preventDefault(); 
            e.stopImmediatePropagation();
            
            // Display the file by calling the Ajax function. Display its content in the CONTENT DOM element
            ajaxify({task:$task[1],param:$param[1],callback:'afterDisplay()',target:'CONTENT'});
         });

      }
   
   }); // $('a').each()
   
   return true;   
   
} // replaceLinksToOtherNotes()

/**
 * Try to find tags i.ex. §some_tag  (the tag is prefixed by the § character because # is meaningfull in markdown
 * 
 * @returns {undefined}
 */
function addLinksToTags() {
   
   var $text=$('#CONTENT').html();
   
   var $tags = $text.match(/(§[a-zA-Z0-9]+)(?!.*\\1)/i);
  
   if ($tags!==null) {
      
      // Keep unique values
      $tags = $tags.unique();
   
      $.each($tags, function($index, $tag) {           
         if (markdown.settings.debug) console.log("Process tag "+$tag);         
         $sTags='<span class="tags" data-tag="'+$tag.substr(1)+'">'+$tag+'</span>';
         $text=$text.replace(new RegExp($tag, "g"), $sTags);
      });
      
      // Set the new page content
      $('#CONTENT').html($text);
      
      // Add a click event to every tags : by clicking on a tag, update the search box
      $('#CONTENT').on('click','.tags',function(){
         $tag=$(this).data('tag').replace('\\','/');
         // Set the value in the search area
         addSearchEntry($tag);      
      });

   } // if ($tags!==null)
   
   return; 
} // function addLinksToTags()

/** 
 * Force links that points on the same server (localhost) to be opened in a new window
 * @returns {Boolean}     
 */
function forceNewWindow() {

   $('a').each(function() {					
      $(this).click(function(e) {
         e.preventDefault(); 
         e.stopImmediatePropagation();
         window.open(this.href, '_blank');
      });
   }); // $('a').each()

   return true;      

} // function forceNewWindow()

/**
 * Add icons to .pdf, .xls, .doc, ... hyperlinks
 */
function addIcons() {

   $("a").each(function() {

      $href=$(this).attr("href");   
      $sAnchor=$(this).text();

      if (/\.doc[x]?$/i.test($href)) { 
         // Word document
         $sAnchor+='<i class="icon_file fa fa-file-word-o" aria-hidden="true"></i>';            
         $(this).html($sAnchor).addClass('download-link');       
      } else if (/\.pdf$/i.test($href)) { 
         // PDF
         $sAnchor+='<i class="icon_file fa fa-file-pdf-o" aria-hidden="true"></i>';            
         $(this).html($sAnchor).addClass('download-link');       
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

   return true;  

} // function addIcons()

/**
 * Try to detect every emails in the HTML content but not yet in a html tags
 * So match "christophe@test.be" and don't match <a ...>christophe@test.be</a>
 * 
 * Then add the mailto hyperlink to these 'plain text' emails
 * 
 * @returns {undefined}
 */
function ProcesseMails() {

   var $text=$('#CONTENT').html();
   
   // ([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+) : search emails
   // (?![^<]*>|[^<>]*<\/) add a restriction : don't search content within html tags
   var $emails = $text.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)(?![^<]*>|[^<>]*<\/)/gi);
   
   if ($emails!==null) {

      $.each($emails, function($index, $email) {       
         $seMail='<a class="email" href="mailto:'+$email+'">'+$email+'</a>';

         if (markdown.settings.debug) console.log('replace '+$email);      
         if (markdown.settings.debug) console.log('by '+$seMail);     
         
         $text=$text.replace(new RegExp($email, "g"), $seMail);
      });
      
      if (markdown.settings.debug) console.log($text);

      $('#CONTENT').html($text);
      
   } // if ($emails!==null)
   
   return true;
   
} // function ProcesseMails()

/**
 * Called when a file is displayed
 */
function afterDisplay() {
   
   // Initialize the Copy into the clipboard button
   // See https://clipboardjs.com/
   new Clipboard('.copy_clip');
   
   // Initialise print preview plugin
   $('#icon_printer').printPreview();

   // Highlight common languages (html, javascript, php, ...)
   // @link : https://github.com/isagalaev/highlight.js
   $('pre code').each(function(i, block) {
      hljs.highlightBlock(block);
   });

   $('#CONTENT').show();
   
   $('html, body').animate({
      'scrollTop' : $("#CONTENT").position().top -25
   });

   // Retrieve the heading 1 from the loaded file 
   var $title=$('#CONTENT h1').text();				  
   if ($title!=='') $('title').text($title);

   var $fname=$('div.filename').text();				  
   if ($fname!=='') $('#footer').html('<strong style="text-transform:uppercase;">'+$fname+'</strong>');

   // If a note contains a link to an another note, use ajax and not normal links
   replaceLinksToOtherNotes();

   // Add links to tags
   addLinksToTags();
   
   // Force links that points on the same server (localhost) to be opened in a new window
   forceNewWindow();

   // Add icons to .pdf, .xls, .doc, ... hyperlinks
   addIcons();  
   
   ProcesseMails();
   
   // Interface : put the cursor immediatly in the edit box
   try {               
      $('#search').focus();
   } catch(err) {         
   }

   // Get the searched keywords.  Apply the restriction on the size.
   var $searchKeywords = $('#search').val().substr(0, markdown.settings.search_max_width).trim();

   if ($searchKeywords!=='') {
      $("#CONTENT").highlite({
         text: $searchKeywords
      });
   }

   // By clicking on the "Edit" link, start the associated edit program (like Notepad f.i.)
   $('#icon_edit').click(function(e) {
      var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).data('file'))));      
      ajaxify({task:'edit',param:$fname});
   });
   
   // By clicking on the "Slideshow" link, open a new tab in the browser and show the markdown 
   // in a slideshow format
   $('#icon_slideshow').click(function(e) {
      var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).data('file'))));   
      slideshow($fname);
   });

   $('#icon_window').click(function(e) {
      var $fname=$(this).data('file');  
      window.open($fname);
   });

   // See if the customafterDisplay() function has been defined and if so, call it
   if (typeof customafterDisplay !== 'undefined' && $.isFunction(customafterDisplay)) {
      customafterDisplay($fname);
   }

   return true;

} // function afterDisplay()

/**
 * 
 * @returns {undefined}
 */
function onChangeSearch() {

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

   return true;

} // Search()

/*
 * Called when the ajax request "onChangeSearch" has been successfully fired.
 * Process the result of the search : the returned data is a json string that represent an 
 * array of files that matched the searched pattern.
 */
function afterSearch($keywords, $data) {

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

} // function afterSearch()

function slideshow($fname) {
      
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
  
var $data = new Object;
   $data.task  = 'killSlideshow';
   $data.param = $fname;         
$.ajax({
      async:true,
  type: "GET",
  url: markdown.url,
      data: $data
});         
      } // success
      
   }); // $.ajax() 
   
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