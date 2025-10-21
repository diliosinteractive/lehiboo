<?php
/**
 * Template Part: FAQ
 *
 * Affiche les questions fréquentes en accordéons
 *
 * @package LeHiboo
 */

if( ! defined( 'ABSPATH' ) ) exit();

$event_id = get_the_ID();

// Récupérer la FAQ (repeater field)
$faq_items = get_post_meta( $event_id, OVA_METABOX_EVENT . 'faq', true );

// Si pas de FAQ, ne rien afficher
if( empty($faq_items) || !is_array($faq_items) ) {
	return;
}

// Filtrer les items vides
$faq_items = array_filter( $faq_items, function($item) {
	return !empty($item['question']) && !empty($item['answer']);
});

if( empty($faq_items) ) {
	return;
}
?>

<div class="event_faq event_section_white">
	<h3 class="faq_title second_font"><?php esc_html_e( 'Questions fréquentes', 'eventlist' ); ?></h3>

	<div class="faq_accordions">
		<?php foreach( $faq_items as $index => $faq ) :
			$question = isset($faq['question']) ? $faq['question'] : '';
			$answer = isset($faq['answer']) ? $faq['answer'] : '';

			if( empty($question) || empty($answer) ) continue;

			$accordion_id = 'faq_' . $index;
		?>
			<div class="faq_item" data-faq-index="<?php echo esc_attr( $index ); ?>">

				<!-- Question (accordéon trigger) -->
				<button class="faq_question"
				        type="button"
				        aria-expanded="false"
				        aria-controls="<?php echo esc_attr( $accordion_id ); ?>">
					<span class="question_text"><?php echo esc_html( $question ); ?></span>
					<i class="faq_icon icon_plus"></i>
				</button>

				<!-- Réponse (accordéon content) -->
				<div class="faq_answer"
				     id="<?php echo esc_attr( $accordion_id ); ?>"
				     style="display: none;">
					<div class="answer_content">
						<?php echo wp_kses_post( wpautop( $answer ) ); ?>
					</div>
				</div>

			</div>
		<?php endforeach; ?>
	</div>
</div>

<script>
(function($) {
	'use strict';

	// Accordéons FAQ
	$('.faq_question').on('click', function(e) {
		e.preventDefault();

		var $button = $(this);
		var $item = $button.closest('.faq_item');
		var $answer = $item.find('.faq_answer');
		var isExpanded = $button.attr('aria-expanded') === 'true';

		// Fermer tous les autres accordéons
		$('.faq_item').not($item).each(function() {
			$(this).find('.faq_question')
				.attr('aria-expanded', 'false')
				.find('.faq_icon')
				.removeClass('icon_minus')
				.addClass('icon_plus');

			$(this).find('.faq_answer').slideUp(300);
		});

		// Toggle l'accordéon actuel
		if( isExpanded ) {
			$button.attr('aria-expanded', 'false');
			$button.find('.faq_icon').removeClass('icon_minus').addClass('icon_plus');
			$answer.slideUp(300);
		} else {
			$button.attr('aria-expanded', 'true');
			$button.find('.faq_icon').removeClass('icon_plus').addClass('icon_minus');
			$answer.slideDown(300);
		}
	});

})(jQuery);
</script>
