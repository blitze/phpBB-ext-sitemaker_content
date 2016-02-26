;(function($, window, document, undefined) {
	'use strict';

	var fNameObj = {};
	var fLabelObj = {};
	var typeObj = {};
	var nTypeObj = {};
	var containerObj = {};
	var editor = {};

	var ace = window.ace || {};
	var postData = window.postData || {};
	var trans = window.trans || {};
	var twig = window.twig || {};

	var addFieldOption = function(fieldName) {
		var ocontainer = $('#' + fieldName + '-options-container');
		var multi = ocontainer.parent().find('.field-multi').is(':checked');

		ocontainer.append('<div class="field-option">' + twig({ref: 'option'}).render({'field_name': fieldName}) + '</div>').find('.field-defaults').attr('type', (multi) ? 'checkbox' : 'radio');

		return false;
	};

	var setField = function(fieldType, fieldTypeLabel, fieldName, fieldLabel) {
		var items = containerObj.children().length;
		var data = {
			'field_type': fieldType,
			'type_label': fieldTypeLabel,
			'field_name': fieldName,
			'field_label': fieldLabel,
			'settings': ''
		};

		data.settings += twig({ref: 'settings'}).render(data);
		if (twig({ref: fieldType}) !== null) {
			data.settings += twig({ref: fieldType}).render(data);
		}

		containerObj.append(twig({ref: 'row'}).render(data)).accordion('refresh').accordion('option', 'active', items);
		$('html,body').animate({scrollTop: $('#cfield-' + fieldName).offset().top}, '32000');
	};

	var editField = function(field) {
		if (field) {
			var tObj = $('#' + field + '-type');
			var el = nTypeObj.children('option:selected');

			var nType = el.val();
			var oType = tObj.val();

			if (oType !== nType) {
				var ops = ['radio', 'checkbox', 'select'];
				var obj = $('#' + field + '-header');
				var currOps = $('#' + field + '-options-container').html();

				// remove options
				$('#' + field + '-options').remove();

				if (twig({ref: nType}) !== null) {
					$('#' + field + '-panel').append(twig({ref: nType}).render({'field_name': field}));

					// if new type is radio/checkbox/select, replace already entered options
					if (ops && $.inArray(nType, ops) > -1) {
						$('#' + field + '-options-container').html(currOps).find('.field-defaults').attr('type', (nType === 'checkbox') ? 'checkbox' : 'radio');
					}
				}

				tObj.val(nType);
				obj.children('.field-type').text('[ ' + el.text() + ' ]');
				containerObj.accordion('option', 'active', containerObj.children().index(obj.parent()));
				$('html,body').animate({scrollTop: $('#cfield-' + field).offset().top}, '32000');
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
			fields.push('<p><b>' + label + ':</b> {{ ' + field + ' }}</p>');
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
		var name = $('#content_name').val();
		var buttonObj = $('#fieldsubmit');
		var missingLabels = 0;

		$('.field_label').each(function() {
			if (!$(this).val().length) {
				missingLabels++;
			}
		});

		if (missingLabels > 0 || !name) {
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

	$(document).ready(function() {
		var removeObj = {};
		var aButtons = {};
		var cButtons = {};
		var eButtons = {};
		var fid = '';

		// collect all templates
		$('.tpl').each(function() {
			twig({
				id: $(this).attr('id').substring('14'),
				data: $(this).html()
			});
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

		cButtons[trans.remove] = function() {
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
		containerObj.on('click', 'a.edit-field', function() {
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
			$(this).parent().parent().find('.field-defaults').attr('type', $(this).is(':checked') ? 'checkbox' : 'radio');
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
				preview[view].html(twig({data: textarea[view].value}).render(postData));
			});
		});

		$('#post-info > a.button').button({disabled: false}).click(function(e) {
			e.preventDefault();
			editor[view].insert('{{ ' + $(this).attr('tag') + ' }}');
		});

		$('#available-fields').on('click', 'a.button', function(e) {
			e.preventDefault();
			var field = '{{ ' + $(this).text().toUpperCase() + ' }}';
			var ftype = $(this).attr('ftype');
			editor[view].focus();

			if (ftype === 'image') {
				editor[view].insert('<img class="cms-teaser-medium-image" src="' + field + '" />');
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