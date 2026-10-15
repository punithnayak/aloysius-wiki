/**
 * WikiPulse: listens to Wikimedia EventStreams (Server-Sent Events)
 * and draws every edit as a bubble.
 */
( function () {
	var STREAM = 'https://stream.wikimedia.org/v2/stream/recentchange';
	var canvas = document.getElementById( 'wp-canvas' );
	if ( !canvas ) {
		return;
	}
	var ctx = canvas.getContext( '2d' );
	var statusEl = document.getElementById( 'wp-status' );
	var statusText = document.getElementById( 'wp-status-text' );
	var feedEl = document.getElementById( 'wp-feed' );
	var topEl = document.getElementById( 'wp-top' );
	var hideBotsEl = document.getElementById( 'wp-hidebots' );
	var fmt = function ( n ) {
		return mw.language.convertNumber( n );
	};

	var state = {
		filter: 'all',
		hideBots: hideBotsEl.checked,
		total: 0,
		bytes: 0,
		times: [],
		wikis: {},
		bubbles: []
	};

	// ---------- Canvas sizing ----------
	var W = 0, H = 0, dpr = window.devicePixelRatio || 1;
	function resize() {
		var r = canvas.getBoundingClientRect();
		W = r.width;
		H = r.height;
		canvas.width = W * dpr;
		canvas.height = H * dpr;
		ctx.setTransform( dpr, 0, 0, dpr, 0, 0 );
	}
	window.addEventListener( 'resize', resize );
	resize();

	// ---------- Filters ----------
	document.querySelectorAll( '.wp-chip' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			document.querySelectorAll( '.wp-chip' ).forEach( function ( b ) {
				b.classList.toggle( 'is-on', b === btn );
			} );
			state.filter = btn.getAttribute( 'data-wiki' );
		} );
	} );
	hideBotsEl.addEventListener( 'change', function () {
		state.hideBots = hideBotsEl.checked;
	} );

	// ---------- Helpers ----------
	function shortName( e ) {
		return ( e.server_name || e.wiki || '?' ).replace( /^www\./, '' ).replace( /\.org$/, '' );
	}
	function safeUrl( u ) {
		return typeof u === 'string' && /^https:\/\//.test( u ) ? u : null;
	}
	function editUrl( e ) {
		if ( e.type === 'edit' && e.revision && e.server_url ) {
			return safeUrl( e.server_url + ( e.server_script_path || '/w' ) +
				'/index.php?diff=' + e.revision.new + '&oldid=' + e.revision.old );
		}
		return safeUrl( e.meta && e.meta.uri );
	}

	// ---------- Each edit ----------
	function onEdit( e ) {
		if ( e.type !== 'edit' && e.type !== 'new' ) {
			return;
		}
		if ( state.hideBots && e.bot ) {
			return;
		}
		if ( state.filter !== 'all' && e.wiki !== state.filter ) {
			return;
		}
		var diff = e.length ? ( e.length.new || 0 ) - ( e.length.old || 0 ) : 0;
		var now = performance.now();

		state.total++;
		state.times.push( now );
		if ( diff > 0 ) {
			state.bytes += diff;
		}
		var name = shortName( e );
		state.wikis[ name ] = ( state.wikis[ name ] || 0 ) + 1;

		// Bubble
		if ( state.bubbles.length < 150 ) {
			var r = Math.min( 46, 5 + Math.log2( 1 + Math.abs( diff ) ) * 2.8 );
			state.bubbles.push( {
				x: r + Math.random() * Math.max( 1, W - 2 * r ),
				y: H + r,
				r: r,
				vy: 0.5 + Math.random() * 0.9,
				phase: Math.random() * Math.PI * 2,
				color: e.type === 'new' ? '167,123,255' : ( diff >= 0 ? '46,229,157' : '255,84,112' ),
				label: r > 30 && state.bubbles.filter( function ( x ) {
					return x.label;
				} ).length < 6 ? e.title : null,
				url: editUrl( e ),
				born: now
			} );
		}

		// Feed row
		var li = document.createElement( 'li' );
		var top = document.createElement( 'div' );
		top.className = 'wp-feed-top';
		var badge = document.createElement( 'span' );
		badge.className = 'wp-badge';
		badge.textContent = name.replace( '.wikipedia', '' );
		var a = document.createElement( 'a' );
		a.textContent = e.title;
		var url = editUrl( e );
		if ( url ) {
			a.href = url;
			a.target = '_blank';
			a.rel = 'noopener';
		}
		top.appendChild( badge );
		top.appendChild( a );
		var meta = document.createElement( 'div' );
		meta.className = 'wp-feed-meta';
		var who = document.createElement( 'span' );
		who.textContent = mw.msg( 'wikipulse-by', e.user || '?' );
		var d = document.createElement( 'span' );
		d.className = diff >= 0 ? 'wp-plus' : 'wp-minus';
		d.textContent = ( diff >= 0 ? '+' : '−' ) + fmt( Math.abs( diff ) );
		meta.appendChild( who );
		meta.appendChild( d );
		li.appendChild( top );
		li.appendChild( meta );
		feedEl.insertBefore( li, feedEl.firstChild );
		while ( feedEl.children.length > 10 ) {
			feedEl.removeChild( feedEl.lastChild );
		}
	}

	// ---------- Animation loop ----------
	function draw( now ) {
		ctx.clearRect( 0, 0, W, H );
		state.bubbles = state.bubbles.filter( function ( b ) {
			return b.y + b.r > -10;
		} );
		state.bubbles.forEach( function ( b ) {
			b.y -= b.vy;
			var x = b.x + Math.sin( b.phase + now / 900 ) * 6;
			var grow = Math.max( 0.05, Math.min( 1, ( now - b.born ) / 300 ) );
			var fade = Math.max( 0, Math.min( 1, b.y / ( H * 0.35 ) ) );
			var r = b.r * grow;
			var g = ctx.createRadialGradient( x - r / 3, b.y - r / 3, r / 6, x, b.y, r );
			g.addColorStop( 0, 'rgba(' + b.color + ',' + ( 0.95 * fade ) + ')' );
			g.addColorStop( 1, 'rgba(' + b.color + ',' + ( 0.25 * fade ) + ')' );
			ctx.beginPath();
			ctx.arc( x, b.y, r, 0, Math.PI * 2 );
			ctx.fillStyle = g;
			ctx.shadowColor = 'rgba(' + b.color + ',' + fade + ')';
			ctx.shadowBlur = 18;
			ctx.fill();
			ctx.shadowBlur = 0;
			if ( b.label && fade > 0.2 ) {
				ctx.font = '600 13px system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
				ctx.fillStyle = 'rgba(255,255,255,' + fade + ')';
				ctx.textAlign = 'center';
				var text = b.label.length > 28 ? b.label.slice( 0, 27 ) + '…' : b.label;
				ctx.fillText( text, x, b.y - r - 6 );
			}
			b.drawX = x;
		} );
		requestAnimationFrame( draw );
	}
	requestAnimationFrame( draw );

	// Click a bubble to open that edit
	canvas.addEventListener( 'click', function ( ev ) {
		var r = canvas.getBoundingClientRect();
		var mx = ev.clientX - r.left, my = ev.clientY - r.top;
		for ( var i = state.bubbles.length - 1; i >= 0; i-- ) {
			var b = state.bubbles[ i ];
			if ( b.url && Math.hypot( mx - b.drawX, my - b.y ) < b.r + 4 ) {
				window.open( b.url, '_blank', 'noopener' );
				return;
			}
		}
	} );

	// ---------- Numbers, twice a second ----------
	setInterval( function () {
		var now = performance.now();
		state.times = state.times.filter( function ( t ) {
			return now - t < 60000;
		} );
		var span = Math.min( 60000, now - ( state.started || now ) );
		var rate = span > 5000 ? Math.round( state.times.length * 60000 / span ) : state.times.length;
		document.getElementById( 'wp-rate' ).textContent = fmt( rate );
		document.getElementById( 'wp-total' ).textContent = fmt( state.total );
		document.getElementById( 'wp-wikis' ).textContent = fmt( Object.keys( state.wikis ).length );
		document.getElementById( 'wp-bytes' ).textContent = fmt( state.bytes );

		var top = Object.keys( state.wikis ).map( function ( k ) {
			return [ k, state.wikis[ k ] ];
		} ).sort( function ( a, b ) {
			return b[ 1 ] - a[ 1 ];
		} ).slice( 0, 6 );
		var max = top.length ? top[ 0 ][ 1 ] : 1;
		topEl.textContent = '';
		top.forEach( function ( t ) {
			var row = document.createElement( 'div' );
			row.className = 'wp-top-row';
			var nm = document.createElement( 'div' );
			nm.className = 'wp-top-name';
			var l = document.createElement( 'span' );
			l.textContent = t[ 0 ];
			var c = document.createElement( 'span' );
			c.textContent = fmt( t[ 1 ] );
			nm.appendChild( l );
			nm.appendChild( c );
			var bar = document.createElement( 'div' );
			bar.className = 'wp-top-bar';
			bar.style.width = ( 100 * t[ 1 ] / max ) + '%';
			row.appendChild( nm );
			row.appendChild( bar );
			topEl.appendChild( row );
		} );
	}, 500 );

	// ---------- Connect ----------
	function setStatus( kind ) {
		statusEl.classList.toggle( 'is-live', kind === 'live' );
		statusEl.classList.toggle( 'is-offline', kind === 'offline' );
		statusText.textContent = mw.msg( 'wikipulse-' + kind );
	}

	if ( !window.EventSource ) {
		setStatus( 'offline' );
		return;
	}
	var source = new EventSource( STREAM );
	source.onopen = function () {
		state.started = state.started || performance.now();
		setStatus( 'live' );
	};
	source.onmessage = function ( msg ) {
		state.lastMsg = performance.now();
		if ( !statusEl.classList.contains( 'is-live' ) ) {
			setStatus( 'live' );
		}
		try {
			onEdit( JSON.parse( msg.data ) );
		} catch ( err ) {}
	};
	source.onerror = function () {
		if ( source.readyState === EventSource.CLOSED ) {
			setStatus( 'offline' );
		} else if ( performance.now() - ( state.lastMsg || 0 ) > 5000 ) {
			setStatus( 'connecting' );
		}
	};
	window.addEventListener( 'pagehide', function () {
		source.close();
	} );
}() );
