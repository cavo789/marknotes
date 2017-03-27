<?php
// @codingStandardsIgnoreFile
?>
<!DOCTYPE html>
<html lang="en">

   <head>

        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="robots" content="noindex, nofollow" />
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

        <meta name="author" content="MarkNotes | Notes management" />
        <meta name="designer" content="MarkNotes | Notes management" />
        <meta name="keywords" content="markdown, marknotes, html, slideshow, knowledge management" />
        <meta name="description" content="MarkNotes is a knowledge management tool.  Notes are written in the Markdown language and then displayed as full functionnal html page.  Can be displayed as a slideshow also." />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black" />
        <meta property="og:type" content="article" />
        <meta property="og:image" content="%ROOT%assets/notes.jpg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="800" />
        <meta property="og:title" content="MarkNotes | Notes management" />
        <meta property="og:site_name" content="MarkNotes | Notes management" />
        <meta property="og:description" content="MarkNotes | Notes management" />

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/animatecss/3.5.2/animate.min.css" />
        <link rel="stylesheet" href="%ROOT%/libs/jquery-albe-timeline/style-albe-timeline.css" />
    </head>

    <body>
        <h1><%SITE_NAME%></h1>
        <div id="myTimeline">
        </div>

        <script src="%ROOT%/libs/jquery/jquery.min.js"></script>
        <script src="%ROOT%/libs/store/store.everything.min.js"></script>
        <script src="%ROOT%/assets/js/markdown.js"></script>
        <script src="%ROOT%/libs/jquery-albe-timeline/jquery-albe-timeline-1.1.2.js"></script>

        <!--%MARKDOWN_GLOBAL_VARIABLES%-->

        <script>

          $(document)
              .ready(function () {

                ajaxify({
                    task: 'getTimeline',
                    dataType: 'json',
                    callback: 'ShowTimeline(data)',
                    useStore: markdown.settings.use_localcache
                });


          }); // $( document ).ready()

          function ShowTimeline($data) {
            $("#myTimeline").albeTimeline($data, {
              effect: "zoomIn",
              showMenu: true,
              //sortDesc: true,
              language: markdown.settings.locale
            });
          }

        </script>
        
    </body>

</html>
