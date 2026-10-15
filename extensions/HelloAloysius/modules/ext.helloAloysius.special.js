/**
 * Special:Hello: make the numbers count up from 0.
 * Without JavaScript the real numbers are already on the page.
 */
( function () {
	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}
	document.querySelectorAll( '.hello-num[data-target]' ).forEach( function ( el, i ) {
		var target = Number( el.getAttribute( 'data-target' ) );
		var duration = 1400;
		var start = null;
		var step = function ( now ) {
			if ( start === null ) {
				start = now;
			}
			var t = Math.min( 1, ( now - start ) / duration );
			var eased = 1 - Math.pow( 1 - t, 3 );
			el.textContent = mw.language.convertNumber( Math.round( target * eased ) );
			if ( t < 1 ) {
				requestAnimationFrame( step );
			}
		};
		el.textContent = mw.language.convertNumber( 0 );
		setTimeout( function () {
			requestAnimationFrame( step );
		}, 150 + i * 90 );
	} );
}() );
