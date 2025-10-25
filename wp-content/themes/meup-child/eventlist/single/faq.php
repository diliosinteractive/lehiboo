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

<?php foreach( $faq_items as $index => $faq ) :
	$question = isset($faq['question']) ? $faq['question'] : '';
	$answer = isset($faq['answer']) ? $faq['answer'] : '';

	if( empty($question) || empty($answer) ) continue;

	$accordion_id = 'faq_' . $index;
?>
	<div class="faq_item" data-faq-index="<?php echo esc_attr( $index ); ?>">
		<!-- Question (accordéon trigger) -->
		<h3 class="faq_question"
		    role="button"
		    tabindex="0"
		    aria-expanded="false"
		    aria-controls="<?php echo esc_attr( $accordion_id ); ?>">
			<?php echo esc_html( $question ); ?>
		</h3>

		<!-- Réponse (accordéon content) -->
		<div class="faq_answer" id="<?php echo esc_attr( $accordion_id ); ?>">
			<?php echo wp_kses_post( wpautop( $answer ) ); ?>
		</div>
	</div>
<?php endforeach; ?>

<script>
(function($) {
	'use strict';

	// Accordéons FAQ
	$('.faq_question').on('click', function(e) {
		e.preventDefault();

		var $question = $(this);
		var $item = $question.closest('.faq_item');
		var $answer = $item.find('.faq_answer');
		var isActive = $question.hasClass('active');

		// Fermer tous les autres accordéons
		$('.faq_item').not($item).each(function() {
			$(this).find('.faq_question')
				.removeClass('active')
				.attr('aria-expanded', 'false');
			$(this).find('.faq_answer').removeClass('active');
		});

		// Toggle l'accordéon actuel
		if( isActive ) {
			$question.removeClass('active').attr('aria-expanded', 'false');
			$answer.removeClass('active');
		} else {
			$question.addClass('active').attr('aria-expanded', 'true');
			$answer.addClass('active');
		}
	});

})(jQuery);
</script>
