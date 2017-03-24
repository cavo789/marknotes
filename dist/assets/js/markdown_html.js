/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-24T17:10:14.434Z
*/
function addTOC(){if($("article h2").length>0){var e,a,i,n,t="<nav role='navigation' class='table-of-contents hidden-xs hidden-sm'><h2>Sur cette page:</h2><ul>";$("article h2").each(function(){a=$(this),i=a.text(),n="#"+a.attr("id"),e="<li><a href='"+n+"'>"+i+"</a></li>",t+=e}),t+="</ul></nav>",$("article").prepend(t)}}$("document").ready(function(){$("img").addClass("fullimg hidden-xs hidden-sm")});