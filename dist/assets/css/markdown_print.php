<?php
/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.2
* @author    : christophe@aesecure.com
* @copyright : MIT (c) 2016 - 2017
* @url       : https://github.com/cavo789/markdown#readme
* @package   : 2017-01-27T17:48:23.647Z
 */
* ?>
<?php

   header('Content-type: text/css');
   
   if (!empty($_GET['appName'])) {
      $appName=filter_input(INPUT_GET, 'appName', FILTER_SANITIZE_STRING);    
      if($appName!=='') { 
         $appName=base64_decode($appName);
         $appName=substr($appName, 0, 100);
         echo '#CONTENT:before {content:"'.$appName.'";display:block;text-align:center;border:1px solid #ccc;font-style:italic;margin:0 0 1em;padding:8px 10px;}';         
      }
   }
   
?>

/*page[size="A4"][layout="portrait"] {width:29.7cm;height:21cm;}*/

body {
   background:#FFF;
   color: #000;
   font-family: Georgia, serif;
   line-height: 1.2;
}

#CONTENT p, table, ul, ol, dl, blockquote, pre, code, form {
   margin: 0 0 1em;
}

#CONTENT h1,h2,h3,h4,h5 {
   font-weight: normal;
   margin: 2em 0 0.5em;
   text-shadow: rgba(0, 0, 0, 0.44) 1px 1px 2px;
}

/* Put the href of the link in the content, juste after the link */
/* So <a href="www.google.be">Google</a> will be printed like */
/* Google (www.google.be) */
a:link:after {
   content: " (" attr(href) ") ";
   font-size: 80%;
   text-decoration: none;
}

#CONTENT h1 {font-size:2em; margin: 2em 0 0.25em;}
#CONTENT h2 {font-size:1.7em;}
#CONTENT h3 {font-size:1.5em;}
#CONTENT h4 {font-size:1.2em;}
#CONTENT h5 {font-size:1em;}

#CONTENT ul, li {
   display:block;
   page-break-inside:avoid;
}

/* Don't print objects that only should be displayed on screen */
.onlyscreen{display:none;}

/* Don't print the left part */
#TDM{display:none;}

/*body, page{box-shadow:0;}*/

/* Make text a little larger on print */
/*page{font-size:larger;}*/

footer {
   position:fixed;
   display:block;
   bottom:0px;
   border-top: 1px solid #cecece;
   font-size: 0.83em;
   margin: 2em 0 0;
   padding: 1em 0 0;
}