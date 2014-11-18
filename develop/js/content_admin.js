;(function($, window, document, undefined) {
	'use strict';

	var fNameObj = {};
	var fLabelObj = {};
	var typeObj = {};
	var nTypeObj = {};
	var tplData = {};
	var containerObj = {};
	var editor = {};

	var addFieldOption = function(fieldName) {
		var str = tplData.option;
		var ocontainer = $('#' + fieldName + '-options-container');
		var multi = ocontainer.prev().is(':checked');

		str = str.replace(/\[fieldName\]/gi, fieldName);
		ocontainer.append('<div class="field-option">' + str + '</div>').find('.field-defaults').attr('type', (multi) ? 'checkbox' : 'radio');

		return false;
	};

	var setField = function(fieldType, fieldTypeLabel, fieldName, fieldLabel) {

		var str = tplData.settings;
		var items = containerObj.children().length;

		if (tplData[fieldType] !== undefined) {
			str += tplData[fieldType];
		}

		str = str.replace(/\[fieldName\]/gi, fieldName);
		str = str.replace(/\[fieldLabel\]/gi, fieldLabel);

		var s = '';
		s += '<div id="cfield-' + fieldName + '" class="field">';
		s += '	<h3 id="' + fieldName + '-header">' + fieldLabel;
		s += '		<span class="small field-type">[ ' + fieldTypeLabel + ' ]</span>';
		s += '	</h3>';
		s += '	<div id="' + fieldName + '-panel">' + str + '</div>';
		s += '	<div class="field-actions">';
		s += '		<a href="#" class="remove-field field-icon ui-dialog-titlebar-close ui-corner-all" title="' + trans.delete + '"><span class="ui-icon ui-icon-closethick">' + trans.delete + '</span></a>';
		s += '		<a href="#" class="edit-field field-icon ui-dialog-titlebar-close ui-corner-all" title="' + trans.edit + '"><span class="ui-icon ui-icon-gear">' + trans.edit + '</span></a>';
		s += '	</div>';
		s += '	<input type="hidden" id="' + fieldName + '-type" name="fdata[' + fieldName + '][type]" value="' + fieldType + '" />';
		s += '</div>';

		containerObj.append(s).accordion('refresh').accordion('option', 'active', items);
		$('html,body').animate({scrollTop: $('#cfield-' + fieldName).offset().top}, '32000');
	};

	var editField = function(fid) {
		if (fid) {
			var tObj = $('#' + fid + '-type');
			var el = nTypeObj.children('option:selected');

			var nType = el.val();
			var oType = tObj.val();

			if (oType !== nType) {
				var ops = ['radio', 'checkbox', 'select'];
				var obj = $('#' + fid + '-header');
				var currOps = $('#' + fid + '-options-container').html();

				// remove options
				$('#' + fid + '-options').remove();

				if (tplData[nType] !== undefined) {
					$('#' + fid + '-panel').append(tplData[nType].replace(/\[fieldName\]/gi, fid));

					// if new type is radio/checkbox/select, replace already entered options
					if (ops && $.inArray(nType, ops) > -1) {
						$('#' + fid + '-options-container').html(currOps).find('.field-defaults').attr('type', (nType === 'checkbox') ? 'checkbox' : 'radio');
					}
				}

				tObj.val(nType);
				obj.children('.field-type').text('[ ' + el.text() + ' ]');
				containerObj.accordion('option', 'active', containerObj.children().index(obj.parent()));
				$('html,body').animate({scrollTop: $('#cfield-' + fid).offset().top}, '32000');
			}
		}
	};

	var removeElement = function(el) {
		el.parent().slideUp('slow').remove();
		return false;
	};

	var setAvailableFields = function() {
		var fields = [];
		var buttons = [];

		$('#fields-container .fieldLabel[value != ""]').each(function() {
			var label = $(this).val();
			var field = $(this).next().val().toUpperCase();
			var ftype = $(this).parents('.ui-accordion-content').next().val();

			buttons.push('<a class="button" href="' + label + '" title="' + label + '" ftype="' + ftype + '">' + field + '</a>');
			fields.push('<p><b>' + label + ':</b> {' + field + '}</p>');
		});

		$('#available-fields').html(buttons.join('&nbsp;')).children('.button').button({disabled: false});

		return false;
	};

	var showTab = function(id) {
		$('.ctab').parent().removeClass('activetab');
		$('#' + id).parent().attr('class', 'activetab');
		$('.ctabcontent').hide();
		$('#c' + id).show();
		return false;
	};

	var checkRequired = function() {
		var labels = '';
		var name = $('#content_name').val();
		var buttonObj = $('#fieldsubmit');

		$('.fieldLabel').each(function() {
			labels = labels + $(this).val();
		});

		if (!labels || !name) {
			showTab(((!name) ? 'ctype' : 'cfields'));
			buttonObj.attr('disabled', 'disabled').removeClass('button1');
		} else {
			buttonObj.removeAttr('disabled').addClass('button1');
		}
	};

	var checkField = function(fieldName, fieldLabel) {
		$('#dialog-add-field .error').remove();
		var error1 = (!fieldName) ? trans.fieldName : (($('#cfield-' + fieldName).length > 0) ? trans.taken : '');
		var error2 = (!fieldLabel) ? trans.fieldLabel : '';

		if (error1) {
			$('<div class="error">' + error1 + '</div>').insertAfter(fNameObj);
		}

		if (error2) {
			$('<div class="error">' + error2 + '</div>').insertAfter(fLabelObj);
		}

		return (error1 || error2) ? true : false;
	};

	var parseTpl = function(str) {
		for (var key in postData) {
			var token = '{' + key + '}';
			if (str.indexOf(token) > -1) {
				str = str.replace(token, postData[key]);
			}
		}

		return str;
	};

	$(document).ready(function() {
		var removeObj = {};
		var aButtons = {};
		var cButtons = {};
		var eButtons = {};
		var fid = '';
		var fieldType = '';
		var editorLang = '';
		var editorId = '';

		// collect all templates
		$('.tpl').each(function() {
			var tpl = $(this).attr('id').substring('14');
			tplData[tpl] = $(this).html();
		});

		fNameObj = $('#dialog-fieldname');
		fLabelObj = $('#dialog-fieldlabel');
		typeObj = $('#dialog-fieldtype');
		nTypeObj = $('#dialog-newtype');
		containerObj = $('#fields-container');

		aButtons[trans.add] = function() {
			var fieldName = fNameObj.val();
			var fieldType = typeObj.val();
			var fieldLabel = fLabelObj.val();
			var fieldTypeLabel = typeObj.children('option:selected').text();

			if (!checkField(fieldName, fieldLabel)) {
				setField(fieldType, fieldTypeLabel, fieldName, fieldLabel);
				containerObj.sortable('refresh');
				$(this).dialog('close');
				setAvailableFields();
			}
		};

		cButtons[trans.delete] = function() {
			removeElement(removeObj);
			containerObj.sortable('refresh');
			$(this).dialog('close');
			setAvailableFields();
		};

		eButtons[trans.edit] = function() {
			editField(fid);
			$(this).dialog('close');
		};

		aButtons[trans.cancel] = function() {
			$(this).dialog('close');
		};

		cButtons[trans.cancel] = function() {
			$(this).dialog('close');
		};

		eButtons[trans.cancel] = function() {
			$(this).dialog('close');
		};

		$('#dialog-add-field, #dialog-edit-field, #dialog-confirm-delete').dialog({
			autoOpen: false,
			width: 350,
			modal: true,
			show: 'slide',
			hide: 'slide'
		});

		var dialogAdd = $('#dialog-add-field');
		$('#add-field').button().click(function(e) {
			e.preventDefault();
			fNameObj.val('');
			fLabelObj.val('');
			$('#dialog-add-field .error').remove();

			dialogAdd.dialog('option', 'buttons', aButtons);
			dialogAdd.dialog('open');
		});

		var dialogEdit = $('#dialog-edit-field');
		containerObj.on('click', 'a.edit-field', function(e) {
			fid = $('#' + $(this).parent().prev().attr('id') + ' input[type="hidden"]').val();
			$('#dialog-newtype').val($(this).parent().next().val());
			dialogEdit.dialog('option', 'buttons', eButtons);
			dialogEdit.dialog('open');
		});

		var dialogConfirm = $('#dialog-confirm-delete');
		containerObj.on('click', 'a.remove-field', function(e) {
			e.preventDefault();
			removeObj = $(this).parent();
			if ($(this).hasClass('db-field')) {
				dialogConfirm.dialog('option', 'buttons', cButtons);
				dialogConfirm.dialog('open');
			} else {
				removeElement(removeObj);
				setAvailableFields();
			}
		});

		containerObj.on('click', 'a.remove-option', function(e) {
			removeElement($(this));
			e.preventDefault();
		});

		containerObj.on('click', 'a.add-option', function(e) {
			addFieldOption($(this).attr('id').substring('11'));
			$(this).blur();
			e.preventDefault();
		});

		var stop = false;

		containerObj.accordion({
			header: '> div > h3',
			active: false,
			collapsible: true,
			heightStyle: 'content'
		})
		.sortable({
			handle: 'h3',
			cursor: 'move',
			axis: 'y',
			opacity: 0.6,
			forcePlaceholderSize: true,
			placeholder: 'ui-state-highlight',
			stop: function(event, ui) {
				// IE doesn't register the blur when sorting
				// so trigger focusout handlers to remove .ui-state-focus
				ui.item.children('h3').triggerHandler('focusout');
				stop = true;
				setAvailableFields();
			}
		});

		$('#fields-container').on('blur', '.fieldLabel', function() {
			setAvailableFields();
		}).on('keyup', '.field-option input[type="text"]', function() {
			$(this).prev().val($(this).val());
		}).on('click', '.field-multi', function() {
			$(this).next().find('.field-defaults').attr('type', $(this).is(':checked') ? 'checkbox' : 'radio');
		});

		$('body').on('click', '.toggle', function(e) {
			var id = $(this).attr('id');
			$('#s' + id).slideToggle();
			$(this).blur();
			e.preventDefault();
		});

		$('.fieldLabel, #content_name').change(function() {
			checkRequired();
		});

		$('#fieldsubmit').click(function() {
			checkRequired();
		});

		// ace editor
		var view = 'summary';
		var views = ['summary', 'detail'];
		var textarea = {};
		var preview = {};

		$.each(views, function(i, view) {
			editor[view] = ace.edit(view + '-editor');
			textarea[view] = document.getElementById('tpl-custom-' + view);
			preview[view] = $('#preview-' + view);

			textarea[view].style.display = 'none';

			editor[view].setTheme('ace/theme/clouds_midnight');
			editor[view].getSession().setMode('ace/mode/html');
			editor[view].getSession().setValue(textarea[view].value);
			editor[view].setShowPrintMargin(false);
			editor[view].getSession().on('change', function() {
				textarea[view].value = editor[view].getSession().getValue();
				preview[view].html(parseTpl(textarea[view].value));
			});
		});

		$('#post-info > a.button').button({disabled: false}).click(function(e) {
			e.preventDefault();
			var tag = $(this).attr('tag');
			editor[view].insert('{' + tag + '}');
		});

		$('#available-fields').on('click', 'a.button', function(e) {
			e.preventDefault();
			var field = '{' + $(this).text().toUpperCase() + '}';
			var ftype = $(this).attr('ftype');
			editor[view].focus();

			if (ftype === 'image') {
				editor[view].insertHtml('<img class="cms-teaser-medium-image" src="' + field + '" />');
			} else {
				editor[view].insert(field);
			}
		}).children('a.button').button({disabled: false});

		// Tabs for field templates
		$('#fields-template').on('click', '.toggle-tpl', function(e) {
			var id = $(this).attr('id');
			$(this).removeClass('ui-state-active').parent().children('li').not(this).addClass('ui-state-active');
			$('.toggle-panel').hide();
			$('#' + id + '-tpl').show();
			view = id.substring('7');
			editor[view].resize();
			e.preventDefault();
		});

		// Tabs for content type
		$('.ctab').click(function(e) {
			var id = $(this).attr('id');
			showTab(id);
			e.preventDefault();
		});
	});
})(jQuery, window, document);
