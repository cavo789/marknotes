
/* global markdown, custominiFiles, customafterDisplay, customafterSearch */

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

/**
 * The ajax request has returned the list of files.  Build the table and initialize the #TOC DOM object
 *
 * @param {json} $data  The return of the ?task=listFiles request
 * @returns {Boolean}
 */
function initFiles($data) {
   
   //console.log($data.count);
   
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
$('#BACKGROUND').remove();            
            var $fname=window.btoa(encodeURIComponent(JSON.stringify($(this).data('file'))));              
            ajaxify({task:'display',param:$fname,callback:'afterDisplay()',target:'CONTENT'});
            $(this).addClass("selected");                  
         }

         // By clicking on the first column (with foldername), get the folder name and apply a filter to only display files in that folder
         if ($(this).attr('data-folder')) {    
            
            // retrieve the name of the folder from data-folder
            var $folder=$(this).data('folder');

            // Initialize the selectize.js object
            var $select = $('#search').selectize();
            var $selectize = $select[0].selectize; 

            // Set the value
            $selectize.setValue($folder.replace('\\','/'), true);

            onChangeSearch();
         }

      }); // $('#tblFiles > tbody  > tr > td').click()

   } // if($data.hasOwnProperty('results'))

   // Feed in the list with the tags
   if($data.hasOwnProperty('tags')) {

      if ($('#search').length) {
         
         $.each($data['tags'], function($id, $item) {                
            $('#search').append($('<option>', {value: $item,text :$item}));            
         });

         $('#search').selectize({            
            delimiter: ',',
            persist: false,
            create: function(input) {
               return {
                   value: input,
                   text: input
               }
           }
         });      
         
         $('.selectize-dropdown').css('width', $('#TDM').width()-5);
         $('.selectize-input').css('width', $('#TDM').width()-5);
         
      } // if ($('#search').length)
      
   }
   
   // Interface : put the cursor immediatly in the edit box
   try {               
      $('#search').focus();
   } catch(err) {         
   }

   // See if the custominiFiles() function has been defined and if so, call it
   if (typeof custominiFiles !== 'undefined' && $.isFunction(custominiFiles)) custominiFiles();

   return true;

} // iniFiles()

/** 
 *  Force links that points on the same server (localhost) to be opened in a new window
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
 * Called when a file is displayed
 */
function afterDisplay() {

   // Initialise print preview plugin
   $('#icon_printer').printPreview();

   $('#CONTENT').show();

   $('html, body').animate({
      'scrollTop' : $("#CONTENT").position().top -25
   });

   // Retrieve the heading 1 from the loaded file 
   var $title=$('#CONTENT h1').text();				  
   if ($title!=='') $('title').text($title);

   var $fname=$('div.filename').text();				  
   if ($fname!=='') $('#footer').html('<strong style="text-transform:uppercase;">'+$fname+'</strong>');

   // Force links that points on the same server (localhost) to be opened in a new window
   forceNewWindow();

   // Add icons to .pdf, .xls, .doc, ... hyperlinks
   addIcons();

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
      
      console.log('cancel the search');
      
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

/**
 * 
 * @param {json} $params
 *      message : the message to display
 *      type    : success, error, warning, information, notification
 *      
 * @returns {undefined}
 */
function Noty($params) {
   
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