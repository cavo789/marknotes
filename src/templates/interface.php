<!DOCTYPE html>
<html lang="%LANGUAGE%" >
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta http-equiv="content-language" content="%LANGUAGE%" />
		<title>%SITE_NAME%</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/bootstrap/css/bootstrap.min.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/ajax_loading.css" />

		<link  media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/ionicons/css/ionicons.min.css">

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/github-markdown-css/github-markdown.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/ajax_loading.css" />

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/AdminLTE/css/AdminLTE.min.css">
		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/AdminLTE/css/skins/%SKIN%.min.css">

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%libs/sweetalert2/sweetalert2.min.css">

		<link media="screen" rel="stylesheet" type="text/css" href="%ROOT%templates/assets/css/interface.css" />

  		<!--%ADDITIONNAL_CSS%-->

	</head>

	<body style="background-color: #3c8dbc;" class="hold-transition fixed %SKIN%">

		<div class="hidden wrapper">

			<header class="main-header">

				<!-- logo -->
				<a id="mnLogo" href="https://github.com/cavo789/marknotes" class="logo" title="Download Marknotes on GitHub">
					<span class="logo-mini"><img src="%ROOT%assets/images/marknotes.svg" /></span>
					<span class="logo-lg"><img src="%ROOT%assets/images/marknotes.svg" height="60px"/></span>
				</a>

				<!-- Header Navbar -->
				<nav class="navbar navbar-static-top" role="navigation">
					<!-- Sidebar toggle button-->
					<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
						<span class="sr-only">Toggle navigation</span>
					</a>

					<!--%SEARCH%-->

					<!-- Navbar Right Menu -->
					<div class="navbar-custom-menu">
						<ul class="nav navbar-nav" id="navBar">
							<li class="control-sidebar-button">
								<a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
							</li>
						</ul>
					</div>
				</nav>
			</header>

			<aside class="main-sidebar">
				<section class="sidebar">
					<ul class="sidebar-menu" data-widget="tree">
						<div class="slimScrollBar">
							<div id="TOC">&nbsp;</div>
						</div>
					</ul>
    			</section>
			</aside>

			<div class="content-wrapper">
				<section class="content-header content-headerFixed">
					<h1>&nbsp;</h1>
				</section>
				<section class="content container-fluid">
					<div id="content" class="page content markdown-body">
						<article size="A4" layout="portrait" id="CONTENT">&nbsp;</article>
					</div>
				</section>
			</div>

			<footer class="main-footer">
				<div class="pull-right hidden-xs">%FOOTER_RIGHT%</div>
				%FOOTER_LEFT%
			</footer>

			<!-- Control Sidebar -->
			<aside class="control-sidebar control-sidebar-dark">
				<!-- Create the tabs -->
				<ul class="nav nav-tabs nav-justified control-sidebar-tabs">
					<li class="active"><a href="#control-sidebar-note-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
					<li><a href="#control-sidebar-clipboard-tab" data-toggle="tab"><i class="fa fa-clipboard"></i></a></li>
					<li><a href="#control-sidebar-slideshow-tab" data-toggle="tab"><i class="fa fa-desktop"></i></a></li>
					<li><a href="#control-sidebar-app-tab" data-toggle="tab"><i class="fa fa-sun-o"></i></a></li>
					<li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
				</ul>

				<!-- define tab's content -->
				<div class="tab-content">
					<!-- Note tab content -->
					<div class="tab-pane active" id="control-sidebar-note-tab">
						<span id="control-sidebar-note-tab-export">
							<ul class="control-sidebar-menu" id="control-sidebar-note-tab-export-list">&nbsp;</ul>
						</span>
						<hr/>
						<span id="control-sidebar-note-tab-utility">
							<ul class="control-sidebar-menu" id="control-sidebar-note-tab-utility-list">&nbsp;</ul>
						</span>
					</div>
					<!-- Clipboard -->
					<div class="tab-pane" id="control-sidebar-clipboard-tab">
						<ul class="control-sidebar-menu" id="control-sidebar-clipboard-tab-list">&nbsp;</ul>
					</div>
					<!-- Slideshow -->
					<div class="tab-pane" id="control-sidebar-slideshow-tab">
						<ul class="control-sidebar-menu" id="control-sidebar-slideshow-tab-list">&nbsp;</ul>
					</div>
					<!-- Application -->
					<div class="tab-pane" id="control-sidebar-app-tab">
						<ul class="control-sidebar-menu" id="control-sidebar-app-tab-list">&nbsp;</ul>
					</div>
					<!-- Settings content -->
					<div class="tab-pane" id="control-sidebar-settings-tab">
						<h3 class="control-sidebar-heading">Marknotes v%VERSION%</h3>
						<p>marknotes is an OpenSource software coded and maintained by <a href="https://github.com/cavo789" target="_blank">Christophe Avonture</a>.</p>
						<p>marknotes will transform your notes taken in the markdown format (.md files) into a full featured website.</p>
						<p>Get your copy of marknotes on <a href="%GITHUB%" target="_blank">GitHub <i class="fa fa-github" aria-hidden="true"></i></a></p>
						<p>Click <a href="javascript:fnPluginTaskUpdate()">here</a> to install a newer version of marknotes.</p>
					</div>
				</div>
			</aside>
			<!-- Add the sidebar's background. This div must be placed
			immediately after the control sidebar -->
			<div class="control-sidebar-bg"></div>

		</div>

		<script type="text/javascript" src="%ROOT%libs/jquery/jquery.min.js"></script>

		<script type="text/javascript" src="%ROOT%libs/bootstrap/js/bootstrap.min.js"></script>

		<script type="text/javascript" src="%ROOT%libs/AdminLTE/js/adminlte.min.js"></script>

		<script type="text/javascript" src="%ROOT%libs/jQuery-slimScroll/jquery.slimscroll.min.js"></script>

		<script type="text/javascript" src="%ROOT%libs/noty/jquery.noty.packaged.min.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%libs/sweetalert2/sweetalert2.min.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%libs/js-cookie/js.cookie.js" defer="defer"></script>

		<script type="text/javascript" defer="defer">
			var marknotes = {};
			marknotes.arrPluginsFct = [];
			marknotes.plugins = {};
			marknotes.settings = {};
			marknotes.settings.debug='%DEBUG%';
			marknotes.settings.language='%LANGUAGE%';
			marknotes.settings.show_tips=%SHOW_TIPS%;
			marknotes.settings.version='%VERSION%';
			marknotes.settings.version_url='%VERSION_URL%';
			marknotes.treeview = {};
			marknotes.docs='%ROOT%%DOCS%';
			marknotes.webroot='%ROOT%';
		</script>

		<!--%ADDITIONNAL_JS%-->

		<!--%MARKDOWN_GLOBAL_VARIABLES%-->
		<script type="text/javascript" src="%ROOT%assets/js/ajaxify.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%assets/js/settings.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%templates/assets/js/menu.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%assets/js/marknotes.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%libs/scrolldir/scrolldir.min.js" defer="defer"></script>

		<script type="text/javascript" src="%ROOT%templates/assets/js/interface.js" defer="defer"></script>

	</body>
</html>
