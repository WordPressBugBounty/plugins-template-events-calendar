<?php
// This file is generated. Do not modify it manually.
return array(
	'card-template' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/card-template',
		'textdomain' => 'template-events-calendar',
		'title' => 'Card Template',
		'icon' => 'layout',
		'category' => 'widgets',
		'description' => 'Marks the repeating card inside an Events Loop. Everything inside renders once per event.',
		'keywords' => array(
			'events',
			'loop',
			'card',
			'template'
		),
		'editorScript' => 'elb-editor',
		'supports' => array(
			'html' => false,
			'inserter' => false
		)
	),
	'event-date' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-date',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Date',
		'icon' => 'calendar',
		'category' => 'widgets',
		'keywords' => array(
			'date',
			'event date',
			'start date',
			'badge'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'date'
			),
			'format' => array(
				'type' => 'string',
				'default' => 'badge'
			),
			'dateShow' => array(
				'type' => 'string',
				'default' => 'start'
			),
			'customDateFormat' => array(
				'type' => 'string',
				'default' => ''
			),
			'showTime' => array(
				'type' => 'boolean',
				'default' => false
			),
			'badgeTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'verticalAlign' => array(
				'type' => 'string',
				'default' => 'top'
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'datePreset' => array(
				'type' => 'string',
				'default' => 'classic'
			),
			'iconPosition' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBlockIcon' => array(
				'type' => 'boolean',
				'default' => false
			),
			'blockIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'blockIconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'blockIconPos' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'blockIconPlacement' => array(
				'type' => 'string',
				'default' => 'inside'
			),
			'blockIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'description' => 'Show the event date as a badge, label or text.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-excerpt' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-excerpt',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Description',
		'icon' => 'editor-paragraph',
		'category' => 'widgets',
		'keywords' => array(
			'excerpt',
			'event description',
			'summary',
			'description'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'excerpt'
			),
			'wordCount' => array(
				'type' => 'number',
				'default' => 20
			),
			'lengthMode' => array(
				'type' => 'string',
				'default' => 'words'
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBlockIcon' => array(
				'type' => 'boolean',
				'default' => false
			),
			'blockIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'blockIconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'blockIconPos' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'blockIconPlacement' => array(
				'type' => 'string',
				'default' => 'outside'
			),
			'blockIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'description' => 'Show the event\'s description, as a short excerpt or the full text.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-image' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-image',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Image',
		'icon' => 'format-image',
		'category' => 'widgets',
		'description' => 'Show the event\'s featured image.',
		'keywords' => array(
			'event',
			'image',
			'thumbnail',
			'elb'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'image'
			),
			'enableLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'linkNewTab' => array(
				'type' => 'boolean',
				'default' => false
			),
			'linkSource' => array(
				'type' => 'string',
				'default' => 'permalink'
			),
			'aspectRatio' => array(
				'type' => 'string',
				'default' => '4/3'
			),
			'imageStyle' => array(
				'type' => 'string',
				'default' => 'default'
			),
			'autoHeight' => array(
				'type' => 'boolean',
				'default' => true
			),
			'hoverEffect' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'imageSize' => array(
				'type' => 'string',
				'default' => 'large'
			),
			'hoverDuration' => array(
				'type' => 'number',
				'default' => 0
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-info-row' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-info-row',
		'textdomain' => 'template-events-calendar',
		'title' => 'Venue, Time & Price',
		'icon' => 'location',
		'category' => 'widgets',
		'keywords' => array(
			'venue',
			'time',
			'ticket',
			'location',
			'info'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'showInfoRow' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showVenue' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showTime' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showTicket' => array(
				'type' => 'boolean',
				'default' => true
			),
			'itemOrder' => array(
				'type' => 'string',
				'default' => 'venue,time,ticket'
			),
			'layoutUnlocked' => array(
				'type' => 'boolean',
				'default' => false
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => '32'
			),
			'textColor' => array(
				'type' => 'string',
				'default' => '#64748b'
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => '14'
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => '400'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'iconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'iconBg' => array(
				'type' => 'string',
				'default' => '#eff6ff'
			),
			'iconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'venueTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueIconBg' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueIcon' => array(
				'type' => 'string',
				'default' => 'mapMarker'
			),
			'venueFontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueFontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'venueFontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueLineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueLineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'venueLetterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueLetterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'venueTextTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueFontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'venueTextDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeIconBg' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeIcon' => array(
				'type' => 'string',
				'default' => 'timeToRead'
			),
			'timeFontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeFontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'timeFontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeLineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeLineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'timeLetterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeLetterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'timeTextTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeFontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'timeTextDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketIconBg' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketIcon' => array(
				'type' => 'string',
				'default' => 'ticket'
			),
			'ticketFontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketFontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'ticketFontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketLineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketLineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'ticketLetterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketLetterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'ticketTextTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketFontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'ticketTextDecoration' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true,
			'inserter' => false
		),
		'render' => 'file:./render.php',
		'description' => 'Show the venue, time and ticket link in one row.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-meta' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-meta',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Meta',
		'icon' => 'tag',
		'category' => 'widgets',
		'description' => 'Show event details like category, date, time and venue in one row.',
		'keywords' => array(
			'event',
			'meta',
			'category',
			'date',
			'time',
			'venue',
			'organizer',
			'price',
			'details',
			'elb'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'time'
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showTime' => array(
				'type' => 'boolean',
				'default' => true
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'format' => array(
				'type' => 'string',
				'default' => 'show_only_start_date'
			),
			'overlayOnImage' => array(
				'type' => 'boolean',
				'default' => false
			),
			'iconPosition' => array(
				'type' => 'string',
				'default' => 'before'
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemOrder' => array(
				'type' => 'string',
				'default' => 'category,time'
			),
			'layoutUnlocked' => array(
				'type' => 'boolean',
				'default' => false
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBlockIcon' => array(
				'type' => 'boolean',
				'default' => false
			),
			'blockIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'blockIconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'blockIconPos' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'blockIconPlacement' => array(
				'type' => 'string',
				'default' => 'outside'
			),
			'blockIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'catTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'catBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'catHoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'catHoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'catPaddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'catPaddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'catPaddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'catPaddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'catPaddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'catMarginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'catMarginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'catMarginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'catMarginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'catMarginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'catFontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'catFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'catFontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'catFontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'catLineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'catLineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'catLetterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'catLetterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'catTextTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'catFontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'catTextDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'items' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'metaSeparator' => array(
				'type' => 'string',
				'default' => 'dot'
			),
			'metaGap' => array(
				'type' => 'number',
				'default' => 8
			),
			'metaWrap' => array(
				'type' => 'boolean',
				'default' => true
			),
			'badgeStyle' => array(
				'type' => 'string',
				'default' => 'text'
			),
			'badgeRadius' => array(
				'type' => 'string',
				'default' => ''
			),
			'separatorColor' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true,
			'inserter' => false
		),
		'render' => 'file:./render.php',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-read-more' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-read-more',
		'textdomain' => 'template-events-calendar',
		'title' => 'Read More',
		'icon' => 'button',
		'category' => 'widgets',
		'keywords' => array(
			'read more',
			'button',
			'link',
			'view event'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'readmore'
			),
			'enableLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'linkNewTab' => array(
				'type' => 'boolean',
				'default' => false
			),
			'linkSource' => array(
				'type' => 'string',
				'default' => 'permalink'
			),
			'buttonLabel' => array(
				'type' => 'string',
				'default' => ''
			),
			'showButton' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showPrice' => array(
				'type' => 'boolean',
				'default' => false
			),
			'format' => array(
				'type' => 'string',
				'default' => 'link'
			),
			'buttonPreset' => array(
				'type' => 'string',
				'default' => 'primary'
			),
			'buttonBg' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonHoverBg' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonHoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonPaddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonPaddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonPaddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonPaddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'buttonPaddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'buttonMarginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'buttonMarginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'buttonMarginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'buttonMarginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'buttonMarginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBlockIcon' => array(
				'type' => 'boolean',
				'default' => true
			),
			'blockIcon' => array(
				'type' => 'string',
				'default' => 'arrowRight'
			),
			'blockIconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'blockIconPos' => array(
				'type' => 'string',
				'default' => 'right'
			),
			'blockIconPlacement' => array(
				'type' => 'string',
				'default' => 'outside'
			),
			'blockIconColor' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'description' => 'Show a "Read more" link or button that opens the event.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-share' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-share',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Share',
		'icon' => 'share',
		'category' => 'widgets',
		'keywords' => array(
			'share',
			'social',
			'facebook'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'shareNetworks' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'string'
				),
				'default' => array(
					'facebook',
					'twitter',
					'copy'
				)
			),
			'sourceField' => array(
				'type' => 'string',
				'default' => 'share'
			),
			'sharePreset' => array(
				'type' => 'string',
				'default' => 'classic'
			),
			'shareSize' => array(
				'type' => 'number',
				'default' => 44
			),
			'showLabels' => array(
				'type' => 'boolean',
				'default' => false
			),
			'borderRadius' => array(
				'type' => 'number',
				'default' => 8
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'description' => 'Show social share buttons for the event.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-time' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-time',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Time',
		'icon' => 'clock',
		'category' => 'widgets',
		'keywords' => array(
			'time',
			'event time',
			'clock',
			'hours'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'time'
			),
			'showTime' => array(
				'type' => 'boolean',
				'default' => true
			),
			'timeStyle' => array(
				'type' => 'string'
			),
			'timeFormat' => array(
				'type' => 'string'
			),
			'timeFormatCustom' => array(
				'type' => 'string'
			),
			'timeShow' => array(
				'type' => 'string'
			),
			'allDayLabel' => array(
				'type' => 'string'
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBlockIcon' => array(
				'type' => 'boolean',
				'default' => true
			),
			'blockIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'blockIconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'blockIconPos' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'blockIconPlacement' => array(
				'type' => 'string',
				'default' => 'outside'
			),
			'blockIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'description' => 'Show the event start and end time.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'event-title' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/event-title',
		'textdomain' => 'template-events-calendar',
		'title' => 'Event Title',
		'icon' => 'heading',
		'category' => 'widgets',
		'keywords' => array(
			'title',
			'event title',
			'heading',
			'event name'
		),
		'editorScript' => 'elb-editor',
		'usesContext' => array(
			'elb/postId'
		),
		'attributes' => array(
			'sourceField' => array(
				'type' => 'string',
				'default' => 'title'
			),
			'enableLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'level' => array(
				'type' => 'number',
				'default' => 3
			),
			'textAlign' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'linkNewTab' => array(
				'type' => 'boolean',
				'default' => false
			),
			'linkSource' => array(
				'type' => 'string',
				'default' => 'permalink'
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexDirection' => array(
				'type' => 'string',
				'default' => ''
			),
			'justifyContent' => array(
				'type' => 'string',
				'default' => ''
			),
			'alignItems' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexWrap' => array(
				'type' => 'string',
				'default' => ''
			),
			'flexGap' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'presetDesign' => array(
				'type' => 'string',
				'default' => 'default'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBlockIcon' => array(
				'type' => 'boolean',
				'default' => false
			),
			'blockIcon' => array(
				'type' => 'string',
				'default' => ''
			),
			'blockIconSize' => array(
				'type' => 'number',
				'default' => 18
			),
			'blockIconPos' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'blockIconPlacement' => array(
				'type' => 'string',
				'default' => 'outside'
			),
			'blockIconColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColorHover' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'customClassName' => true
		),
		'render' => 'file:./render.php',
		'description' => 'Show the event title, with an optional link and icon.',
		'ancestor' => array(
			'elb/loop-builder'
		)
	),
	'flex-container' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/flex-container',
		'title' => 'Container',
		'category' => 'elb-blocks',
		'description' => 'Group blocks and arrange them in rows or columns.',
		'keywords' => array(
			'container',
			'group',
			'row',
			'column',
			'flex',
			'layout',
			'wrapper'
		),
		'textdomain' => 'template-events-calendar',
		'supports' => array(
			'anchor' => true,
			'html' => false,
			'inserter' => false
		),
		'usesContext' => array(
			'elb/postId',
			'elb/postType'
		),
		'editorScript' => 'elb-layout-blocks-editor',
		'attributes' => array(
			'block_id' => array(
				'type' => 'string'
			),
			'elbRole' => array(
				'type' => 'string',
				'default' => ''
			),
			'elbFrontendCss' => array(
				'type' => 'string',
				'default' => ''
			),
			'htmlTag' => array(
				'type' => 'string',
				'default' => 'div'
			),
			'htmlTagLink' => array(
				'type' => 'object'
			),
			'linkTarget' => array(
				'type' => 'boolean',
				'default' => false
			),
			'contentWidth' => array(
				'type' => 'string',
				'default' => 'alignfull'
			),
			'innerContentWidth' => array(
				'type' => 'string',
				'default' => 'alignwide'
			),
			'widthDesktop' => array(
				'type' => 'number',
				'default' => 100
			),
			'widthType' => array(
				'type' => 'string',
				'default' => '%'
			),
			'innerContentCustomWidthDesktop' => array(
				'type' => 'number',
				'default' => 1200
			),
			'innerContentCustomWidthType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'minHeightDesktop' => array(
				'type' => 'number'
			),
			'minHeightType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'directionDesktop' => array(
				'type' => 'string',
				'default' => 'column'
			),
			'alignItemsDesktop' => array(
				'type' => 'string',
				'default' => 'center'
			),
			'justifyContentDesktop' => array(
				'type' => 'string',
				'default' => 'center'
			),
			'wrapDesktop' => array(
				'type' => 'string',
				'default' => 'nowrap'
			),
			'alignContentDesktop' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundType' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'backgroundColor' => array(
				'type' => 'string'
			),
			'boxShadowColor' => array(
				'type' => 'string',
				'default' => '#00000070'
			),
			'boxShadowHOffset' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowVOffset' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowBlur' => array(
				'type' => 'number'
			),
			'boxShadowSpread' => array(
				'type' => 'number'
			),
			'boxShadowPosition' => array(
				'type' => 'string',
				'default' => 'outset'
			),
			'boxShadowColorHover' => array(
				'type' => 'string'
			),
			'boxShadowHOffsetHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowVOffsetHover' => array(
				'type' => 'number',
				'default' => 0
			),
			'boxShadowBlurHover' => array(
				'type' => 'number'
			),
			'boxShadowSpreadHover' => array(
				'type' => 'number'
			),
			'boxShadowPositionHover' => array(
				'type' => 'string',
				'default' => 'outset'
			),
			'topPaddingDesktop' => array(
				'type' => 'number',
				'default' => 10
			),
			'bottomPaddingDesktop' => array(
				'type' => 'number',
				'default' => 10
			),
			'leftPaddingDesktop' => array(
				'type' => 'number',
				'default' => 10
			),
			'rightPaddingDesktop' => array(
				'type' => 'number',
				'default' => 10
			),
			'paddingType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'paddingLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'topMarginDesktop' => array(
				'type' => 'number'
			),
			'bottomMarginDesktop' => array(
				'type' => 'number'
			),
			'leftMarginDesktop' => array(
				'type' => 'number'
			),
			'rightMarginDesktop' => array(
				'type' => 'number'
			),
			'marginType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'marginLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'variationSelected' => array(
				'type' => 'boolean',
				'default' => false
			),
			'rowGapDesktop' => array(
				'type' => 'number',
				'default' => 20
			),
			'rowGapType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'columnGapDesktop' => array(
				'type' => 'number',
				'default' => 20
			),
			'columnGapType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'isBlockRootParent' => array(
				'type' => 'boolean',
				'default' => false
			),
			'widthSetByUser' => array(
				'type' => 'boolean',
				'default' => false
			),
			'textColor' => array(
				'type' => 'string'
			),
			'linkColor' => array(
				'type' => 'string'
			),
			'linkHoverColor' => array(
				'type' => 'string'
			),
			'overflow' => array(
				'type' => 'string',
				'default' => 'visible'
			),
			'equalHeight' => array(
				'type' => 'boolean',
				'default' => false
			),
			'containerBorderTopWidth' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderLeftWidth' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderRightWidth' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderBottomWidth' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderTopLeftRadius' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderTopRightRadius' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderBottomLeftRadius' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderBottomRightRadius' => array(
				'type' => 'number',
				'default' => ''
			),
			'containerBorderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'containerBorderLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'containerBorderRadiusLink' => array(
				'type' => 'boolean',
				'default' => true
			),
			'containerBorderStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'containerBorderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'containerBorderHColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'childrenWidthDesktop' => array(
				'type' => 'string'
			),
			'layout' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	),
	'loop-builder' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/loop-builder',
		'textdomain' => 'template-events-calendar',
		'title' => 'Events Loop',
		'category' => 'widgets',
		'icon' => 'layout',
		'description' => 'Show events in a grid or list, filtered by date, category and more.',
		'keywords' => array(
			'events',
			'calendar',
			'upcoming events',
			'event list',
			'event grid',
			'event carousel'
		),
		'editorScript' => 'elb-editor',
		'viewScript' => 'elb-frontend',
		'editorStyle' => 'elb-editor',
		'attributes' => array(
			'postId' => array(
				'type' => 'number',
				'default' => 0
			),
			'postType' => array(
				'type' => 'string',
				'default' => 'tribe_events'
			),
			'perPage' => array(
				'type' => 'number',
				'default' => 6
			),
			'dateFrom' => array(
				'type' => 'string',
				'default' => ''
			),
			'dateTo' => array(
				'type' => 'string',
				'default' => ''
			),
			'categoryIds' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'showMonthHeading' => array(
				'type' => 'boolean',
				'default' => true
			),
			'monthHeadingColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingFontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingShowLine' => array(
				'type' => 'boolean',
				'default' => true
			),
			'monthHeadingLineColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingLineWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingLineStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingGap' => array(
				'type' => 'string',
				'default' => ''
			),
			'monthHeadingMarginBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'timelineShowLine' => array(
				'type' => 'boolean',
				'default' => true
			),
			'timelineLineColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'timelineLineWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'timelineLineStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'useCategoryColor' => array(
				'type' => 'boolean',
				'default' => true
			),
			'textColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'hoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'paddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'borderStyle' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'borderWidth' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusTopRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusBottomLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'borderRadiusUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'marginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'marginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'fontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'lineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'letterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'letterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'textTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'fontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'textDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusHoverTextColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusHoverBackgroundColor' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusPaddingTop' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusPaddingRight' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusPaddingBottom' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusPaddingLeft' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusPaddingUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'statusMarginTop' => array(
				'type' => 'string',
				'default' => '0'
			),
			'statusMarginRight' => array(
				'type' => 'string',
				'default' => '0'
			),
			'statusMarginBottom' => array(
				'type' => 'string',
				'default' => '0'
			),
			'statusMarginLeft' => array(
				'type' => 'string',
				'default' => '0'
			),
			'statusMarginUnit' => array(
				'type' => 'string',
				'default' => 'PX'
			),
			'statusFontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusFontSize' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusFontSizeUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'statusFontWeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusLineHeight' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusLineHeightUnit' => array(
				'type' => 'string',
				'default' => 'em'
			),
			'statusLetterSpacing' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusLetterSpacingUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'statusTextTransform' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusFontStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'statusTextDecoration' => array(
				'type' => 'string',
				'default' => ''
			),
			'noResultsText' => array(
				'type' => 'string',
				'default' => ''
			),
			'isSetup' => array(
				'type' => 'boolean',
				'default' => false
			),
			'layoutStyle' => array(
				'type' => 'string',
				'default' => ''
			),
			'layoutVersions' => array(
				'type' => 'object',
				'default' => array(
					
				)
			),
			'eventsTime' => array(
				'type' => 'string',
				'default' => 'all'
			),
			'showEvents' => array(
				'type' => 'string',
				'default' => 'both'
			),
			'eventsOrder' => array(
				'type' => 'string',
				'default' => 'ASC'
			),
			'eventsOrderBy' => array(
				'type' => 'string',
				'default' => 'start_date'
			),
			'selectedCategories' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		),
		'providesContext' => array(
			'elb/postId' => 'postId',
			'elb/postType' => 'postType'
		),
		'supports' => array(
			'html' => false
		),
		'render' => 'file:./render.php'
	),
	'wrapper' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'elb/wrapper',
		'title' => 'Wrapper',
		'category' => 'elb-blocks',
		'description' => 'Events Loop Builder wrapper block with spacing settings.',
		'keywords' => array(
			'wrapper',
			'events loop builder wrapper',
			'elb',
			'eclb'
		),
		'textdomain' => 'template-events-calendar',
		'supports' => array(
			'anchor' => true,
			'html' => false,
			'reusable' => false,
			'inserter' => false
		),
		'usesContext' => array(
			'elb/postId',
			'elb/postType'
		),
		'editorScript' => 'elb-layout-blocks-editor',
		'attributes' => array(
			'block_id' => array(
				'type' => 'string'
			),
			'columnGap' => array(
				'type' => 'number',
				'default' => 10
			),
			'columnGapType' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'rowGap' => array(
				'type' => 'number',
				'default' => 10
			),
			'rowGapType' => array(
				'type' => 'string',
				'default' => 'px'
			)
		)
	)
);
