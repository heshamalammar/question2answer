<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

require_once QA_INCLUDE_DIR . 'db/selects.php';
require_once QA_INCLUDE_DIR . 'app/format.php';
require_once QA_INCLUDE_DIR . 'app/q-list.php';


// Check that we're logged in

$userid = qa_get_logged_in_userid();

if (!isset($userid))
	qa_redirect('login');


// Find out which updates to show

$forfavorites = qa_get('show') != 'content';
$forcontent = qa_get('show') != 'favorites';


// Get lists of recent updates for this user

$questions = qa_db_select_with_pending(
	qa_db_user_updates_selectspec($userid, $forfavorites, $forcontent)
);

if ($forfavorites) {
	if ($forcontent) {
		$sometitle = qa_lang_html('misc/recent_updates_title');
		$nonetitle = qa_lang_html('misc/no_recent_updates');
	} else {
		$sometitle = qa_lang_html('misc/recent_updates_favorites');
		$nonetitle = qa_lang_html('misc/no_updates_favorites');
	}
} else {
	$sometitle = qa_lang_html('misc/recent_updates_content');
	$nonetitle = qa_lang_html('misc/no_updates_content');
}


// Prepare and return content for theme

$qa_content = qa_q_list_page_content(
	qa_any_sort_and_dedupe($questions),
	null, // questions per page
	0, // start offset
	null, // total count (null to hide page links)
	$sometitle, // title if some questions
	$nonetitle, // title if no questions
	array(), // categories for navigation
	null, // selected category id
	null, // show question counts in category navigation
	null, // prefix for links in category navigation
	null, // prefix for RSS feed paths (null to hide)
	$forfavorites ? strtr(qa_lang_html('misc/suggest_update_favorites'), array(
		'^1' => '<a href="' . qa_path_html('favorites') . '">',
		'^2' => '</a>',
	)) : null // suggest what to do next
);

$qa_content['navigation']['sub'] = array(
	'all' => array(
		'label' => qa_lang_html('misc/nav_all_my_updates'),
		'url' => qa_path_html('updates'),
		'selected' => $forfavorites && $forcontent,
	),

	'favorites' => array(
		'label' => qa_lang_html('misc/nav_my_favorites'),
		'url' => qa_path_html('updates', array('show' => 'favorites')),
		'selected' => $forfavorites && !$forcontent,
	),

	'myposts' => array(
		'label' => qa_lang_html('misc/nav_my_content'),
		'url' => qa_path_html('updates', array('show' => 'content')),
		'selected' => $forcontent && !$forfavorites,
	),
);


return $qa_content;
