/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-21T22:24:08.020Z
*/
function addTOC(){var a,e,d,n,i="<nav role='navigation' class='table-of-contents hidden-xs hidden-sm'><h2>Sur cette page:</h2><ul>";$("article h2").each(function(){e=$(this),d=e.text(),n="#"+e.attr("id"),a="<li><a href='"+n+"'>"+d+"</a></li>",i+=a}),i+="</ul></nav>",$("article").prepend(i)}$("document").ready(function(){$("img").addClass("fullimg hidden-xs hidden-sm"),addToc()}),C();