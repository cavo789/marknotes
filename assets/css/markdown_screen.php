<?php

   header('Content-type: text/css');
   
   $imgMaxWidth=filter_input(INPUT_GET, 'imgMaxWidth', FILTER_SANITIZE_NUMBER_INT);   
   if ($imgMaxWidth != 0) echo 'img {max-width:'.$imgMaxWidth.'px;}';
   
?>

/* note_link will allow to stylize links to other notes within the local website */
a.note_link:after { font-family: FontAwesome; content: " \f18e"; }

body {overflow:hidden;}

/* By selecting a file from the filelist, highlight its name */
#tblFiles > tbody > tr:nth-child(odd) .selected{background-color:#90b6e2;color:white;}
#tblFiles  > tbody > tr:nth-child(even) .selected{background-color:#90b6e2;color:white;}

/* Style for the "Open in a new window" hyperlink */
.open_newwindow{text-decoration:underline;}

/* Style for the "Edit file" hyperlink */
.edit_file{text-decoration:underline;color:#337ab7;cursor:pointer;}

/* Style for the formatting of the name of the file, displayed in the content, first line */
.filename{font-style:italic;font-weight:bold;color:#dfdfe0;top:15px;position:inherit;} 

/* Default page background color */
body{background:#F7F2E9;}

/* The root folder name */
.rootfolder{display:none;}

/* The search area.  The width will be initialized by javascript */
#search{position:fixed !important;left:5px;top:5px;z-index: 1;}
.selectize-dropdown, .selectize-input {position:fixed;}

/* Formating of the array with the list of files */
/* The search area.  The width will be initialized by javascript */
#tblFiles{font-size:0.8em;color:#445c7b;background-color:#f5f5f5;}
#tblFiles>thead>tr{font-size:1.2em;color:#445c7b;background-color:#c7c0c0;}
/*#tblFiles>thead>tr.tablesorter-filter-row{background-color:red;color:white;}*/

/* The icons area is used f.i. for displaying a lock icon when the note contains encrypted data */
#icons {display:inline-block;position:absolute;top:5px;right:-1px;margin-right:10px;}

/* Images */
#icon_edit{margin-left:20px;color:lightgray;}
#icon_lock{margin-left:2px;color:#abe0ab;}
#icon_printer{color:lightgray;}
#icon_clipboard{margin-left:20px;color:lightgray;}
#icon_window{margin-left:20px;color:lightgray;}

.icon_file{padding-left:5px;}
.icon_encrypted{padding-left:5px;padding-right:5px;color:#abe0ab;}

/* Content if the full page : contains the list of files and the content of the select note */

#CONTENT{margin-left:10px;top:5px !important;max-height:960px;overflow-y:auto;overflow-x:auto;width:100%;left:-15px;}

/* Background image displayed on the first screen */
#IMG_BACKGROUND{width:100%;height:100%;vertical-align:middle;}

/* TDM if the left part, i.e. the container of the search area and TOC (the list of files) */
#TDM{left:5px; top:5px !important;max-height:960px;overflow-y:auto;overflow-x:auto;}
#TOC{position:inherit;top:30px;}

/* page is used to display the content of the selected note */
page{background:white;display:none;margin:0 auto;margin-bottom:0.5cm;box-shadow:0 0 0.5cm rgba(0,0,0,0.5);}

/* Don't display informations that are targeted for printers only */            
.onlyprint{display:none;}

.countfiles{font-size:xx-small;font-style:italic;}
/* Use by the jQuery Highlite plugin, highlight searched keywords */
.highlight{background-color:yellow;border-radius:.125em;}  

.download-link{background-color:rgba(255, 235, 59, 0.21);text-decoration:underline;}