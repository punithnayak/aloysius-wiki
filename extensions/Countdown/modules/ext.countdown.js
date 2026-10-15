/**
 * Countdown: PHP drew the boxes once. This keeps them ticking.
 */
( function () {
	var boxes = document.querySelectorAll( '.cd[data-to]' );
	if ( !boxes.length ) {
		return;
	}

	function pad( n ) {
		var s = mw.language.convertNumber( n );
		return n < 10 ? mw.language.convertNumber( 0 ) + s : s;
	}

	function tick() {
		var now = Date.now();
		boxes.forEach( function ( box ) {
			if ( box.classList.contains( 'is-done' ) ) {
				return;
			}
			var left = Math.max( 0, Math.floor( ( Number( box.getAttribute( 'data-to' ) ) - now ) / 1000 ) );
			var values = {
				days: Math.floor( left / 86400 ),
				hours: Math.floor( left % 86400 / 3600 ),
				minutes: Math.floor( left % 3600 / 60 ),
				seconds: left % 60
			};
			box.querySelectorAll( '.cd-num' ).forEach( function ( el ) {
				var text = pad( values[ el.getAttribute( 'data-unit' ) ] );
				if ( el.textContent !== text ) {
					el.textContent = text;
					el.classList.remove( 'is-tick' );
					void el.offsetWidth; // restart the animation
					el.classList.add( 'is-tick' );
				}
			} );
			if ( left === 0 ) {
				box.classList.add( 'is-done' );
				var done = document.createElement( 'div' );
				done.className = 'cd-done-text';
				done.textContent = box.getAttribute( 'data-done' );
				box.insertBefore( done, box.querySelector( '.cd-when' ) );
			}
		} );
	}
	tick();
	setInterval( tick, 1000 );
}() );
