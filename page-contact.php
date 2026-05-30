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

						<li class="contact-info__item">
							<span class="contact-info__label"><?php esc_html_e( 'E-mail', 'jasanika' ); ?></span>
							<span class="contact-info__value">
								<a class="contact-info__link" href="<?php echo esc_url( 'mailto:info@jasanika.cz' ); ?>">
									<?php echo esc_html( 'info@jasanika.cz' ); ?>
								</a>
							</span>
						</li>

						<li class="contact-info__item">
							<span class="contact-info__label"><?php esc_html_e( 'Telefon', 'jasanika' ); ?></span>
							<span class="contact-info__value">
								<a class="contact-info__link" href="<?php echo esc_url( 'tel:+420000000000' ); ?>">
									<?php echo esc_html( '+420 000 000 000' ); ?>
								</a>
							</span>
						</li>

						<li class="contact-info__item">
							<span class="contact-info__label"><?php esc_html_e( 'Adresa', 'jasanika' ); ?></span>
							<span class="contact-info__value">
								<?php echo esc_html( 'Jasanika' ); ?><br>
								<?php echo esc_html( 'Česká republika' ); ?>
							</span>
						</li>

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
