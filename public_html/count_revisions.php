<?php
require_once 'common.php';

function count_revisions() {
	global $page_id, $from_rev_id, $to_rev_id;

	$range_clause = "";
	if (isset($from_rev_id) && isset($to_rev_id)) {
		$range_clause = "
			AND revision.rev_timestamp > (SELECT rev_timestamp FROM revision WHERE rev_id = $from_rev_id)
			AND revision.rev_timestamp < (SELECT rev_timestamp FROM revision WHERE rev_id = $to_rev_id)
		";
	}

	if (isset($page_id)) {
		$query = "
			SELECT count(revision.rev_id)
			FROM revision
			JOIN page ON revision.rev_page = page_id
			WHERE page.page_id = $page_id
			{$range_clause}
		";
	}	

	return $query;
}

if (php_sapi_name() !== 'cli' ) {
	echo_query_results(count_revisions());
}
