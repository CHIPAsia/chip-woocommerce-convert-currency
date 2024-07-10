/* To handle hide and show of options in WCC */
jQuery(($) => {
	$( function () {
		// Type of Options
		$( 'select#chip_wcc_options' )
			.on( 'change', function () {
				if ( 'bnm' === $( this ).val() ) {
					$( this ).closest( 'tr' ).next( 'tr' ).hide();
					$( this ).closest( 'tr' ).next().next( 'tr' ).hide();
				} else if ( 'oer' === $( this ).val() ) {
					$( this ).closest( 'tr' ).next( 'tr' ).show();
					$( this ).closest( 'tr' ).next().next( 'tr' ).hide();
				} else {
					$( this ).closest( 'tr' ).next( 'tr' ).hide();
					$( this ).closest( 'tr' ).next().next( 'tr' ).show();
				}
			} )
			.trigger( 'change' );
	} );
});
