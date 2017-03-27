<?php
// @codingStandardsIgnoreFile
?>
<!--
   based on remark : https://github.com/gnab/remark
   Tutorial : https://github.com/gnab/remark/wiki/Markdown
-->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
    		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    		<meta name="robots" content="%ROBOTS%" />
    		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />
    		<meta name="author" content="MarkNotes | Notes management" />
    		<meta name="designer" content="MarkNotes | Notes management" />
    		<meta name="keywords" content="%TITLE%" />
    		<meta name="description" content="%TITLE%" />
    		<meta name="apple-mobile-web-app-capable" content="yes" />
    		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
    		<meta property="og:url" content="%URL_PAGE%" />
    		<meta property="og:type" content="article" />
    		<meta property="og:image" content="%URL_IMG%" />
    		<meta property="og:image:width" content="1200" />
    		<meta property="og:image:height" content="522" />
    		<meta property="og:title" content="%TITLE%" />
    		<meta property="og:site_name" content="%SITE_NAME%" />
    		<meta property="og:description" content="%TITLE%" />

        <title>%TITLE%</title>

    		<link rel="stylesheet" type="text/css" href="%ROOT%/libs/font-awesome/css/font-awesome.min.css" media="screen" />

        <style>

            #source{display:none;}

            body {
                font-family: 'Droid Serif';
            }
            h1, h2, h3 {
                font-family: 'Yanone Kaffeesatz';
                font-weight: 400;
                margin-bottom: 0;
            }

            img {
                max-width:800px;
            }

            .remark-slide-content h1 {
                font-size: 3em;
            }
            .remark-slide-content h2 {
                font-size: 2em;
            }
            .remark-slide-content h3 {
                font-size: 1.6em;
            }
            .footnote {
                position: absolute;
                bottom: 1em;
            }
            li p {
                line-height: 1.25em;
            }
            .italic {
                font-style:italic;
            }
            .red {
                color: #fa0000;
             }
            .large {
                font-size: 2em;
            }
            a, a > code {
                color: rgb(249, 38, 114);
                text-decoration: none;
            }
            code {
                background: #e7e8e2;
                border-radius: 5px;
            }
            .remark-code, .remark-inline-code {
                font-family: 'Ubuntu Mono';
            }
            .remark-code-line-highlighted {
                background-color: #373832;
            }
            .pull-left {
                float: left;
                width: 47%;
            }
            .pull-right {
                float: right;
                width: 47%;
            }
            .pull-right ~ p {
                clear: both;
            }
            #slideshow .slide .content code {
                font-size: 0.8em;
            }
            #slideshow .slide .content pre code {
                font-size: 0.9em;
                padding: 15px;
            }
            .inverse {
                background: #272822;
                color: #777872;
                text-shadow: 0 0 20px #333;
            }
            .inverse h1, .inverse h2 {
                color: #f3f3f3;
                line-height: 0.8em;
            }

            /* Slide-specific styling */
            #slide-inverse .footnote {
                bottom: 12px;
                left: 20px;
            }
            #slide-how .slides {
                font-size: 0.9em;
                position: absolute;
                top:  151px;
                right: 140px;
            }
            #slide-how .slides h3 {
                margin-top: 0.2em;
            }
            #slide-how .slides .first, #slide-how .slides .second {
                padding: 1px 20px;
                height: 90px;
                width: 120px;
                -moz-box-shadow: 0 0 10px #777;
                -webkit-box-shadow: 0 0 10px #777;
                box-shadow: 0 0 10px #777;
            }
            #slide-how .slides .first {
                background: #fff;
                position: absolute;
                top: 20%;
                left: 20%;
                z-index: 1;
            }
            #slide-how .slides .second {
                position: relative;
                background: #fff;
                z-index: 0;
            }

            /* Two-column layout */
            .left-column {
                color: #777;
                width: 20%;
                height: 92%;
                float: left;
            }
            .left-column h2:last-of-type, .left-column h3:last-child {
                color: #000;
            }
            .right-column {
                width: 75%;
                float: right;
                padding-top: 1em;
            }
        </style>
    </head>
    <body>

        <textarea id="source" readonly="readonly">%CONTENT%</textarea>

        <script src="%URL%/libs/remark/remark-latest.min.js"></script>

        <script type="text/javascript">
            var hljs = remark.highlighter.engine;
            var slideshow = remark.create({
                highlightStyle: 'monokai'
            });
        </script>

    </body>

</html>
