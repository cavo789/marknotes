/**
* markdown - Script that will transform your notes taken in the Markdown format (.md files) into a rich website
* @version   : 1.0.5
* @author    : christophe@aesecure.com
* @license   : MIT
* @url       : https://github.com/cavo789/markdown
* @package   : 2017-03-20T21:07:15.852Z
*/
function isFullScreen(){return document.fullScreenElement&&null!==document.fullScreenElement||document.mozFullScreen||document.webkitIsFullScreen}function requestFullScreen(e){e.requestFullscreen?e.requestFullscreen():e.msRequestFullscreen?e.msRequestFullscreen():e.mozRequestFullScreen?e.mozRequestFullScreen():e.webkitRequestFullscreen&&e.webkitRequestFullscreen()}function exitFullScreen(){document.exitFullscreen?document.exitFullscreen():document.msExitFullscreen?document.msExitFullscreen():document.mozCancelFullScreen?document.mozCancelFullScreen():document.webkitExitFullscreen&&document.webkitExitFullscreen()}function toggleFullScreen(e){return $("#TDM").toggleClass("hidden"),$("#CONTENT").parent().toggleClass("fullwidth"),$("#icons").children("i").each(function(){"icon_fullscreen"!==this.id&&"icon_refresh"!==this.id&&"icon_edit"!==this.id&&$(this).toggleClass("hidden")}),isFullScreen()?($("#CONTENT").css("max-height",$(window).height()-10),$("#CONTENT").css("min-height",$(window).height()-10),exitFullScreen()):($("#CONTENT").css("max-height",screen.height),$("#CONTENT").css("min-height",screen.height),requestFullScreen(e||document.documentElement)),!0}