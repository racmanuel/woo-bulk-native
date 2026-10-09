/* global wcBulkToolsSearch */
( function( $ ) {
	'use strict';

	var SELECTOR       = 'ul.cat-checklist';
	var WRAP_CLASS     = 'wc-becs-wrap';
	var ROW_CLASS      = 'wc-becs-row';
	var INPUT_CLASS    = 'wc-becs-search';
	var CLEAR_CLASS    = 'wc-becs-clear';
	var META_CLASS     = 'wc-becs-meta';
	var HIDDEN_CLASS   = 'wc-becs-hidden';
	var TEXT_DATA_KEY  = 'wcBecsText';
	var TIMER_DATA_KEY = 'wcbtTimer';
	var BULK_TITLES_SELECTOR = '#bulk-titles';
	var BULK_SEARCH_WRAP_CLASS = 'wcbt-bulk-titles-search';
	var BULK_SEARCH_INPUT_CLASS = 'wcbt-bulk-titles-input';
	var BULK_SEARCH_CLEAR_CLASS = 'wcbt-bulk-titles-clear';
	var BULK_SEARCH_META_CLASS = 'wcbt-bulk-titles-meta';
	var BULK_SEARCH_EMPTY_CLASS = 'wcbt-bulk-titles-empty';
	var BULK_TIMER_DATA_KEY = 'wcbtBulkTitlesTimer';
	var DEBOUNCE_MS    = 120;

	var i18n = window.wcBulkToolsSearch || {};

	function t( key, fallback ) {
		return ( i18n && i18n[ key ] ) ? i18n[ key ] : fallback;
	}

	function format( tpl, value ) {
		return ( tpl || '' ).replace( '%s', value );
	}

	function normalize( str ) {
		var value = ( str || '' ).toString().toLowerCase();

		return value.normalize ? value.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' ) : value;
	}

	/**
	 * Devuelve el checklist asociado a un contenedor, calculado siempre desde
	 * el DOM actual (nunca desde referencias capturadas al construir la UI).
	 */
	function getChecklist( $wrap ) {
		return $wrap.next( SELECTOR ).first();
	}

	function getTaxonomyLabel( $checklist ) {
		var $label = $checklist.prevAll( 'span.inline-edit-categories-label' ).first();
		if ( $label.length ) {
			return ( $label.text() || '' ).toString().trim();
		}
		return '';
	}

	function getLabelText( $li ) {
		var $label = $li.children( 'label.selectit' );
		if ( $label.length ) {
			return $label.text();
		}
		return $li.text();
	}

	function describe( label ) {
		if ( label ) {
			return format( t( 'descriptionTpl', 'Escribe para filtrar %s' ), label );
		}
		return t( 'descriptionGeneric', 'Escribe para filtrar' );
	}

	function ensureIndexed( $checklist ) {
		$checklist.find( 'li' ).each( function() {
			var li = this;
			if ( li.dataset && li.dataset[ TEXT_DATA_KEY ] ) {
				return;
			}
			var txt = normalize( getLabelText( $( li ) ) );
			if ( li.dataset ) {
				li.dataset[ TEXT_DATA_KEY ] = txt;
			}
		} );
	}

	function setAllVisible( $checklist ) {
		$checklist.find( 'li' ).removeClass( HIDDEN_CLASS );
	}

	function revealAncestors( $li ) {
		$li.parents( 'li' ).removeClass( HIDDEN_CLASS );
	}

	function revealDescendants( $li ) {
		$li.find( 'li' ).removeClass( HIDDEN_CLASS );
	}

	function setMeta( $wrap, text ) {
		$wrap.find( '.' + META_CLASS ).first().text( text );
	}

	function applyFilter( $wrap, query ) {
		var $checklist = getChecklist( $wrap );
		if ( ! $checklist.length ) {
			return;
		}

		var label = getTaxonomyLabel( $checklist );

		ensureIndexed( $checklist );
		query = normalize( query ).trim();

		if ( ! query ) {
			setAllVisible( $checklist );
			setMeta( $wrap, describe( label ) );
			return;
		}

		var $all = $checklist.find( 'li' );
		$all.addClass( HIDDEN_CLASS );

		var matches = 0;
		$all.each( function() {
			var li = this;
			var hay = li.dataset ? ( li.dataset[ TEXT_DATA_KEY ] || '' ) : '';
			if ( hay && hay.indexOf( query ) !== -1 ) {
				matches++;
				revealAncestors( $( li ) );
				revealDescendants( $( li ) );
				$( li ).removeClass( HIDDEN_CLASS );
			}
		} );

		if ( matches === 0 ) {
			setMeta( $wrap, t( 'noResults', 'Sin resultados' ) );
		} else {
			setMeta( $wrap, t( 'results', '%d resultados' ).replace( '%d', matches.toString() ) );
		}
	}

	function buildUI( $checklist ) {
		// Solo se omite si este checklist ya tiene su propio contenedor.
		if ( $checklist.prev( '.' + WRAP_CLASS ).length ) {
			return;
		}

		var label = getTaxonomyLabel( $checklist );
		var placeholder = label
			? format( t( 'placeholderTpl', 'Buscar en %s...' ), label )
			: t( 'placeholderGeneric', 'Buscar...' );

		var $wrap = $( '<div />', { 'class': WRAP_CLASS } );
		var $row = $( '<div />', { 'class': ROW_CLASS } );
		var $input = $( '<input />', {
			type: 'search',
			'class': INPUT_CLASS,
			placeholder: placeholder,
			spellcheck: false
		} );
		// Importante: no pasar `autocomplete: 'off'` en el objeto anterior porque
		// jQuery lo trata como una llamada a método si jQuery UI Autocomplete está cargado.
		$input.attr( 'autocomplete', 'off' );

		var $clear = $( '<button />', {
			type: 'button',
			'class': 'button ' + CLEAR_CLASS,
			text: t( 'clear', 'Limpiar' )
		} );
		var $meta = $( '<div />', { 'class': META_CLASS, text: describe( label ) } );

		$row.append( $input, $clear );
		$wrap.append( $row, $meta );
		$checklist.before( $wrap );
	}

	function buildIn( $ctx ) {
		$ctx.find( SELECTOR ).each( function() {
			buildUI( $( this ) );
		} );
	}

	function getBulkTitlesList( $titles ) {
		return $titles.find( '.ntdelitem' );
	}

	function getBulkTitlesText( $item ) {
		return ( $item.find( '.ntdeltitle' ).first().text() || '' ).toString().trim();
	}

	function setBulkTitlesMeta( $wrap, text ) {
		$wrap.find( '.' + BULK_SEARCH_META_CLASS ).first().text( text );
	}

	function getBulkTitlesDescription( count ) {
		return t( 'bulkTitlesDescription', '%d productos seleccionados' ).replace( '%d', count.toString() );
	}

	function applyBulkTitlesFilter( $wrap, query ) {
		var $titles = $wrap.next( BULK_TITLES_SELECTOR ).first();
		if ( ! $titles.length ) {
			return;
		}

		var $items = getBulkTitlesList( $titles );
		var normalizedQuery = normalize( query ).trim();
		var matches = 0;

		$items.each( function() {
			var $item = $( this );
			var isMatch = ! normalizedQuery || normalize( getBulkTitlesText( $item ) ).indexOf( normalizedQuery ) !== -1;

			$item.toggleClass( HIDDEN_CLASS, ! isMatch );
			if ( isMatch ) {
				matches++;
			}
		} );

		var $empty = $wrap.find( '.' + BULK_SEARCH_EMPTY_CLASS ).first();
		$empty.toggle( matches === 0 );
		setBulkTitlesMeta(
			$wrap,
			normalizedQuery && matches === 0
				? t( 'bulkTitlesNoResults', 'No hay productos coincidentes' )
				: getBulkTitlesDescription( $items.length )
		);
	}

	function buildBulkTitlesUI( $titles ) {
		if ( $titles.prev( '.' + BULK_SEARCH_WRAP_CLASS ).length ) {
			return;
		}

		var $wrap = $( '<div />', { 'class': BULK_SEARCH_WRAP_CLASS } );
		var $input = $( '<input />', {
			type: 'search',
			'class': BULK_SEARCH_INPUT_CLASS,
			'aria-label': t( 'bulkTitlesLabel', 'Buscar productos seleccionados' ),
			placeholder: t( 'bulkTitlesPlaceholder', 'Buscar productos seleccionados...' ),
			spellcheck: false
		} );
		var $clear = $( '<button />', {
			type: 'button',
			'class': 'button ' + BULK_SEARCH_CLEAR_CLASS,
			text: t( 'clear', 'Limpiar' )
		} );
		var $meta = $( '<div />', {
			'class': BULK_SEARCH_META_CLASS,
			text: getBulkTitlesDescription( getBulkTitlesList( $titles ).length )
		} );
		var $empty = $( '<p />', {
			'class': BULK_SEARCH_EMPTY_CLASS,
			text: t( 'bulkTitlesNoResults', 'No hay productos coincidentes' ),
			role: 'status',
			'aria-live': 'polite'
		} ).hide();

		$wrap.append( $( '<div />', { 'class': ROW_CLASS } ).append( $input, $clear ), $meta, $empty );
		$titles.before( $wrap );
	}

	function buildBulkTitlesIn( $ctx ) {
		$ctx.find( BULK_TITLES_SELECTOR ).each( function() {
			buildBulkTitlesUI( $( this ) );
		} );
	}

	function buildAll() {
		buildIn( $( '#inline-edit, #bulk-edit, #the-list' ) );
		buildBulkTitlesIn( $( '#bulk-edit' ) );
	}

	function scheduleFilter( $input ) {
		var $wrap = $input.closest( '.' + WRAP_CLASS );
		if ( ! $wrap.length ) {
			return;
		}

		clearTimeout( $input.data( TIMER_DATA_KEY ) );
		$input.data( TIMER_DATA_KEY, setTimeout( function() {
			applyFilter( $wrap, $input.val() );
		}, DEBOUNCE_MS ) );
	}

	function scheduleBulkTitlesFilter( $input ) {
		var $wrap = $input.closest( '.' + BULK_SEARCH_WRAP_CLASS );
		if ( ! $wrap.length ) {
			return;
		}

		clearTimeout( $input.data( BULK_TIMER_DATA_KEY ) );
		$input.data( BULK_TIMER_DATA_KEY, setTimeout( function() {
			applyBulkTitlesFilter( $wrap, $input.val() );
		}, DEBOUNCE_MS ) );
	}

	function init() {
		buildAll();

		// Handlers delegados en `document`: imprescindibles porque WordPress y
		// WooCommerce clonan filas completas del listado (y jQuery.clone copia los
		// handlers directos, dejando clausuras que apuntan a nodos que ya no son
		// los que el usuario está editando). Con delegación siempre se resuelve el
		// input y su checklist desde el DOM actual.
		$( document ).on( 'input', '.' + INPUT_CLASS, function() {
			scheduleFilter( $( this ) );
		} );

		$( document ).on( 'input', '.' + BULK_SEARCH_INPUT_CLASS, function() {
			scheduleBulkTitlesFilter( $( this ) );
		} );

		$( document ).on( 'click', '.' + CLEAR_CLASS, function( event ) {
			event.preventDefault();

			var $input = $( this ).closest( '.' + WRAP_CLASS ).find( '.' + INPUT_CLASS ).first();
			$input.val( '' );
			applyFilter( $input.closest( '.' + WRAP_CLASS ), '' );
			$input.trigger( 'focus' );
		} );

		$( document ).on( 'click', '.' + BULK_SEARCH_CLEAR_CLASS, function( event ) {
			event.preventDefault();

			var $input = $( this ).closest( '.' + BULK_SEARCH_WRAP_CLASS ).find( '.' + BULK_SEARCH_INPUT_CLASS ).first();
			$input.val( '' );
			applyBulkTitlesFilter( $input.closest( '.' + BULK_SEARCH_WRAP_CLASS ), '' );
			$input.trigger( 'focus' );
		} );

		$( document ).on( 'click', '#bulk-titles .ntdelbutton', function() {
			var $titles = $( this ).closest( BULK_TITLES_SELECTOR );
			var $wrap = $titles.prev( '.' + BULK_SEARCH_WRAP_CLASS );

			setTimeout( function() {
				if ( $wrap.length ) {
					applyBulkTitlesFilter( $wrap, $wrap.find( '.' + BULK_SEARCH_INPUT_CLASS ).val() );
				}
			}, 0 );
		} );

		// La fila de edición masiva se reconstruye al aplicarla: se vuelve a
		// construir la UI si aparece algún checklist sin su contenedor.
		$( document ).on( 'click', '#doaction, #doaction2', function() {
			setTimeout( buildAll, 0 );
		} );

		// Al abrir la edición rápida, la fila inline se inserta en la tabla.
		$( document ).on( 'click', '.editinline', function() {
			setTimeout( buildAll, 0 );
		} );
	}

	$( init );
} )( jQuery );
