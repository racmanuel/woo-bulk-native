/* global wcBulkToolsCleaner, ajaxurl */
( function( $ ) {
	'use strict';

	var i18n = window.wcBulkToolsCleaner || {};

	function t( key, fallback ) {
		return ( i18n && i18n[ key ] ) ? i18n[ key ] : fallback;
	}

	$( function() {
		var $simulateBtn = $( '#wcbt-dcc-simulate-btn' );
		if ( ! $simulateBtn.length ) {
			return;
		}

		var $executeBtn = $( '#wcbt-dcc-execute-btn' );
		var nonce = $simulateBtn.data( 'nonce' );
		var totalCleanable = 0;

		function showError( message ) {
			$( '#wcbt-dcc-error-text' ).text( message );
			$( '#wcbt-dcc-error' ).show();
		}

		function resetButtons() {
			$simulateBtn.prop( 'disabled', false );
			$executeBtn.prop( 'disabled', totalCleanable <= 0 );
		}

		$simulateBtn.on( 'click', function() {
			var $btn = $( this );
			$btn.prop( 'disabled', true ).text( t( 'simulating', 'Simulando…' ) );
			$( '#wcbt-dcc-error' ).hide();
			$( '#wcbt-dcc-result' ).hide();
			$( '#wcbt-dcc-progress' ).hide();

			$.post( ajaxurl, {
				action: 'wcbt_cleaner_simulate',
				_wpnonce: nonce
			}, function( response ) {
				$btn.prop( 'disabled', false ).text( t( 'simulate', 'Simular limpieza' ) );

				if ( response && response.success ) {
					totalCleanable = response.data.cleanable;
					$( '#wcbt-dcc-total-found' ).text( response.data.total_found );
					$( '#wcbt-dcc-cleanable' ).text( response.data.cleanable );
					$( '#wcbt-dcc-keep' ).text( response.data.keep );
					$( '#wcbt-dcc-stats' ).show();
					$executeBtn.prop( 'disabled', totalCleanable <= 0 );
				} else {
					showError( ( response && response.data ) ? response.data : t( 'errorSimulate', 'Error al simular.' ) );
				}
			} ).fail( function() {
				$btn.prop( 'disabled', false ).text( t( 'simulate', 'Simular limpieza' ) );
				showError( t( 'errorConnection', 'Error de conexión.' ) );
			} );
		} );

		$executeBtn.on( 'click', function() {
			if ( totalCleanable === 0 ) {
				return;
			}

			if ( ! window.confirm( t( 'confirm', '¿Estás seguro de que deseas modificar las categorías de los productos?' ) ) ) {
				return;
			}

			var $btn = $( this );
			$btn.prop( 'disabled', true );
			$simulateBtn.prop( 'disabled', true );
			$( '#wcbt-dcc-error' ).hide();
			$( '#wcbt-dcc-result' ).hide();
			$( '#wcbt-dcc-stats' ).hide();
			$( '#wcbt-dcc-progress' ).show();

			var processed = 0;
			var modified = 0;
			var skipped = 0;
			var total = totalCleanable;
			var batch = 0;

			function processBatch() {
				$.post( ajaxurl, {
					action: 'wcbt_cleaner_batch',
					_wpnonce: nonce,
					batch: batch
				}, function( response ) {
					if ( ! response || ! response.success ) {
						resetButtons();
						showError( ( response && response.data ) ? response.data : t( 'errorBatch', 'Error al procesar lote.' ) );
						return;
					}

					processed += response.data.batch_processed;
					modified += response.data.batch_modified;
					skipped += response.data.batch_skipped;

					var percent = Math.min( 100, Math.round( ( processed / total ) * 100 ) );
					$( '#wcbt-dcc-progress-bar' ).css( 'width', percent + '%' );
					$( '#wcbt-dcc-progress-text' ).text( percent + '%' );
					$( '#wcbt-dcc-proc-processed' ).text( processed );
					$( '#wcbt-dcc-proc-modified' ).text( modified );
					$( '#wcbt-dcc-proc-skipped' ).text( skipped );

					if ( response.data.done ) {
						$( '#wcbt-dcc-result-processed' ).text( processed );
						$( '#wcbt-dcc-result-modified' ).text( modified );
						$( '#wcbt-dcc-result-skipped' ).text( skipped );
						$( '#wcbt-dcc-progress' ).hide();
						$( '#wcbt-dcc-result' ).show();
						$btn.prop( 'disabled', false );
						$simulateBtn.prop( 'disabled', false );
					} else {
						batch++;
						processBatch();
					}
				} ).fail( function() {
					resetButtons();
					showError( t( 'errorBatchConnection', 'Error de conexión durante el procesamiento.' ) );
				} );
			}

			processBatch();
		} );
	} );
} )( jQuery );
