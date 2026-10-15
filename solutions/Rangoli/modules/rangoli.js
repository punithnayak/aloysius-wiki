/**
 * Rangoli: when you click the button, flowers fly everywhere.
 */
document.querySelectorAll( '.rangoli-button' ).forEach( function ( button ) {
	button.addEventListener( 'click', function () {
		var flowers = [ '🌸', '✨', '🌼', '🎉', '💜', '🧡' ];
		var box = button.getBoundingClientRect();

		for ( var i = 0; i < 30; i++ ) {
			var flower = document.createElement( 'span' );
			flower.className = 'rangoli-burst';
			flower.textContent = flowers[ i % flowers.length ];
			flower.style.left = box.left + box.width / 2 + 'px';
			flower.style.top = box.top + 'px';
			flower.style.setProperty( '--x', ( Math.random() - 0.5 ) * 600 + 'px' );
			flower.style.setProperty( '--y', ( Math.random() * 400 ) + 'px' );
			document.body.appendChild( flower );
			setTimeout( flower.remove.bind( flower ), 1600 );
		}
	} );
} );
