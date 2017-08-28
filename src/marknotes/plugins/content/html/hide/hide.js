/**
 * Triggered when the use click on the Hide treeview button
 */
function fnPluginTaskHideTreeViewDoIt() {

	var html = $("html");
	var sidebar = $("#sidebar");
	var page_wrapper = $("#page-wrapper");
	var content = $("#content");
	var toolbar = $("#toolbar-app");

	if (html.hasClass("sidebar-hidden")) {

		// The treeview is hidden, show it

		content.addClass('content');
		html.removeClass("sidebar-hidden").addClass("sidebar-visible");
		toolbar.removeClass('toolbar-app-left').addClass('toolbar-app-right');

	} else if (html.hasClass("sidebar-visible")) {

		// The treeview is visible, hide it

		html.removeClass("sidebar-visible").addClass("sidebar-hidden");
		content.removeClass('content');
		toolbar.removeClass('toolbar-app-right').addClass('toolbar-app-left');

	} else {

		// First call, the treeview wasn't yet hidden

		if (sidebar.position().left === 0) {

			// The treeview is visible => hide it

			content.removeClass('content');
			html.addClass("sidebar-hidden");
			toolbar.removeClass('toolbar-app-right').addClass('toolbar-app-left');

		} else {

			// The treeview is hidden, show it

			content.addClass('content');
			html.addClass("sidebar-visible");
			toolbar.removeClass('toolbar-app-left').addClass('toolbar-app-right');

		}
	}

	return true;

}
