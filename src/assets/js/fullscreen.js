/**
 * Toggle fullscreen
 * @link http://stackoverflow.com/a/23971798
 */
function isFullScreen()
{
    return (document.fullScreenElement && document.fullScreenElement !== null) || document.mozFullScreen || document.webkitIsFullScreen;
} // function isFullScreen()

function requestFullScreen(element)
{
    if (element.requestFullscreen) {
        element.requestFullscreen();
    } else if (element.msRequestFullscreen) {
        element.msRequestFullscreen();
    } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
    } else if (element.webkitRequestFullscreen) {
        element.webkitRequestFullscreen();
    }
} // function requestFullScreen()

function exitFullScreen()
{
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    }
} // function exitFullScreen()

/**
 * Toggle fullscreen mode Y/N
 * @param {type} element    DOM element
 * @returns boolean
 */
function toggleFullScreen(element)
{
   
   // Hide (or show it again) the treeview and the search engine i.e. everything at the left side
    $('#TDM').toggleClass('hidden');

   // Give the content part the full width (or give it back its original width)
    $('#CONTENT').parent().toggleClass('fullwidth');

   // Hide all buttons (or show it again) except icon_fullscreen
    $('#icons').children('i').each(function () {
        if ((this.id!=='icon_fullscreen')&&(this.id!=='icon_refresh')&&(this.id!=='icon_edit')) {
            $(this).toggleClass('hidden');
        }
    });

    if (!isFullScreen()) {
        // Not yet fullscreen.  Get the max height to the content area
      
        $('#CONTENT').css('max-height', screen.height);
        $('#CONTENT').css('min-height', screen.height);
      
        // And active the fullscreen mode
        requestFullScreen(element || document.documentElement);
    } else { // if (!isFullScreen())
      
        // Reinitialize the height of the content area
        $('#CONTENT').css('max-height', $(window).height()-10);
        $('#CONTENT').css('min-height', $(window).height()-10);
      
        // And exit the fullscreen mode
        exitFullScreen();
    } // if (!isFullScreen())
   
    return true;
   
} // function toggleFullScreen()