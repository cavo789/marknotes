<div id="divSearch" class="search_form" data-intro="%INTRO%">
	<input id="search" name="search" type="search" placeholder="%PLACEHOLDER%" class="flexdatalist search_input">
	<button type="submit" class="search_button">
		<svg>
			<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon_search"></use>
		</svg>
	</button>
	<div class="search_option">
		<div title="%RESTRICT%">
			<input type="checkbox" id="search_folder">
			<label for="search_folder">
				<svg class="icon" width="32" height="32" viewBox="0 0 32 32">
					<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon_folder"></use>
				</svg>
			</label>
		</div>
		<div title="%DISABLE_CACHE%" style="display:none;">
			<input type="checkbox" id="search_refresh">
			<label for="search_refresh">
				<svg class="icon" width="32" height="32" viewBox="0 0 32 32">
					<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon_refresh"></use>
				</svg>
			</label>
		</div>
		<div title="%ENABLE_PLUGINS%">
			<input type="checkbox" id="search_plugins">
			<label for="search_plugins">
				<svg class="icon" width="32" height="32" viewBox="0 0 32 32">
					<use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon_plugins"></use>
				</svg>
			</label>
		</div>
	</div>
</div>

<svg xmlns="http://www.w3.org/2000/svg" width="0" height="0" display="none">

	<!--
		For making a symbol from a SVG :
		1. Go to https://github.com/encharm/Font-Awesome-SVG-PNG/tree/master/black/svg
		2. Select an image and click on the RAW button to get the code
		3. Copy the code into https://svg-to-symbol.herokuapp.com/
		4. convert into a symbol
	-->

	<symbol id="icon_search" viewBox="0 0 32 32">
		<path d="M 19.5 3 C 14.26514 3 10 7.2651394 10 12.5 C 10 14.749977 10.810825 16.807458 12.125 18.4375 L 3.28125 27.28125 L 4.71875 28.71875 L 13.5625 19.875 C 15.192542 21.189175 17.250023 22 19.5 22 C 24.73486 22 29 17.73486 29 12.5 C 29 7.2651394 24.73486 3 19.5 3 z M 19.5 5 C 23.65398 5 27 8.3460198 27 12.5 C 27 16.65398 23.65398 20 19.5 20 C 15.34602 20 12 16.65398 12 12.5 C 12 8.3460198 15.34602 5 19.5 5 z"
		/>
	</symbol>

	<symbol id="icon_folder" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg">
		<path d="M1845 931q0-35-53-35H704q-40 0-85.5 21.5T547 970l-294 363q-18 24-18 40 0 35 53 35h1088q40 0 86-22t71-53l294-363q18-22 18-39zM704 768h768V608q0-40-28-68t-68-28H800q-40 0-68-28t-28-68v-64q0-40-28-68t-68-28H288q-40 0-68 28t-28 68v853l256-315q44-53 116-87.5T704 768zm1269 163q0 62-46 120l-295 363q-43 53-116 87.5t-140 34.5H288q-92 0-158-66t-66-158V352q0-92 66-158t158-66h320q92 0 158 66t66 158v32h544q92 0 158 66t66 158v160h192q54 0 99 24.5t67 70.5q15 32 15 68z"
		/>
	</symbol>

	<symbol id="icon_refresh" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
		<path d="M1639 1056q0 5-1 7-64 268-268 434.5T892 1664q-146 0-282.5-55T366 1452l-129 129q-19 19-45 19t-45-19-19-45v-448q0-26 19-45t45-19h448q26 0 45 19t19 45-19 45l-137 137q71 66 161 102t187 36q134 0 250-65t186-179q11-17 53-117 8-23 30-23h192q13 0 22.5 9.5t9.5 22.5zm25-800v448q0 26-19 45t-45 19h-448q-26 0-45-19t-19-45 19-45l138-138q-148-137-349-137-134 0-250 65T460 628q-11 17-53 117-8 23-30 23H178q-13 0-22.5-9.5T146 736v-7q65-268 270-434.5T896 128q146 0 284 55.5T1425 340l130-129q19-19 45-19t45 19 19 45z"
		/>
	</symbol>

	<symbol id="icon_plugins" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
		<path d="M1728 1098q0 81-44.5 135t-123.5 54q-41 0-77.5-17.5t-59-38-56.5-38-71-17.5q-110 0-110 124 0 39 16 115t15 115v5q-22 0-33 1-34 3-97.5 11.5T971 1561t-98 5q-61 0-103-26.5t-42-83.5q0-37 17.5-71t38-56.5 38-59T839 1192q0-79-54-123.5T650 1024q-84 0-143 45.5T448 1197q0 43 15 83t33.5 64.5 33.5 53 15 50.5q0 45-46 89-37 35-117 35-95 0-245-24-9-2-27.5-4t-27.5-4l-13-2q-1 0-3-1-2 0-2-1V512q2 1 17.5 3.5t34 5T137 524q150 24 245 24 80 0 117-35 46-44 46-89 0-22-15-50.5t-33.5-53T463 256t-15-83q0-82 59-127.5T651 0q80 0 134 44.5T839 168q0 41-17.5 77.5t-38 59-38 56.5-17.5 71q0 57 42 83.5T873 542q64 0 180-15t163-17v2q-1 2-3.5 17.5t-5 34-3.5 21.5q-24 150-24 245 0 80 35 117 44 46 89 46 22 0 50.5-15t53-33.5T1472 911t83-15q82 0 127.5 59t45.5 143z"
		/>
	</symbol>

	<symbol id="icon_cog" viewBox="0 0 1792 1792">
		<path d="M1152 896q0-106-75-181t-181-75-181 75-75 181 75 181 181 75 181-75 75-181zm512-109v222q0 12-8 23t-20 13l-185 28q-19 54-39 91 35 50 107 138 10 12 10 25t-9 23q-27 37-99 108t-94 71q-12 0-26-9l-138-108q-44 23-91 38-16 136-29 186-7 28-36 28H785q-14 0-24.5-8.5T749 1634l-28-184q-49-16-90-37l-141 107q-10 9-25 9-14 0-25-11-126-114-165-168-7-10-7-23 0-12 8-23 15-21 51-66.5t54-70.5q-27-50-41-99l-183-27q-13-2-21-12.5t-8-23.5V783q0-12 8-23t19-13l186-28q14-46 39-92-40-57-107-138-10-12-10-24 0-10 9-23 26-36 98.5-107.5T465 263q13 0 26 10l138 107q44-23 91-38 16-136 29-186 7-28 36-28h222q14 0 24.5 8.5T1043 158l28 184q49 16 90 37l142-107q9-9 24-9 13 0 25 10 129 119 165 170 7 8 7 22 0 12-8 23-15 21-51 66.5t-54 70.5q26 50 41 98l183 28q13 2 21 12.5t8 23.5z"
		/>
	</symbol>

</svg>
