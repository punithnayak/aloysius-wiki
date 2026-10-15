/**
 * HelloAloysius, browser side.
 * The PHP hooks decided WHAT to show. This file makes it move.
 */
( function () {
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// 1. Reading progress bar: only on pages where PHP added the greeting chip.
	if ( document.querySelector( '.hello-chip' ) ) {
		var bar = document.createElement( 'div' );
		bar.className = 'hello-progress';
		document.body.appendChild( bar );

		var update = function () {
			var max = document.documentElement.scrollHeight - window.innerHeight;
			var done = max > 0 ? Math.min( 1, window.scrollY / max ) : 1;
			bar.style.transform = 'scaleX(' + done + ')';
		};
		window.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	}

	// 2. Save celebration: PHP set wgHelloJustSaved after the PageSaveComplete hook.
	var saved = mw.config.get( 'wgHelloJustSaved' );
	if ( !saved ) {
		return;
	}

	var card = document.createElement( 'div' );
	card.className = 'hello-saved';
	card.setAttribute( 'role', 'status' );

	var icon = document.createElement( 'div' );
	icon.className = 'hello-saved-icon';
	icon.textContent = saved.isNew ? '✨' : '🎉';

	var title = document.createElement( 'div' );
	title.className = 'hello-saved-title';
	title.textContent = mw.msg( saved.isNew ? 'helloaloysius-created-title' : 'helloaloysius-saved-title' );

	card.appendChild( icon );
	card.appendChild( title );

	if ( saved.count ) {
		var body = document.createElement( 'div' );
		// Split the message around $1 so the number can get its own colour.
		var parts = mw.msg( 'helloaloysius-saved-body', '\u0000' ).split( '\u0000' );
		var num = document.createElement( 'span' );
		num.className = 'hello-saved-count';
		num.textContent = mw.language.convertNumber( saved.count );
		body.appendChild( document.createTextNode( parts[ 0 ] || '' ) );
		body.appendChild( num );
		body.appendChild( document.createTextNode( parts[ 1 ] || '' ) );
		card.appendChild( body );
	}

	var note = document.createElement( 'div' );
	note.className = 'hello-saved-note';
	note.textContent = mw.msg( 'helloaloysius-hook-note' );
	card.appendChild( note );

	document.body.appendChild( card );

	var close = function () {
		card.classList.add( 'is-leaving' );
		setTimeout( function () {
			card.remove();
		}, 400 );
	};
	card.addEventListener( 'click', close );
	setTimeout( close, 6000 );

	// A burst of emoji flying out of the card.
	if ( !reduceMotion ) {
		var rect = card.getBoundingClientRect();
		var emoji = [ '🎉', '✨', '⭐', '💜', '🧡', '📝' ];
		for ( var i = 0; i < 18; i++ ) {
			var e = document.createElement( 'span' );
			e.className = 'hello-burst';
			e.textContent = emoji[ i % emoji.length ];
			var angle = Math.PI + ( Math.PI * i / 17 ) + ( Math.random() - 0.5 ) * 0.3;
			var dist = 120 + Math.random() * 160;
			e.style.left = ( rect.left + 36 ) + 'px';
			e.style.top = ( rect.top + 30 ) + 'px';
			e.style.setProperty( '--dx', Math.cos( angle ) * dist + 'px' );
			e.style.setProperty( '--dy', ( Math.sin( angle ) * dist - 60 ) + 'px' );
			e.style.setProperty( '--rot', ( Math.random() * 360 - 180 ) + 'deg' );
			e.style.animationDelay = ( Math.random() * 0.15 ) + 's';
			document.body.appendChild( e );
			setTimeout( e.remove.bind( e ), 1700 );
		}
	}
}() );
