function qa_reveal(elem, type, callback) {
	if (elem)
		$(elem).slideDown(400, callback);
}

function qa_conceal(elem, type, callback) {
	if (elem)
		$(elem).slideUp(400);
}

function qa_set_inner_html(elem, type, html) {
	if (elem)
		elem.innerHTML = html;
}

function qa_set_outer_html(elem, type, html) {
	if (elem) {
		var e = document.createElement('div');
		e.innerHTML = html;
		elem.parentNode.replaceChild(e.firstChild, elem);
	}
}

function qa_show_waiting_after(elem, inside) {
	if (elem && !elem.qa_waiting_shown) {
		var w = document.getElementById('qa-waiting-template');

		if (w) {
			var c = w.cloneNode(true);
			c.id = null;

			if (inside)
				elem.insertBefore(c, null);
			else
				elem.parentNode.insertBefore(c, elem.nextSibling);

			elem.qa_waiting_shown = c;
		}
	}
}

function qa_hide_waiting(elem) {
	var c = elem.qa_waiting_shown;

	if (c) {
		c.parentNode.removeChild(c);
		elem.qa_waiting_shown = null;
	}
}

function qa_vote_click(elem) {
	var ens = elem.name.split('_');
	var postid = ens[1];
	var vote = parseInt(ens[2]);
	var code = elem.form.elements.code.value;
	var anchor = ens[3];

	qa_ajax_post('vote', {
			postid: postid,
			vote: vote,
			code: code
		},
		function (lines) {
			if (lines[0] == '1') {
				qa_set_inner_html(document.getElementById('voting_' + postid), 'voting', lines.slice(1).join("\n"));

			} else if (lines[0] == '0') {
				var mess = document.getElementById('errorbox');

				if (!mess) {
					mess = document.createElement('div');
					mess.id = 'errorbox';
					mess.className = 'qa-error';
					mess.innerHTML = lines[1];
					mess.style.display = 'none';
				}

				var postelem = document.getElementById(anchor);
				var e = postelem.parentNode.insertBefore(mess, postelem);
				qa_reveal(e);

			} else
				qa_ajax_error();
		}
	);

	return false;
}

function qa_notice_click(elem) {
	var ens = elem.name.split('_');
	var code = elem.form.elements.code.value;

	qa_ajax_post('notice', {
			noticeid: ens[1],
			code: code
		},
		function (lines) {
			if (lines[0] == '1')
				qa_conceal(document.getElementById('notice_' + ens[1]), 'notice');
			else if (lines[0] == '0')
				alert(lines[1]);
			else
				qa_ajax_error();
		}
	);

	return false;
}

function qa_favorite_click(elem) {
	var ens = elem.name.split('_');
	var code = elem.form.elements.code.value;

	qa_ajax_post('favorite', {
			entitytype: ens[1],
			entityid: ens[2],
			favorite: parseInt(ens[3]),
			code: code
		},
		function (lines) {
			if (lines[0] == '1')
				qa_set_inner_html(document.getElementById('favoriting'), 'favoriting', lines.slice(1).join("\n"));
			else if (lines[0] == '0') {
				alert(lines[1]);
				qa_hide_waiting(elem);
			} else
				qa_ajax_error();
		}
	);

	qa_show_waiting_after(elem, false);

	return false;
}

function qa_ajax_post(operation, params, callback) {
	$.extend(params, {
		qa: 'ajax',
		qa_operation: operation,
		qa_root: qa_root,
		qa_request: qa_request
	});

	$.post(qa_root, params, function (response) {
		var header = 'QA_AJAX_RESPONSE';
		var headerpos = response.indexOf(header);

		if (headerpos >= 0)
			callback(response.substr(headerpos + header.length).replace(/^\s+/, '').split("\n"));
		else
			callback([]);

	}, 'text').fail(function (jqXHR) {
		if (jqXHR.readyState > 0)
			callback([])
	});
}

function qa_ajax_error() {
	alert('Unexpected response from server - please try again or switch off Javascript.');
}

function qa_display_rule_show(target, show, first) {
	var e = document.getElementById(target);
	if (e) {
		if (first || e.nodeName == 'SPAN')
			e.style.display = (show ? '' : 'none');
		else if (show)
			$(e).fadeIn();
		else
			$(e).fadeOut();
	}
}