<!DOCTYPE html>
<html lang="%LANGUAGE%">

	<head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="content-language" content="%LANGUAGE%" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%SITE_NAME%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/bootstrap/css/bootstrap.min.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/github-markdown-css/github-markdown.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/ajax_loading.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/interface.css" />
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/jquery-toolbar/jquery.toolbar.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/menu.css">

		<link media="print" rel="stylesheet" type="text/css" media="print" href="%ROOT%templates/assets/css/print.css">

		<!--%ADDITIONNAL_CSS%-->

	</head>

	<body class="hidden">

		<div id="sidebar" class="onlyscreen sidebar">

			<a id="mnLogo" href="https://github.com/cavo789/marknotes" target="_blank" title="Download Marknotes on GitHub"><img src="%ROOT%assets/images/marknotes.png" class="logo" /></a>

			<div id="toolbar-app" data-toolbar="style-option" class="btn-toolbar btn-toolbar-default"><i class="fa fa-bars"></i></div>

			<div id="toolbar-app-options" class="hidden btn-toolbar-warning">
			    <div id="toolbar-app-icons" class="fa-1x"></div>
			</div>

			<!--%SEARCH%-->

			<div id="TOC">&nbsp;</div>

			<div class="app_version">
				<a id="mnWebsite" href="https://www.marknotes.fr/" target="_blank"><span data-i18n="app_name" /></a>
			</div>

		</div>

		<div id="page-wrapper" class="page-wrapper">

			<!-- The hamburger for the content menu -->
			<div id="toolbar-content" class="menu">
				<span class="menu-circle"></span>
				<a href="#" class="menu-link">
				<span class="menu-icon">
				<span class="menu-line menu-line-1"></span>
				<span class="menu-line menu-line-2"></span>
				<span class="menu-line menu-line-3"></span>
				</span>
				</a>
			</div>

			<!-- Container for the icons. Will be feed up by -->
			<!-- assets/js/menu.js, fnInterfaceInitContentButtons() -->
			<div id="toolbar-content-icons" class="menu-overlay"></div>

			<div id="content" class="page content markdown-body">
				<article size="A4" layout="portrait" id="CONTENT">&nbsp;</article>
			</div>

		</div>

		<footer class="onlyprint">&nbsp;</footer>

		<script type="text/javascript" src="%ROOT%libs/jquery/jquery.min.js"></script>

		<script type="text/javascript" src="%ROOT%libs/bootstrap/js/bootstrap.min.js"></script>

		<!-- For nice user alerts (informations, warning, ...) -->
		<script type="text/javascript" src="%ROOT%libs/noty/jquery.noty.packaged.min.js" defer="defer"></script>

		<!-- jquery-toolbar -->
		<script type="text/javascript" src="%ROOT%libs/jquery-toolbar/jquery.toolbar.min.js" defer="defer"></script>

		<script type="text/javascript" defer="defer">
			var marknotes = {};
			marknotes.arrPluginsFct = [];
			marknotes.plugins = {};
			marknotes.settings = {};
			marknotes.settings.debug='%DEBUG%';
			marknotes.settings.language='%LANGUAGE%';
			marknotes.treeview = {};
			marknotes.docs='%ROOT%%DOCS%';
			marknotes.webroot='%ROOT%';
		</script>

		<!--%ADDITIONNAL_JS%-->

		<!--%MARKDOWN_GLOBAL_VARIABLES%-->
		<script type="text/javascript" src="%ROOT%assets/js/ajaxify.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%templates/assets/js/menu.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%assets/js/marknotes.js" defer="defer"></script>
		<script type="text/javascript" defer="defer">

			// Called by the i18n plugin when that plugin is enabled
		  	function fnTranslateInterface(params) {
				try {

					// This function should only be fired once
					// So, now, remove it from the arri18nFct array
					marknotes.arri18nFct.splice(marknotes.arri18nFct.indexOf('fnTranslateInterface'), 1);

					$('#mnLogo').prop('title', $.i18n('app_download'));
					$('#mnWebsite').prop('href', $.i18n('app_website'));
					$('body').i18n();
				} catch (err) {
					console.warn(err.message);
				}

				return true;
			}

			$(document).ready(function () {

				$("#toolbar-content").click(function (e) {
					// Based on https://github.com/tobiasahlin/animated-menu
					e.preventDefault();
					$(".menu").toggleClass("open");
					$(".menu-overlay").toggleClass("open");
				});

				initializeTasks();

				if (typeof marknotes.arri18nFct !== "undefined") {
					marknotes.arri18nFct.push("fnTranslateInterface");
				}

				$("body").removeClass("hidden");

			});
		</script>

	</body>

</html>
