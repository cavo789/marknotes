<!DOCTYPE html>
<html lang="%LANGUAGE%">

	<head>

		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="robots" content="%ROBOTS%" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8;" />

		<title>%SITE_NAME% | %APP_NAME%</title>

		<!--%META_DATA%-->
		<!--%FAVICON%-->

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/animatecss/3.5.2/animate.min.css" />
		<link rel="stylesheet" href="%ROOT%/libs/jquery-albe-timeline/style-albe-timeline.css" />

		<style>
			h1 { position:fixed; }
		</style>

	</head>

    <body>

        <h1>%SITE_NAME%</h1>

        <div id="divTimeline">&nbsp;</div>

        <script src="%ROOT%/libs/jquery/jquery.min.js"></script>
        <script src="%ROOT%/libs/store/store.everything.min.js"></script>
        <script src="%ROOT%/assets/js/marknotes.js"></script>
        <script src="%ROOT%/libs/jquery-albe-timeline/jquery-albe-timeline.min.js"></script>

        <!--%MARKDOWN_GLOBAL_VARIABLES%-->

        <script>

          $(document)
              .ready(function () {

                ajaxify({
                    filename: 'timeline.json',
                    callback: 'ShowTimeline(data)',
                    useStore: marknotes.settings.use_localcache
                });


          }); // $( document ).ready()

          function ShowTimeline($data) {
            $.fn.albeTimeline.languages = [{
                "en-US": {
                    days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    shortMonths: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    msgEmptyContent: "No information to display."
                },
                "fr-FR": {
                    days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
                    months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
                    shortMonths: ["Jan","Fév","Mar","Avr","Mai","Juin", "Juil","Août","Sep","Oct","Nov","Déc"],
                    msgEmptyContent: "Aucune information à afficher."
                }
            }];

            $("#divTimeline").albeTimeline($data, {
              'effect': 'zoomIn',
              'showMenu': true,
              //sortDesc: true,
              'language': marknotes.settings.locale
            });
          }

        </script>

    </body>

</html>
