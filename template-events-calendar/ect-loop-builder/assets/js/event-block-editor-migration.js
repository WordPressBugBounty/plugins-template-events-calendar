( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! config || ! wp.blocks || ! wp.apiFetch || ! wp.blockEditor ) {
		return;
	}

	var legacyBlock = 'ebec/event-list';
	var targetBlock = 'elb/loop-builder';
	var createElement = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var useRef = wp.element.useRef;
	var useState = wp.element.useState;

	if ( wp.blocks.getBlockType( legacyBlock ) ) {
		return;
	}

	// Legacy Event Block attributes. Typography keys follow one pattern per field
	// (event_{field}_{family|weight|transform|style|decoration} strings,
	// event_{field}_{font|line_height|letter_spacing} numbers), as in PHP legacy_defaults().
	var TYPO_FIELDS = [ 'date', 'title', 'venue', 'description', 'link' ];
	var TYPO_STRING_PROPS = [ 'family', 'weight', 'transform', 'style', 'decoration' ];
	var TYPO_NUMBER_PROPS = [ 'font', 'line_height', 'letter_spacing' ];
	var STRING_KEYS = [
		'ebec_max_events', 'ebec_block_id', 'ebec_venue', 'ebec_display_cate',
		'ebec_display_desc', 'ebec_type', 'ebec_hide_read_more_link',
		'ebec_date_formats', 'ebec_order', 'ebec_date_range_start',
		'ebec_date_range_end', 'main_skin_color', 'event_date_color',
		'event_title_color', 'event_venue_color', 'event_description_color',
		'event_link_color', 'event_header_type', 'event_simple_color',
		'event_featured_color', 'event_link_name', 'no_event_text',
		'event_layout', 'event_desc_type'
	];

	function attributesSchema() {
		var schema = {
			ebec_ev_category: { type: 'array', default: [ 'all' ] },
			ebec_event_source: { type: 'boolean', default: false },
			isPreview: { type: 'boolean', default: false }
		};
		STRING_KEYS.forEach( function ( key ) {
			schema[ key ] = { type: 'string' };
		} );
		TYPO_FIELDS.forEach( function ( field ) {
			TYPO_STRING_PROPS.forEach( function ( prop ) {
				schema[ 'event_' + field + '_' + prop ] = { type: 'string' };
			} );
			TYPO_NUMBER_PROPS.forEach( function ( prop ) {
				schema[ 'event_' + field + '_' + prop ] = { type: 'number' };
			} );
		} );
		return schema;
	}

	// Deterministic 32-bit hash of the block's clientId (keeps generated block IDs stable per block).
	function clientIdHash( clientId ) {
		return String( clientId || '' ).split( '' ).reduce( function ( hash, character ) {
			return ( ( hash * 31 ) + character.charCodeAt( 0 ) ) >>> 0;
		}, 1 );
	}

	function LegacyMigrationEdit( props ) {
		var started = useRef( false );
		var mounted = useRef( true );
		var busyState = useState( false );
		var busy = busyState[ 0 ];
		var setBusy = busyState[ 1 ];
		var errorState = useState( '' );
		var error = errorState[ 0 ];
		var setError = errorState[ 1 ];
		var blockProps = wp.blockEditor.useBlockProps( {
			style: { border: '1px solid #dcdcde', padding: '20px', background: '#fff' }
		} );

		function convert() {
			var editor = wp.data.select( 'core/editor' );
			setBusy( true );
			setError( '' );
			wp.apiFetch( {
				path: config.endpoint,
				method: 'POST',
				data: {
					postId: editor ? editor.getCurrentPostId() : 0,
					blockIndex: clientIdHash( props.clientId ),
					attributes: props.attributes || {}
				}
			} ).then( function ( response ) {
				var blocks = wp.blocks.parse( response.content || '' );
				if ( blocks.length !== 1 || blocks[ 0 ].name !== targetBlock ) {
					throw new Error( config.invalid );
				}
				// This block unmounts here, so no state update follows a successful conversion.
				wp.data.dispatch( 'core/block-editor' ).replaceBlock( props.clientId, blocks[ 0 ] );
			} ).catch( function ( requestError ) {
				if ( ! mounted.current ) {
					return;
				}
				setError( requestError && requestError.message ? requestError.message : config.invalid );
				setBusy( false );
			} );
		}

		useEffect( function () {
			if ( ! started.current ) {
				started.current = true;
				convert();
			}
			return function () {
				mounted.current = false;
			};
		}, [] );

		return createElement(
			'div',
			blockProps,
			createElement( 'strong', null, config.title ),
			createElement( 'p', null, busy ? config.converting : config.description ),
			error ? createElement( wp.components.Notice, { status: 'error', isDismissible: false }, error ) : null,
			error && ! busy ? createElement( wp.components.Button, { variant: 'secondary', onClick: convert }, config.retry ) : null,
			busy ? createElement( wp.components.Spinner ) : null
		);
	}

	wp.blocks.registerBlockType( legacyBlock, {
		apiVersion: 3,
		title: config.title,
		icon: 'update',
		category: 'widgets',
		attributes: attributesSchema(),
		supports: { inserter: false, html: false, reusable: false },
		edit: LegacyMigrationEdit,
		save: function () {
			return null;
		}
	} );
}( window.wp, window.ectEventBlockMigration ) );
