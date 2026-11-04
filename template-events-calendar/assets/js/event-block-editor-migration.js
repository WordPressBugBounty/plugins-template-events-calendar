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

	function attributesSchema() {
		var schema = {
			ebec_ev_category: { type: 'array', default: [ 'all' ] },
			ebec_event_source: { type: 'boolean', default: false },
			isPreview: { type: 'boolean', default: false }
		};
		[
			'ebec_max_events', 'ebec_block_id', 'ebec_venue', 'ebec_display_cate',
			'ebec_display_desc', 'ebec_type', 'ebec_hide_read_more_link',
			'ebec_date_formats', 'ebec_order', 'ebec_date_range_start',
			'ebec_date_range_end', 'main_skin_color', 'event_date_color',
			'event_title_color', 'event_venue_color', 'event_description_color',
			'event_link_color', 'event_date_family', 'event_date_weight',
			'event_date_transform', 'event_date_style', 'event_date_decoration',
			'event_title_family', 'event_title_weight', 'event_title_transform',
			'event_title_style', 'event_title_decoration', 'event_venue_family',
			'event_venue_weight', 'event_venue_transform', 'event_venue_style',
			'event_venue_decoration', 'event_description_family',
			'event_description_weight', 'event_description_transform',
			'event_description_style', 'event_description_decoration',
			'event_link_family', 'event_link_weight', 'event_link_transform',
			'event_link_style', 'event_link_decoration', 'event_header_type',
			'event_simple_color', 'event_featured_color', 'event_link_name',
			'no_event_text', 'event_layout', 'event_desc_type'
		].forEach( function ( key ) {
			schema[ key ] = { type: 'string' };
		} );
		[
			'event_date_font', 'event_title_font', 'event_venue_font',
			'event_description_font', 'event_link_font', 'event_date_line_height',
			'event_date_letter_spacing', 'event_title_line_height',
			'event_title_letter_spacing', 'event_venue_line_height',
			'event_venue_letter_spacing', 'event_description_line_height',
			'event_description_letter_spacing', 'event_link_line_height',
			'event_link_letter_spacing'
		].forEach( function ( key ) {
			schema[ key ] = { type: 'number' };
		} );
		return schema;
	}

	function blockIndex( clientId ) {
		return String( clientId || '' ).split( '' ).reduce( function ( hash, character ) {
			return ( ( hash * 31 ) + character.charCodeAt( 0 ) ) >>> 0;
		}, 1 );
	}

	function LegacyMigrationEdit( props ) {
		var started = useRef( false );
		var state = useState( false );
		var busy = state[ 0 ];
		var setBusy = state[ 1 ];
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
					blockIndex: blockIndex( props.clientId ),
					attributes: props.attributes || {}
				}
			} ).then( function ( response ) {
				var blocks = wp.blocks.parse( response.content || '' );
				if ( blocks.length !== 1 || blocks[ 0 ].name !== targetBlock ) {
					throw new Error( config.invalid );
				}
				wp.data.dispatch( 'core/block-editor' ).replaceBlock( props.clientId, blocks[ 0 ] );
			} ).catch( function ( requestError ) {
				setError( requestError && requestError.message ? requestError.message : config.invalid );
			} ).finally( function () {
				setBusy( false );
			} );
		}

		useEffect( function () {
			if ( started.current ) {
				return;
			}
			started.current = true;
			convert();
		}, [] );

		return createElement(
			'div',
			blockProps,
			createElement( 'strong', null, config.title ),
			createElement( 'p', null, busy ? config.converting : config.description ),
			error ? createElement( wp.components.Notice, { status: 'error', isDismissible: false }, error ) : null,
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
