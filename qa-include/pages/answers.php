<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

require_once QA_INCLUDE_DIR . 'db/selects.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'app/q-list.php';


$categoryslugs = qa_request_parts(1);
$countslugs = count($categoryslugs);
$userid = qa_get_logged_in_userid();


// Get list of answers with related questions, plus category information

list($questions, $categories, $categoryid) = qa_db_select_with_pending(
	qa_db_recent_a_qs_selectspec($userid, 0, $categoryslugs),
	qa_db_category_nav_selectspec($categoryslugs, false, false, true),
	$countslugs ? qa_db_slugs_to_category_id_selectspec($categoryslugs) : null
);

if ($countslugs) {
	if (!isset($categoryid))
		return include QA_INCLUDE_DIR . 'qa-page-not-found.php';

	$categorytitlehtml = qa_html($categories[$categoryid]['title']);
	$sometitle = qa_lang_html_sub('main/recent_as_in_x', $categorytitlehtml);
	$nonetitle = qa_lang_html_sub('main/no_answers_in_x', $categorytitlehtml);
} else {
	$sometitle = qa_lang_html('main/recent_as_title');
	$nonetitle = qa_lang_html('main/no_answers_found');
}


// Prepare and return content for theme

return qa_q_list_page_content(
	qa_any_sort_and_dedupe($questions), // questions
	qa_opt('page_size_activity'), // questions per page
	0, // start offset
	null, // total count (null to hide page links)
	$sometitle, // title if some questions
	$nonetitle, // title if no questions
	$categories, // categories for navigation
	$categoryid, // selected category id
	false, // show question counts in category navigation
	'answers/', // prefix for links in category navigation
	qa_opt('feed_for_activity') ? 'answers' : null, // prefix for RSS feed paths (null to hide)
	qa_html_suggest_qs_tags(qa_using_tags(), qa_category_path_request($categories, $categoryid)) // suggest what to do next
);
