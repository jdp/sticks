<?php
/*
 * Sticks Microframework
 * Copyright (c) 2008 Justin Poliey <jdp34@njit.edu>
 * http://github.com/jdp/sticks
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
 
error_reporting(E_ALL);

/* Basic configuration and error checking */
define('SCRIPT_PATH',      "scripts");
define('TEMPLATE_PATH',    "templates");
define('CLASS_PATH',       "classes");
define('DEFAULT_SCRIPT',   "default");
define('DEFAULT_TEMPLATE', "default");
define('ERROR_TEMPLATE',   "_error");

class SticksFramework {
	
	/* Returns a well-formed absolute URL */
	public function url($target) {
		return dirname($_SERVER['PHP_SELF']) . "/{$target}";
	}
	
	public static function handleError($errno, $errstr, $errfile, $errline) {
		while (ob_get_level()) {
			ob_end_clean();
		}
		$error = array(
			"number"  => $errno,
			"message" => $errstr,
			"file"    => $errfile,
			"line"    => $errline
		);
		include(sprintf("%s/%s.html", TEMPLATE_PATH, ERROR_TEMPLATE));
		die();
	}
	
}

set_error_handler("SticksFramework::handleError");

function __autoload($class_name) {
	require_once sprintf("%s/%s.php", CLASS_PATH, strtolower($class_name));
}

$sticks = new SticksFramework();

/* Construct the Sticks request */
$sticks->request = (strlen($_SERVER['QUERY_STRING']) > 0) ? split("/", $_SERVER['QUERY_STRING']) : array(DEFAULT_SCRIPT);

/* Process the requested script */
$sticks->script_file = sprintf("%s/%s.php", SCRIPT_PATH, urldecode($sticks->request[0]));
if (!is_file($sticks->script_file)) {
	trigger_error(sprintf('The requested script <span class="filename">%s</span> does not exist', $sticks->script_file));
}
$sticks->arguments = (count($sticks->request) > 0) ? array_slice($sticks->request, 1) : array();
$sticks->template = DEFAULT_TEMPLATE;
ob_start();
include($sticks->script_file);
$sticks->script_output = ob_get_clean();

/* Process the template */
$sticks->template_file = sprintf("%s/%s.html", TEMPLATE_PATH, $sticks->template);
if (!is_file($sticks->template_file)) {
	trigger_error(sprintf('The template file <span class="filename">%s</span> does not exist', $sticks->template_file));
}
ob_start();
include($sticks->template_file);
$sticks->template_output = ob_get_clean();

/* Render everything */
echo $sticks->template_output;
?>