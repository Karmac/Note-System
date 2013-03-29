// Plugin que nos ayudará a manejar las preticiones.
$.customRequest = function(action, data, events) {
	// Creamos los eventos por defecto.
	var events = $.extend({
		onSuccess:	function() {}
	}, events);

	// Realizamos la petición.
	$.ajax({
		url:		'ajax.php?action=' + action,
		dataType:	'JSON',
		type:		'POST',
		data:		data,
		beforeSend:	function() {
			// Eliminamos todos los mensajes activos y activamos el mensaje de carga.
			$('.alert.error, .alert.success').remove();
			$('.loading').show();
		},
		error:		function(jqXHR) {
			// Eliminamos todos los mensajes y mostramos el error.
			$('.alert.error, .alert.success').remove();
			$('<div class="alert error">').html(jqXHR.responseText)
										  .prependTo('body')
										  .animate({ 'margin-top': '0px' })
										  .delay(2000)
										  .animate({ 'margin-top': '-33px' });
		},
		success:	function(result) {
			$('.alert.error, .alert.success').remove();
			// La petición se completó y el archivo respondió con un mensaje de éxito.
			if (result.success) {
				$('<div class="alert success">').html(result.success)
												.prependTo('body')
												.animate({ 'margin-top': '0px' })
												.delay(2000)
												.animate({ 'margin-top': '-33px' });
			// La petición se completó y el archivo devolvió una cadena con los datos de las notas.
			} else {
				// El plugin de templating creará un bucle automáticamente al pasarle el vector de datos.
				$('#note-template').tmpl(result).hide()
								   .appendTo('#note-list')
								   .slideDown();

				// Actualizamos el ordenado ya que hemos añadido nuevas notas.
				$('#note-list').sortable('refresh');
			}
			// Llamamos a nuestra función opcional.
			events.onSuccess();
		},
		complete:	function() {
			// Ocultamos el mensaje de carga.
			$('.loading').hide();
		}
	});
}

$(function() {
	
	/** Habilitamos el ordenado. **/
	$('#note-list').sortable({
		axis:					'y',
		handle:					'#sort-handle',
		placeholder:			'placeholder',
		forcePlaceholderSize:	true,
		update:					function() {
			var positions = $('#note-list').sortable('toArray');
			// Eliminar "nota-" del ID.
			positions = $.map(positions, function(value) {
				return value.replace('note-', '');
			});

			$.customRequest('rearrange', {
				positions: positions
			});
		}
	});

	/** Cargamos todas las notas. **/
	$.customRequest('getAll');

	/** Añadir una nueva nota. **/
	$('#add-note').click(function() {
		$.customRequest('add');
	});

	/** Eliminar una nota. **/
	$('#delete-note').live('click', function(event) {
		event.preventDefault();

		var $note = $(this).parents('.note');

		$.customRequest(
			'delete',
			{ id: $note.data('note-id') },
			{ onSuccess: function() { $note.slideUp(function() { $(this).remove() }); } }
		);
	});

	/** Editar una nota. **/
	$('#edit-note').live('click', function(event) {
		event.preventDefault();

		var $note_wrapper	= $(this).parent().siblings('.note-wrapper'),
			$note_text		= $note_wrapper.find('.note-text'),
			$note_actions	= $note_wrapper.siblings('.note-actions');

		// Cancelar las demás ediciones.
		$('.note #discard-changes').trigger('click');

		// Hacer la nota editable, guardar el texto actual y cambiar los botones.
		$note_wrapper.addClass('editable');
		$note_text.attr('contentEditable', 'true')
				  .focus();

		if (!$note_text.data('original-text')) {
			$note_text.attr('data-original-text', $note_text.html())
		}
		
		$note_actions.html($('#actions-active-template').tmpl());
	});

	/** Cancelar la edición. **/
	$('#discard-changes').live('click', function(event) {
		event.preventDefault();

		var $note_wrapper	= $(this).parent().siblings('.note-wrapper'),
			$note_text		= $note_wrapper.find('.note-text'),
			$note_actions	= $note_wrapper.siblings('.note-actions');

		// Hacer la nota no editable, restaurar el texto y los botones.
		$note_wrapper.removeClass('editable');
		$note_text.attr('contentEditable', 'false')
				  .html($note_text.data('original-text'))
				  .removeAttr('data-original-text');
		$note_actions.html($('#actions-default-template').tmpl());
	});

	/** Confirmar la edición. **/
	$('#save-changes').live('click', function(event) {
		event.preventDefault();

		var $note			= $(this).parents('.note'),
			$note_wrapper	= $note.find('.note-wrapper'),
			$note_text		= $note.find('.note-text'),
			$note_actions	= $note.find('.note-actions');

		// Guardamos la nota.
		$.customRequest(
			'edit',
			{ id: $note.data('note-id'), text: $note_text.text() },
			{
				onSuccess:	function() {
					$note_text.removeAttr('data-original-text');
					// Hacer la nota no editable y restaurar los botones.
					$note_wrapper.removeClass('editable');
					$note_text.attr('contentEditable', 'false');
					$note_actions.html($('#actions-default-template').tmpl());
				}
			}
		);
	});

});