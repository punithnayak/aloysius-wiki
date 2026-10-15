// 1. Say hello every time a page opens
mw.notify( 'Hello, ' + mw.config.get( 'wgUserName' ) + '! Your script is running 🎉' );

mw.loader.using( 'mediawiki.util' ).then( function () {

	// 2. Your own link in the Tools menu
	mw.util.addPortletLink( 'p-tb', mw.util.getUrl( 'Special:Random' ), '🎲 Surprise me', 't-surprise' );

	// 3. Party mode: a link that switches the disco on and off
	var party = mw.util.addPortletLink( 'p-tb', '#', '🪩 Party mode', 't-party' );
	party.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		document.body.classList.toggle( 'party' );
	} );

	// 4. Page facts: ask MediaWiki about this page
	var facts = mw.util.addPortletLink( 'p-tb', '#', '📊 Page facts', 't-facts' );
	facts.addEventListener( 'click', function ( event ) {
		event.preventDefault();
		mw.notify(
			'Page: ' + mw.config.get( 'wgPageName' ) +
			' · Revision: ' + mw.config.get( 'wgRevisionId' ) +
			' · Your edits: ' + mw.config.get( 'wgUserEditCount' )
		);
	} );
} );
