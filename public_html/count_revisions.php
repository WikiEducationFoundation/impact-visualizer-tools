<?php
require_once 'common.php';

function count_revisions() {
	global $start, $end;
	
	$query = "
		SELECT count(revision.rev_id)
		FROM revision
		JOIN page ON revision.rev_page = page_id
		WHERE page.page_id = 15613
		AND revision.rev_timestamp > (SELECT rev_timestamp FROM revision WHERE rev_id = 1159158915)
		AND revision.rev_timestamp < (SELECT rev_timestamp FROM revision WHERE rev_id = 1161912094)
	";

	return $query;
}

if (php_sapi_name() !== 'cli' ) {
	echo_query_results(count_revisions());
}
