<?php

function get_db() {
	global $username, $password, $db_name, $wiki_name;

	$settings = parse_ini_file("../replica.my.cnf", true);

	$hostname = "{$wiki_name}.web.db.svc.wikimedia.cloud";
	$username = $settings['client']['user'];
	$password = $settings['client']['password'];
	$db_name = "{$wiki_name}_p";

	$db = new mysqli($hostname, $username, $password, $db_name);
	if ($db->connect_errno > 0) {
	  die ("Cannot connect to database");
	}
	$db->set_charset('utf8');
	return $db;
}

function escape_and_quote($str) {
  global $db;
  return "'{$db->escape_string($str)}'";
}

// db-escape a list and join with commas
function escape_implode($args) {
  return implode(',', array_map('escape_and_quote', $args));
}

function load_wiki_name($query_array) {
	global $language, $project, $database, $wiki_name;

	$language = empty($query_array["lang"]) ? "en" : $query_array["lang"];
	$project = empty($query_array["project"]) ? "wikipedia" : $query_array["project"];
	$database = empty($query_array["db"]) ? "" : $query_array["db"];

	// Abort if we received garbage.
	if ( !preg_match('/^[a-z_]+$/', $language )
	  || !preg_match('/^[a-z]+$/', $project )
	  || !preg_match('/^[a-z_]*$/', $database )
	) {
	  exit;
	}

	$project_map = array(
	  'wikibooks' => 'wikibooks',
	  'wikinews' => 'wikinews',
	  'wikipedia' => 'wiki',
	  'wikiquote' => 'wikiquote',
	  'wikisource' => 'wikisource',
	  'wikiversity' => 'wikiversity',
	  'wikivoyage' => 'wikivoyage',
	  'wiktionary' => 'wiktionary',
	);
	$short_project = $project_map[$project];

	// We set database name directly if received as a valid parameter
	if ( !empty($database) ) {
	  $wiki_name = $database;
	} else {
	  $wiki_name = $language . $short_project;
	}
}

function load_parameters($query_array) {
	global $page_id, $from_rev_id, $to_rev_id;

	if(isset($query_array["page_id"])) {
	  $page_id = escape_and_quote($query_array["page_id"]);
	}

	if(isset($query_array["from_rev_id"])) {
	  $from_rev_id = escape_and_quote($query_array["from_rev_id"]);
	}

	if(isset($query_array["to_rev_id"])) {
	  $to_rev_id = escape_and_quote($query_array["to_rev_id"]);
	}
}

function echo_query_results($query) {
	global $db;
	$result = $db->query($query);

	if ($result === false) {
		echo '{ "success": false, "data": [], "query": "' . $query . '" }';
		return;
	}

	$jsonData = json_encode($result->fetch_all(MYSQLI_ASSOC));

	echo '{ "success": true, "data": ' . $jsonData . ' }';
}

// Main.
if (php_sapi_name() !== 'cli') {
	global $db;
	load_wiki_name($_GET);
	$db = get_db();
	load_parameters($_GET);
	// Control flow continues in endpoint module.
}
