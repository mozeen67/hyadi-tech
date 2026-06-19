<?php
/**
 * Server-side rendering for the Flexible Marquee block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content (inner blocks HTML).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Allowed values for enumerated attributes.
// ---------------------------------------------------------------------------
$gutslider_allowed_directions   = array( 'left', 'right', 'up', 'down' );
$gutslider_allowed_orientations = array( 'horizontal', 'vertical' );
$gutslider_allowed_units        = array( 'px', 'em', 'rem', 'vh', 'vw', '%' );

// ---------------------------------------------------------------------------
// Sanitize scalar attributes.
// ---------------------------------------------------------------------------
$gutslider_unique_id   = isset( $attributes['uniqueId'] )
	? sanitize_html_class( $attributes['uniqueId'] )
	: '';

$gutslider_speed       = isset( $attributes['speed'] )
	? max( 1, intval( $attributes['speed'] ) )
	: 30;

$gutslider_direction   = ( isset( $attributes['direction'] ) && in_array( $attributes['direction'], $gutslider_allowed_directions, true ) )
	? $attributes['direction']
	: 'left';

$gutslider_orientation = ( isset( $attributes['orientation'] ) && in_array( $attributes['orientation'], $gutslider_allowed_orientations, true ) )
	? $attributes['orientation']
	: 'horizontal';

$gutslider_pause_on_hover = ! empty( $attributes['pauseOnHover'] );
$gutslider_is_vertical    = ( 'vertical' === $gutslider_orientation );

// ---------------------------------------------------------------------------
// Sanitize responsive range attributes.
// ---------------------------------------------------------------------------
$gutslider_gap_ranges    = ( isset( $attributes['columnsGapRanges'] ) && is_array( $attributes['columnsGapRanges'] ) )
	? $attributes['columnsGapRanges']
	: array();

$gutslider_gap_units     = ( isset( $attributes['columnsGapUnits'] ) && is_array( $attributes['columnsGapUnits'] ) )
	? $attributes['columnsGapUnits']
	: array();

$gutslider_height_ranges = ( isset( $attributes['slideHeightRanges'] ) && is_array( $attributes['slideHeightRanges'] ) )
	? $attributes['slideHeightRanges']
	: array();

$gutslider_height_units  = ( isset( $attributes['slideHeightUnits'] ) && is_array( $attributes['slideHeightUnits'] ) )
	? $attributes['slideHeightUnits']
	: array();

// ---------------------------------------------------------------------------
// Build CSS custom properties for the wrapper element.
//
// style.scss reads these via var(--marquee-gap-desk) etc. inside media queries,
// so responsive behaviour lives entirely in the stylesheet — no <style> tag needed.
// ---------------------------------------------------------------------------
$gutslider_css_vars = array();

foreach ( array( 'desk', 'tab', 'mob' ) as $bp ) {
	$val  = isset( $gutslider_gap_ranges[ $bp ] ) && is_numeric( $gutslider_gap_ranges[ $bp ] ) ? $gutslider_gap_ranges[ $bp ] : null;
	$unit = ( isset( $gutslider_gap_units[ $bp ] ) && in_array( $gutslider_gap_units[ $bp ], $gutslider_allowed_units, true ) )
		? $gutslider_gap_units[ $bp ]
		: 'px';

	if ( null !== $val ) {
		$gutslider_css_vars[] = '--marquee-gap-' . $bp . ':' . floatval( $val ) . $unit;
	}
}

if ( $gutslider_is_vertical ) {
	foreach ( array( 'desk', 'tab', 'mob' ) as $bp ) {
		$val  = isset( $gutslider_height_ranges[ $bp ] ) && is_numeric( $gutslider_height_ranges[ $bp ] ) ? $gutslider_height_ranges[ $bp ] : null;
		$unit = ( isset( $gutslider_height_units[ $bp ] ) && in_array( $gutslider_height_units[ $bp ], $gutslider_allowed_units, true ) )
			? $gutslider_height_units[ $bp ]
			: 'px';

		if ( null !== $val ) {
			$gutslider_css_vars[] = '--marquee-height-' . $bp . ':' . floatval( $val ) . $unit;
		}
	}
}

// ---------------------------------------------------------------------------
// Build wrapper attributes.
// ---------------------------------------------------------------------------
$gutslider_wrapper_class = implode(
	' ',
	array(
		'marquee-wrapper',
		esc_attr( $gutslider_unique_id ),
		'marquee-' . esc_attr( $gutslider_orientation ),
	)
);

$gutslider_wrapper_attrs = array( 'class' => $gutslider_wrapper_class );

if ( ! empty( $gutslider_css_vars ) ) {
	// All var names are hardcoded; values use floatval() + whitelisted units — no raw user input.
	$gutslider_wrapper_attrs['style'] = implode( ';', $gutslider_css_vars );
}

// ---------------------------------------------------------------------------
// Build container attributes.
// ---------------------------------------------------------------------------
$gutslider_container_classes = array(
	'marquee-container',
	'marquee-' . esc_attr( $gutslider_orientation ),
);

if ( $gutslider_pause_on_hover ) {
	$gutslider_container_classes[] = 'pause-on-hover';
}

// ---------------------------------------------------------------------------
// Determine animation name from orientation + direction.
// ---------------------------------------------------------------------------
if ( $gutslider_is_vertical ) {
	$gutslider_animation_name = ( 'up' === $gutslider_direction || 'left' === $gutslider_direction )
		? 'marquee-scroll-up'
		: 'marquee-scroll-down';
} else {
	$gutslider_animation_name = 'marquee-scroll-' . $gutslider_direction;
}
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( $gutslider_wrapper_attrs ) ); ?>>
	<div
		class="<?php echo esc_attr( implode( ' ', $gutslider_container_classes ) ); ?>"
		style="--animation-duration:<?php echo intval( $gutslider_speed ); ?>s;--animation-name:<?php echo esc_attr( $gutslider_animation_name ); ?>"
	>
		<div class="marquee-content">
			<?php
				echo wp_kses_post( $content );
			?>
		</div>
		<div class="marquee-content" aria-hidden="true">
			<?php
				echo wp_kses_post( $content );
			?>
		</div>
	</div>
</div>
