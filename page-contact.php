<?php

/**
 * Template Name: Contact Page
 *
 * Dedicated contact page template for Jasanika theme.
 *
 * @package Jasanika
 */

get_header();
?>

<main id="main" class="site-main">

	<div class="contact-page">
		<div class="contact-page__container">

			<header class="contact-page__header">
				<h1 class="contact-page__title"><?php esc_html_e( 'Kontakt', 'jasanika' ); ?></h1>
				<p class="contact-page__subtitle"><?php esc_html_e( 'Spojte se s námi a rádi vám pomůžeme.', 'jasanika' ); ?></p>
			</header>

			<div class="contact-page__body">

					<aside class="contact-info">

					<h2 class="contact-info__title"><?php esc_html_e( 'Kontaktní informace', 'jasanika' ); ?></h2>

					<ul class="contact-info__list">

						<?php $email = jasanika_get_email(); ?>
						<?php if ( $email ) : ?>
							<li class="contact-info__item">
								<span class="contact-info__label"><?php esc_html_e( 'E-mail', 'jasanika' ); ?></span>
								<span class="contact-info__value">
									<a class="contact-info__link" href="mailto:<?php echo esc_attr( $email ); ?>">
										<?php echo esc_html( $email ); ?>
									</a>
								</span>
							</li>
						<?php endif; ?>

						<?php $phone = jasanika_get_phone(); ?>
						<?php if ( $phone ) : ?>
							<li class="contact-info__item">
								<span class="contact-info__label"><?php esc_html_e( 'Telefon', 'jasanika' ); ?></span>
								<span class="contact-info__value">
									<a class="contact-info__link" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								</span>
							</li>
						<?php endif; ?>

						<?php
						$street = jasanika_get_address_street();
						$city   = jasanika_get_address_city();
						$zip    = jasanika_get_address_zip();
						if ( $street || $city || $zip ) :
						?>
							<li class="contact-info__item">
								<span class="contact-info__label"><?php esc_html_e( 'Adresa', 'jasanika' ); ?></span>
								<span class="contact-info__value">
									<?php if ( $street ) : ?>
										<?php echo esc_html( $street ); ?><br>
									<?php endif; ?>
									<?php if ( $zip || $city ) : ?>
										<?php echo esc_html( trim( $zip . ' ' . $city ) ); ?>
									<?php endif; ?>
								</span>
							</li>
						<?php endif; ?>

					</ul>

				</aside>

				<div class="contact-page__form">
					<?php get_template_part( 'template-parts/components/contact-form' ); ?>
				</div>

			</div>

		</div>
	</div>

</main>

<?php
get_footer();
