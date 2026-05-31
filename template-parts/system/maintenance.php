<?php

/**
 * Jasanika – Maintenance Page Template
 *
 * Standalone full-page template served when maintenance mode is active.
 * Does not use the standard header/footer to keep the response lean and
 * to avoid loading unnecessary theme assets.
 *
 * HTTP context: called from jasanika_maintenance_render() inside
 * template_redirect, after 503 + Retry-After headers are already sent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings      = jasanika_maintenance_get_settings();
$site_name     = get_bloginfo( 'name' );
$logo_url      = jasanika_get_logo_url();
$title         = $settings['title']        ?: __( "We'll be back soon!", 'jasanika' );
$description   = $settings['description']  ?: '';
$contact_email = $settings['contact_email'] ?: '';
$contact_phone = $settings['contact_phone'] ?: '';
$launch_date   = $settings['launch_date']   ?: '';

$show_countdown     = ! empty( $settings['show_countdown'] ) && ! empty( $launch_date );
$newsletter_enabled = ! empty( $settings['newsletter_enabled'] );

$social = array_filter( array(
	'facebook'  => $settings['facebook_url']  ?? '',
	'instagram' => $settings['instagram_url'] ?? '',
	'linkedin'  => $settings['linkedin_url']  ?? '',
	'youtube'   => $settings['youtube_url']   ?? '',
) );

$tpl_uri  = get_template_directory_uri();
$vars_css = $tpl_uri . '/assets/css/base/variables.css';
$main_css = $tpl_uri . '/assets/css/components/maintenance.css';
$fonts    = 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap';

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $title ); ?> – <?php echo esc_html( $site_name ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="<?php echo esc_url( $fonts ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $vars_css ); ?>">
	<link rel="stylesheet" href="<?php echo esc_url( $main_css ); ?>">
	<?php
	$favicon_url = jasanika_get_favicon_url();
	if ( $favicon_url ) {
		echo '<link rel="icon" href="' . esc_url( $favicon_url ) . '">' . "\n";
	}
	?>
</head>
<body class="maintenance-page">

<div class="maintenance-wrap">

	<!-- Branding -->
	<div class="maintenance-logo">
		<?php if ( $logo_url ) : ?>
			<img
				src="<?php echo esc_url( $logo_url ); ?>"
				alt="<?php echo esc_attr( $site_name ); ?>"
				class="maintenance-logo__img"
			>
		<?php else : ?>
			<span class="maintenance-logo__name"><?php echo esc_html( $site_name ); ?></span>
		<?php endif; ?>
	</div>

	<!-- Main Content -->
	<main class="maintenance-content">

		<h1 class="maintenance-title"><?php echo esc_html( $title ); ?></h1>

		<?php if ( $description ) : ?>
			<p class="maintenance-description"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>

		<!-- Contact Information -->
		<?php if ( $contact_email || $contact_phone ) : ?>
			<div class="maintenance-contact">
				<?php if ( $contact_email ) : ?>
					<a
						href="mailto:<?php echo esc_attr( $contact_email ); ?>"
						class="maintenance-contact__link"
					><?php echo esc_html( $contact_email ); ?></a>
				<?php endif; ?>
				<?php if ( $contact_phone ) : ?>
					<a
						href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $contact_phone ) ); ?>"
						class="maintenance-contact__link"
					><?php echo esc_html( $contact_phone ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<!-- Countdown Timer -->
		<?php if ( $show_countdown ) : ?>
			<div class="maintenance-countdown" id="maintenance-countdown" aria-live="polite">
				<div class="maintenance-countdown__unit">
					<span class="maintenance-countdown__value" id="cd-days">--</span>
					<span class="maintenance-countdown__label"><?php esc_html_e( 'Days', 'jasanika' ); ?></span>
				</div>
				<div class="maintenance-countdown__unit">
					<span class="maintenance-countdown__value" id="cd-hours">--</span>
					<span class="maintenance-countdown__label"><?php esc_html_e( 'Hours', 'jasanika' ); ?></span>
				</div>
				<div class="maintenance-countdown__unit">
					<span class="maintenance-countdown__value" id="cd-minutes">--</span>
					<span class="maintenance-countdown__label"><?php esc_html_e( 'Minutes', 'jasanika' ); ?></span>
				</div>
				<div class="maintenance-countdown__unit">
					<span class="maintenance-countdown__value" id="cd-seconds">--</span>
					<span class="maintenance-countdown__label"><?php esc_html_e( 'Seconds', 'jasanika' ); ?></span>
				</div>
			</div>
			<script>
			(function () {
				var target  = new Date( '<?php echo esc_js( $launch_date ); ?>' );
				var wrap    = document.getElementById( 'maintenance-countdown' );
				var elDays  = document.getElementById( 'cd-days' );
				var elHours = document.getElementById( 'cd-hours' );
				var elMins  = document.getElementById( 'cd-minutes' );
				var elSecs  = document.getElementById( 'cd-seconds' );

				function pad( n ) {
					return String( n ).padStart( 2, '0' );
				}

				function tick() {
					var diff = target - Date.now();
					if ( diff <= 0 ) {
						wrap.style.display = 'none';
						return;
					}
					elDays.textContent  = pad( Math.floor( diff / 86400000 ) );
					elHours.textContent = pad( Math.floor( ( diff % 86400000 ) / 3600000 ) );
					elMins.textContent  = pad( Math.floor( ( diff % 3600000  ) / 60000   ) );
					elSecs.textContent  = pad( Math.floor( ( diff % 60000    ) / 1000    ) );
				}

				tick();
				setInterval( tick, 1000 );
			}());
			</script>
		<?php endif; ?>

		<!-- Newsletter Signup -->
		<?php if ( $newsletter_enabled ) : ?>
			<div class="maintenance-newsletter">
				<h2 class="maintenance-newsletter__title">
					<?php echo esc_html( jasanika_get_newsletter_title() ); ?>
				</h2>
				<p class="maintenance-newsletter__description">
					<?php echo esc_html( jasanika_get_newsletter_description() ); ?>
				</p>

				<form
					class="maintenance-newsletter__form"
					id="maintenance-newsletter-form"
					novalidate
				>
					<div class="maintenance-newsletter__row">
						<input
							type="email"
							id="maintenance-nl-email"
							class="maintenance-newsletter__input"
							placeholder="<?php esc_attr_e( 'Your email address', 'jasanika' ); ?>"
							required
						>
						<button type="submit" class="maintenance-newsletter__btn">
							<?php esc_html_e( 'Subscribe', 'jasanika' ); ?>
						</button>
					</div>

					<label class="maintenance-newsletter__consent">
						<input type="checkbox" id="maintenance-nl-consent" value="1">
						<?php echo esc_html( jasanika_get_newsletter_privacy_text() ); ?>
					</label>

					<div
						class="maintenance-newsletter__message"
						id="maintenance-nl-message"
						role="alert"
					></div>
				</form>

				<script>
				(function () {
					var form    = document.getElementById( 'maintenance-newsletter-form' );
					var msg     = document.getElementById( 'maintenance-nl-message' );
					var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
					var nonce   = <?php echo wp_json_encode( wp_create_nonce( 'jasanika_newsletter_subscribe' ) ); ?>;
					var i18n    = {
						emailRequired:   <?php echo wp_json_encode( __( 'Please enter your email address.', 'jasanika' ) ); ?>,
						consentRequired: <?php echo wp_json_encode( __( 'Please accept the privacy policy to subscribe.', 'jasanika' ) ); ?>,
						serverError:     <?php echo wp_json_encode( __( 'An error occurred. Please try again later.', 'jasanika' ) ); ?>,
					};

					function showMsg( text, isError ) {
						msg.textContent = text;
						msg.className   = 'maintenance-newsletter__message ' + ( isError ? 'is-error' : 'is-success' );
					}

					if ( ! form ) { return; }

					form.addEventListener( 'submit', function ( e ) {
						e.preventDefault();

						var email   = document.getElementById( 'maintenance-nl-email' ).value.trim();
						var consent = document.getElementById( 'maintenance-nl-consent' ).checked;

						if ( ! email ) {
							showMsg( i18n.emailRequired, true );
							return;
						}
						if ( ! consent ) {
							showMsg( i18n.consentRequired, true );
							return;
						}

						var body = new URLSearchParams( {
							action:  'jasanika_newsletter_subscribe',
							nonce:   nonce,
							email:   email,
							consent: '1',
						} );

						fetch( ajaxUrl, {
							method:  'POST',
							headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
							body:    body.toString(),
						} )
						.then( function ( r ) { return r.json(); } )
						.then( function ( json ) {
							if ( json.success ) {
								showMsg( json.data.message, false );
								form.reset();
							} else {
								showMsg( json.data.message || i18n.serverError, true );
							}
						} )
						.catch( function () {
							showMsg( i18n.serverError, true );
						} );
					} );
				}());
				</script>
			</div>
		<?php endif; ?>

	</main><!-- .maintenance-content -->

	<!-- Social Links -->
	<?php if ( $social ) : ?>
		<nav class="maintenance-social" aria-label="<?php esc_attr_e( 'Social links', 'jasanika' ); ?>">

			<?php if ( ! empty( $social['facebook'] ) ) : ?>
				<a href="<?php echo esc_url( $social['facebook'] ); ?>" class="maintenance-social__link" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $social['instagram'] ) ) : ?>
				<a href="<?php echo esc_url( $social['instagram'] ); ?>" class="maintenance-social__link" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $social['linkedin'] ) ) : ?>
				<a href="<?php echo esc_url( $social['linkedin'] ); ?>" class="maintenance-social__link" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $social['youtube'] ) ) : ?>
				<a href="<?php echo esc_url( $social['youtube'] ); ?>" class="maintenance-social__link" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="#1b1a1f"/></svg>
				</a>
			<?php endif; ?>

		</nav>
	<?php endif; ?>

</div><!-- .maintenance-wrap -->

</body>
</html>
