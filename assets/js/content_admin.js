(function($){
	var fname_obj = {}, flabel_obj = {}, type_obj = {}, ntype_obj = {}, tpl_data = {}, container_obj = {}, editor = {};

	var add_field_option = function(field_name) {
		var str = tpl_data.option;
		str = str.replace(/\[field_name\]/gi, field_name);

		$('#'+field_name+'-options-container').append('<div class="field-option">' + str + '</div>');
		return false;
	};

	var set_field = function(field_type, l_field_type, field_name, field_label) {

		var str = tpl_data.settings;
		var items = container_obj.children().length;

		if (field_type === 'form.field.radio' || field_type === 'form.field.checkbox' || field_type === 'form.field.select') {
			str += tpl_data[field_type.substring('11')];
		}

		str = str.replace(/\[field_name\]/gi, field_name);
		str = str.replace(/\[field_label\]/gi, field_label);

		var s = '';
		s += '<div id="cfield-' + field_name + '" class="field">';
		s += '	<h3 id="' + field_name + '-header">' + field_label;
		s += '		<span class="small field-type">[ ' + l_field_type + ' ]</span>';
		s += '	</h3>';
		s += '	<div id="' + field_name + '-panel">' + str + '</div>';
		s += '	<div class="field-actions">';
		s += '		<a href="#" class="remove-field field-icon ui-dialog-titlebar-close ui-corner-all" title="' + l_delete + '"><span class="ui-icon ui-icon-closethick">' + l_delete + '</span></a>';
		s += '		<a href="#" class="edit-field field-icon ui-dialog-titlebar-close ui-corner-all" title="' + l_edit + '"><span class="ui-icon ui-icon-gear">' + l_edit + '</span></a>';
		s += '	</div>';
		s += '	<input type="hidden" id="' + field_name + '-type" name="fdata[' + field_name + '][type]" value="' + field_type + '" />';
		s += '</div>';

		container_obj.append(s).accordion('refresh').accordion('option', 'active', items);
		$('html,body').animate({scrollTop: $("#cfield-" + field_name).offset().top}, '32000');
	};

	var edit_field = function(fid) {
		if (fid) {
			var t_obj = $('#'+fid+'-type');
			var el = ntype_obj.children('option:selected');

			var ntype = el.val();
			var otype = t_obj.val();

			if (otype !== ntype) {
				var ops = ['form.field.radio', 'form.field.checkbox', 'form.field.select'];
				var obj = $('#'+fid+'-header');

				if ($.inArray(ntype, ops) >= 0 && $.inArray(otype, ops) < 0) {
					$('#' + fid + '-panel').append(tpl_data[ntype.substring('11')].replace(/\[field_name\]/gi, fid));
				} else if ($.inArray(ntype, ops) < 0 && $.inArray(otype, ops) >= 0) {
					$('#' + fid + '-options').remove();
				}	

				t_obj.val(ntype);
				obj.children('.field-type').text('[ ' + el.text() + ' ]');
				container_obj.accordion('option', 'active', container_obj.children().index(obj.parent()));
				$('html,body').animate({scrollTop: $("#cfield-" + fid).offset().top}, '32000');
			}
		}
	};

	var remove_element = function(el) {
		el.parent().slideUp('slow').remove();
		return false;
	};

	var set_available_fields = function() {
		var fields = [], buttons = [];
		$('#fields-container .field_label[value != ""]').each(function(){
			var label = $(this).val();
			var field = $(this).next().val().toUpperCase();
			var ftype = $(this).parents('.ui-accordion-content').next().val();

			buttons.push('<a class="button" href="'+label+'" title="'+label+'" ftype="'+ftype+'">'+field+'</a>');
			fields.push('<p><b>'+label+':</b> {'+field+'}</p>');
		});

		$('#available-fields').html(buttons.join('&nbsp;')).children('.button').button({disabled: true});

		return false;
	};

	var show_tab = function(id) {
		$('.ctab').parent().removeClass('activetab');
		$('#'+id).parent().attr('class', 'activetab');
		$('.ctabcontent').hide();
		$('#c'+id).show();
		return false;
	};

	var check_required = function() {
		var labels = '';
		var name = $('#content_name').val();
		var buttonObj = $('#fieldsubmit');

		$('.field_label').each(function() {
			labels = labels + $(this).val();
		});

		if (!labels || !name) {
			show_tab(((!name) ? 'ctype' : 'cfields'));
			buttonObj.attr("disabled", "disabled").removeClass('button1');
		} else {
			buttonObj.removeAttr("disabled").addClass('button1');
		}
	};

	var check_field = function(field_name, field_label) {
		$('#dialog-add-field .error').remove();
		var error1 = (!field_name) ? l_fieldname : (($('#cfield-' + field_name).length > 0) ? l_taken : '');
		var error2 = (!field_label) ? l_fieldlabel : '';

		if (error1) {
			$('<div class="error">'+error1+'</div>').insertAfter(fname_obj);
		}

		if (error2) {
			$('<div class="error">'+error2+'</div>').insertAfter(flabel_obj);
		}

		return (error1 || error2) ? true : false;
	};

	$(document).ready(function() {
		var remove_obj = {}, abuttons = {}, cbuttons = {}, ebuttons = {}, fid = '', field_type = '', editor_lang = '', editor_id = '';

		$('.tpl').each(function() {
			var tpl = $(this).attr('id').substring('14');
			tpl_data[tpl] = $(this).html();
		});

		fname_obj = $('#dialog-fieldname');
		flabel_obj = $('#dialog-fieldlabel');
		type_obj = $('#dialog-fieldtype');
		ntype_obj = $('#dialog-newtype');
		container_obj = $('#fields-container');
		
		abuttons[l_add] = function() {
			var field_name = fname_obj.val();
			var field_type = type_obj.val();
			var field_label = flabel_obj.val();
			var l_field_type = type_obj.children('option:selected').text();

			if (!check_field(field_name, field_label)) {
				set_field(field_type, l_field_type, field_name, field_label);
				container_obj.sortable('refresh');
				$(this).dialog('close');
				set_available_fields();
			}
		};

		cbuttons[l_delete] = function() {
			remove_element(remove_obj);
			container_obj.sortable('refresh');
			$(this).dialog('close');
			set_available_fields();
		};

		ebuttons[l_edit] = function() {
			edit_field(fid);
			$(this).dialog('close');
		};

		abuttons[l_cancel] = function() {
			$(this).dialog('close');
		};

		cbuttons[l_cancel] = function() {
			$(this).dialog('close');
		};

		ebuttons[l_cancel] = function() {
			$(this).dialog('close');
		};

		$('#dialog-add-field, #dialog-edit-field, #dialog-confirm-delete').dialog({
			autoOpen: false,
			width: 350,
			modal: true,
			show: 'slide',
			hide: 'slide'
		});

		var dialog_add = $('#dialog-add-field');
		$('#add-field').button().click(function(e) {
			e.preventDefault();
			fname_obj.val('');
			flabel_obj.val('');
			$('#dialog-add-field .error').remove();

			dialog_add.dialog('option', 'buttons', abuttons);
			dialog_add.dialog('open');
		});

		var dialog_edit = $('#dialog-edit-field');
		container_obj.on('click', 'a.edit-field', function(e) {
			fid = $('#' + $(this).parent().prev().attr('id') + ' input[type="hidden"]').val();
			$('#dialog-newtype').val($(this).parent().next().val());
			dialog_edit.dialog('option', 'buttons', ebuttons);
			dialog_edit.dialog('open');
		});

		var dialog_confirm = $('#dialog-confirm-delete');
		container_obj.on('click', 'a.remove-field', function(e) {
			e.preventDefault();
			remove_obj = $(this).parent();
			if ($(this).hasClass('db-field')) {
				dialog_confirm.dialog('option', 'buttons', cbuttons);
				dialog_confirm.dialog('open');
			} else {
				remove_element(remove_obj);
				set_available_fields();
			}
		});

		container_obj.on('click', 'a.remove-option', function(e) {
			remove_element($(this));
			e.preventDefault();
		});

		container_obj.on('click', 'a.add-option', function(e) {
			add_field_option($(this).attr('id').substring('11'));
			$(this).blur();
			e.preventDefault();
		});

		var stop = false;

		container_obj.accordion({
			header: "> div > h3",
			active: false,
			collapsible: true,
			heightStyle: "content"
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
				set_available_fields();
			}
		});

		$('#fields-container').on('blur', '.field_label', function() {
			set_available_fields();
		}).on('blur', '.field-option input[type="text"]', function() {
			$(this).prev().val($(this).val());
		});

		$('body').on('click', '.toggle', function(e) {
			var id = $(this).attr('id');
			$('#s'+id).slideToggle();
			$(this).blur();
			e.preventDefault();
		});

		$('.field_label, #content_name').change(function() {
			check_required();
		});

		$('#fieldsubmit').click(function() {
			check_required();
		});

		// ace editor
		var view = 'summary';
		var views = ['summary', 'detail'];
		var textarea = {}, preview = {};
		$.each(views, function(i, view) {
			editor[view] = ace.edit(view + "-editor");
			textarea[view] = document.getElementById('tpl-custom-' + view);
			preview[view] = $('#preview-' + view);

			textarea[view].style.display = 'none';

			editor[view].setTheme("ace/theme/clouds_midnight");
			editor[view].getSession().setMode("ace/mode/html");
			editor[view].getSession().setValue(textarea[view].value);
			editor[view].setShowPrintMargin(false);
			editor[view].getSession().on('change', function() {
				textarea[view].value = editor[view].getSession().getValue();
				preview[view].html(textarea[view].value);
			});
		});

		$('#post-info > a.button').button({disabled: false}).click(function(e) {
			e.preventDefault();
			var tag = $(this).attr('tag');
			editor[view].insert('{'+tag+'}');
		});

		$('#available-fields').on('click', 'a.button', function(e) {
			e.preventDefault();
			var field = '{' + $(this).text().toUpperCase() + '}';
			var ftype = $(this).attr('ftype');
			editor[view].focus();

			if (ftype === 'image') {
				editor[view].insertHtml('<img class="cms-teaser-medium-image" src="'+field+'" />');
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
			show_tab(id);
			e.preventDefault();
		});
	});
})(jQuery);