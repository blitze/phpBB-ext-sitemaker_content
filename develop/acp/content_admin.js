(function($, window, document) {
	'use strict';

	var fNameObj = {};
	var fLabelObj = {};
	var typeObj = {};
	var nTypeObj = {};
	var containerObj = {};
	var view = 'summary';
	var views = ['summary', 'detail'];
	var textarea = {};
	var preview = {};

	var CodeMirror = window.CodeMirror || {};
	var postData = window.postData || {};
	var trans = window.trans || {};
	var Twig = window.Twig || {};

	var makeOptionsSortable = function(element) {
		element.sortable({
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			dropOnEmpty: true
		}).disableSelection();
	};

	var addFieldOption = function($element) {
		var fieldName = $element.attr('id').substring(11);
		var ocontainer = $('#' + fieldName + '-options-container');
		var itemsCount = ocontainer.children().length;

		ocontainer.append(Twig.twig({ ref: 'option' }).render({
			field_name: fieldName,
			type: $element.data('type'),
			index: itemsCount + 1
		}));
	};

	var toggleFieldOptionDefaultsType = function($element) {
		var type = $element.is(':checked') ? 'checkbox' : 'radio';
		var fieldName = $element.attr('id').substring(13);

		$('#add-option-' + fieldName).data('type', type);
		$('#' + fieldName + '-options-container')
			.find('.field-defaults')
				.attr('type', type);
	};
	
	var makeOptionsTogglable = function($element) {
		console.log($element);
		$element
			.find('select[data-togglable-settings]')
			.each(function() {
				var $this = $(this);

				$this.change(function() {
					window.phpbb.toggleSelectSettings($this);
				});
				window.phpbb.toggleSelectSettings($this);
			});
	};

	var addField = function(fieldType, fieldName, fieldLabel) {
		var items = containerObj.children().length;
		var data = {
			'field_data': {
				'field': {
					'field_name': fieldName,
					'field_type': fieldType,
					'field_label': fieldLabel
				}
			}
		};

		$.getJSON(window.ajaxUrl + '&type=' + fieldType, data, function(row) {
			var $field = $(row);
			containerObj.append($field).accordion('refresh').accordion('option', 'active', items);
			$('html,body').animate({ scrollTop: $field.offset().top }, 500, 'swing');
			makeOptionsSortable(containerObj.find('#' + fieldName + '-options-container'));
			makeOptionsTogglable($field);
			setAvailableFields();
		});
	};

	var editField = function(field) {
		var tObj = $('#' + field + '-field_type');
		var el = nTypeObj.children('option:selected');

		var nType = el.val();
		var oType = tObj.val();

		if (oType !== nType) {
			var row = $('#cfield-' + field);
			var index = row.index();
			var data = row.find(':input').serializeArray();

			$.getJSON(window.ajaxUrl + '&type=' + nType, data, function(newRow) {
				row.replaceWith(newRow);
				containerObj.accordion('refresh').accordion('option', 'active', index);
				$('html,body').animate({ scrollTop: $('#cfield-' + field).offset().top }, 500, 'swing');
				setAvailableFields();
			});
		}
	};

	var removeElement = function(el) {
		el.parent().slideUp('slow').remove();
		return false;
	};

	var setAvailableFields = function() {
		var buttons = [];

		$('#fields-container .field_label[value != ""]').each(function() {
			var label = $(this).val();
			var field = $(this).next().val().toUpperCase();
			var ftype = $(this).next().next().val();

			buttons.push('<a class="button" href="#" data-tag="' + field + '" data-ftype="' + ftype + '">' + label + '</a>');
		});

		$('#available-fields').html(buttons.join()).children('.button').button({ disabled: false });
	};

	var showTab = function(tab) {
		var panelId = $(tab).attr('href');

		// we set the view for the summary|detail templates 
		view = panelId.split('-')[0].substr(1);
		view = (views.indexOf(view) < 0) ? 'summary' : view;

		$(tab).parent().addClass('activetab').siblings('li').removeClass('activetab');
		$(panelId).show().siblings().hide();
	};

	var checkRequired = function() {
		var name = $('#content-name').val();
		var buttonObj = $('#fieldsubmit');
		var missingLabels = 0;

		$('.field_label').each(function() {
			if (!$(this).val().length) {
				missingLabels++;
			}
		});

		if (missingLabels > 0 || !name) {
			var tab = (!name) ? 'ctype' : 'cfields';
			$('#content-tabs a[href^="#' + tab + '"]').trigger('click');
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

		return !!(error1 || error2);
	};

	var getEditor = function(view) {
		return CodeMirror.fromTextArea($('#tpl-custom-' + view).get(0), {
			theme: 'monokai',
			mode: 'html',
			lineNumbers: true,
			lineWrapping: false,
			autoRefresh: true,
			styleActiveLine: true,
			fixedGutter: true,
			indentUnit: 4,
			indentWithTabs: true,
			coverGutterNextToScrollbar: false
		});
	};

	$(document).ready(function() {
		var removeObj = {};
		var aButtons = {};
		var cButtons = {};
		var eButtons = {};
		var fid = '';

		// collect all templates
		$('.tpl').each(function() {
			Twig.twig({
				id: $(this).attr('id').substring('14'),
				data: $(this).html()
			});
		});

		fNameObj = $('#dialog-fieldname');
		fLabelObj = $('#dialog-fieldlabel');
		typeObj = $('#dialog-fieldtype');
		nTypeObj = $('#dialog-newtype');
		containerObj = $('#fields-container');

		aButtons[trans.addField] = function() {
			var fieldName = fNameObj.val();
			var fieldType = typeObj.val();
			var fieldLabel = fLabelObj.val();

			if (!checkField(fieldName, fieldLabel)) {
				addField(fieldType, fieldName, fieldLabel);
				containerObj.sortable('refresh');
				$(this).dialog('close');
			}
		};

		aButtons[trans.cancel] = function() {
			$(this).dialog('close');
		};

		cButtons[trans.deleteField] = function() {
			removeElement(removeObj);
			containerObj.sortable('refresh');
			$(this).dialog('close');
			setAvailableFields();
		};

		cButtons[trans.cancel] = function() {
			$(this).dialog('close');
		};

		eButtons[trans.editField] = function() {
			editField(fid);
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
			fid = $(this).data('field');
			$('#dialog-newtype').val($('#' + fid + '-field_type').val());
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
			addFieldOption($(this));
			$(this).blur();
			e.preventDefault();
		});

		containerObj.on('click', 'input.toggle-multi', function() {
			toggleFieldOptionDefaultsType($(this));
		});

		containerObj.on('blur', '.field_label', function() {
			setAvailableFields();
		});

		containerObj.on('keyup', '.field-option input[type="text"]', function() {
			$(this).prev().val($(this).val());
		});

		containerObj.on('click', 'input.field-defaults[type=radio]', function() {
			// Credit: http://smoothprogramming.com/jquery/toggle-radio-button-using-jquery/
			var previousValue = $(this).data('storedValue');
			if (previousValue) {
				$(this).prop('checked', !previousValue);
				$(this).data('storedValue', !previousValue);
			} else {
				$(this).data('storedValue', true);
				$("input.field-defaults[type=radio]:not(:checked)").data("storedValue", false);
			}
		});

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
					setAvailableFields();
				}
			});

		makeOptionsSortable(containerObj.find('.field-options-list'));

		var $topicBlocksInput = $('#input-topic-blocks');
		var topicBlocksSortableOptions = {
			connectWith: '.topic-blocks',
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			dropOnEmpty: true
		};

		$('#available-topic-block-options').sortable(topicBlocksSortableOptions);

		topicBlocksSortableOptions.update = function() {
			var items = [];
			$(this).find('li').each(function() {
				items.push($(this).data('service'));
			});
			$topicBlocksInput.val(items.join(','));
		};
		$('#selected-topic-blocks').sortable(topicBlocksSortableOptions);

		$('body').on('click', '.toggle', function(e) {
			var id = $(this).attr('id');
			$('#s' + id).slideToggle();
			$(this).blur();
			e.preventDefault();
		});

		$('.fieldLabel, #content-name').change(function() {
			checkRequired();
		});

		$('#fieldsubmit').click(function() {
			checkRequired();
		});

		// editor
		$.each(views, function(i, view) {
			preview[view] = $('#preview-' + view);
			textarea[view] = getEditor(view);

			textarea[view].on('change', function() {
				preview[view].html(Twig.twig({ data: textarea[view].getValue() }).render(postData));
			});
		});

		$('#post-info > a.button').button({ disabled: false }).click(function(e) {
			e.preventDefault();
			textarea[view].replaceSelection('{{ ' + $(this).data('tag') + ' }}');
		});

		$('#available-fields').on('click', 'a.button', function(e) {
			e.preventDefault();
			var field = '{{ ' + $(this).data('tag').toUpperCase() + ' }}';

			textarea[view].focus();
			textarea[view].replaceSelection(field);
		}).children('a.button').button({ disabled: false });

		// Tabs for content type
		$('.ctab').click(function(e) {
			e.preventDefault();
			showTab(this);
		});

		var $loadingIndicator;
		$(document).ajaxStart(function() {
			$loadingIndicator = window.phpbb.loadingIndicator();
		}).ajaxStop(function() {
			window.phpbb.clearLoadingTimeout();
			if ($loadingIndicator) {
				$loadingIndicator.fadeOut(window.phpbb.alertTime);
			}
		});
	});
})(jQuery, window, document);
