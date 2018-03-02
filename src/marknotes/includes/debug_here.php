<?php
function here($msg = null, $deep = 3, $return = false) : string
{

	/*<!-- build:debug -->*/
	$pos = 0;
	if ($deep < 1) {
		$deep = 1;
	}

	$debugTrace = debug_backtrace();
	$class = '';
	$file = '';
	$line = '';
	$func = '';
	$txt = '';

	$previous = '';
	for ($i = 0; $i < $deep; $i++) {
		if (isset($debugTrace[$pos + $i])) {
			$file = isset($debugTrace[$pos + $i]['file']) ? $debugTrace[$pos + $i]['file'] : '';
			$line = isset($debugTrace[$pos + $i]['line']) ? $debugTrace[$pos + $i]['line'] : '';
		}
		if (isset($debugTrace[$pos + $i + 1])) {
			$class = isset($debugTrace[$pos + $i + 1]['class'])
				? $debugTrace[$pos + $i + 1]['class'].'::'
				: '';
			$func = isset($debugTrace[$pos + $i + 1]['function'])
				? $debugTrace[$pos + $i + 1]['function'].'()'
				: '';
		}
		if (($line != '') && ($line !== $previous)) {
			$previous = $line;
			$txt .= ($deep > 1?'<li>':'').$class.$func.' in&nbsp;'.$file.' line&nbsp;'.$line.($deep > 1?'</li>':'');
		}
	} // for

	$txt = '<pre style="background-color:yellow;padding:10px">'.
		date('Y-m-d H:i:s').' - '.__METHOD__.
		' called by '.($deep > 1?'<ol>':'').$txt.($deep > 1?'</ol>':'').
		($msg != null?'<div style="padding:10px;border:1px dotted;">'.print_r($msg, true).'</div>':'').
		'</pre>';

	echo $txt;
	/*<!-- endbuild -->*/

	return true;
}
