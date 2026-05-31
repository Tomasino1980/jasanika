<?php

/**
 * Cookie Banner Component
 *
 * Displays the GDPR cookie consent banner and preferences modal.
 * Only rendered when the banner is enabled in Cookie Manager settings.
 * Hidden automatically once the user has recorded a consent decision.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! jasanika_cookie_consent_is_banner_enabled() ) {
	return;
}

$settings           = jasanika_cookie_consent_get_settings();
$title              = $settings['banner_title'];
$description        = $settings['banner_description'];
$privacy_policy_url = $settings['privacy_policy_url'];
$cookie_policy_url  = $settings['cookie_policy_url'];

?>

<!-- Cookie Banner -->
<div
	id="jasanika-cookie-banner"
	class="cookie-banner"
	role="dialog"
	aria-modal="true"
	aria-labelledby="cookie-banner-title"
	aria-describedby="cookie-banner-description"
>
	<div class="cookie-banner__inner">

		<div class="cookie-banner__content">

			<?php if ( $title ) : ?>
				<h2 id="cookie-banner-title" class="cookie-banner__title">
					<?php echo esc_html( $title ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( $description ) : ?>
				<p id="cookie-banner-description" class="cookie-banner__description">
					<?php echo esc_html( $description ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $privacy_policy_url || $cookie_policy_url ) : ?>
				<p class="cookie-banner__links">
					<?php if ( $privacy_policy_url ) : ?>
						<a
							href="<?php echo esc_url( $privacy_policy_url ); ?>"
							class="cookie-banner__link"
							target="_blank"
							rel="noopener noreferrer"
						>
							<?php esc_html_e( 'Privacy Policy', 'jasanika' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $cookie_policy_url ) : ?>
						<a
							href="<?php echo esc_url( $cookie_policy_url ); ?>"
							class="cookie-banner__link"
							target="_blank"
							rel="noopener noreferrer"
						>
							<?php esc_html_e( 'Cookie Policy', 'jasanika' ); ?>
						</a>
					<?php endif; ?>
				</p>
			<?php endif; ?>

		</div>

		<div class="cookie-banner__actions">

			<button
				id="jasanika-cookie-accept-all"
				class="cookie-banner__btn cookie-banner__btn--primary"
				type="button"
			>
				<?php esc_html_e( 'Accept All', 'jasanika' ); ?>
			</button>

			<button
				id="jasanika-cookie-reject"
				class="cookie-banner__btn cookie-banner__btn--secondary"
				type="button"
			>
				<?php esc_html_e( 'Reject Optional', 'jasanika' ); ?>
			</button>

			<button
				id="jasanika-cookie-preferences-open"
				class="cookie-banner__btn cookie-banner__btn--ghost"
				type="button"
			>
				<?php esc_html_e( 'Preferences', 'jasanika' ); ?>
			</button>

		</div>

	</div>
</div>

<!-- Cookie Preferences Modal -->
<div
	id="jasanika-cookie-modal"
	class="cookie-modal"
	role="dialog"
	aria-modal="true"
	aria-labelledby="cookie-modal-title"
	hidden
>
	<div class="cookie-modal__overlay" id="jasanika-cookie-modal-overlay"></div>

	<div class="cookie-modal__inner">

		<div class="cookie-modal__header">
			<h2 id="cookie-modal-title" class="cookie-modal__title">
				<?php esc_html_e( 'Cookie Preferences', 'jasanika' ); ?>
			</h2>
			<button
				id="jasanika-cookie-modal-close"
				class="cookie-modal__close"
				type="button"
				aria-label="<?php esc_attr_e( 'Close cookie preferences', 'jasanika' ); ?>"
			>
				&times;
			</button>
		</div>

		<div class="cookie-modal__body">

			<!-- Necessary Cookies -->
			<div class="cookie-modal__category">
				<div class="cookie-modal__category-row">
					<div class="cookie-modal__category-info">
						<strong class="cookie-modal__category-name">
							<?php esc_html_e( 'Necessary Cookies', 'jasanika' ); ?>
						</strong>
						<p class="cookie-modal__category-desc">
							<?php esc_html_e( 'These cookies are required for the website to function properly. They cannot be disabled.', 'jasanika' ); ?>
						</p>
					</div>
					<span class="cookie-modal__always-on">
						<?php esc_html_e( 'Always On', 'jasanika' ); ?>
					</span>
				</div>
			</div>

			<!-- Analytics Cookies -->
			<div class="cookie-modal__category">
				<div class="cookie-modal__category-row">
					<div class="cookie-modal__category-info">
						<label
							class="cookie-modal__category-name"
							for="cookie-pref-analytics"
						>
							<?php esc_html_e( 'Analytics Cookies', 'jasanika' ); ?>
						</label>
						<p class="cookie-modal__category-desc">
							<?php esc_html_e( 'Help us understand how visitors interact with our website by collecting anonymous usage data.', 'jasanika' ); ?>
						</p>
					</div>
					<label class="cookie-modal__toggle-switch">
						<input
							type="checkbox"
							id="cookie-pref-analytics"
							name="analytics"
							class="cookie-modal__checkbox"
						>
						<span class="cookie-modal__slider" aria-hidden="true"></span>
					</label>
				</div>
			</div>

			<!-- Marketing Cookies -->
			<div class="cookie-modal__category">
				<div class="cookie-modal__category-row">
					<div class="cookie-modal__category-info">
						<label
							class="cookie-modal__category-name"
							for="cookie-pref-marketing"
						>
							<?php esc_html_e( 'Marketing Cookies', 'jasanika' ); ?>
						</label>
						<p class="cookie-modal__category-desc">
							<?php esc_html_e( 'Used to deliver personalised advertisements and track the effectiveness of marketing campaigns.', 'jasanika' ); ?>
						</p>
					</div>
					<label class="cookie-modal__toggle-switch">
						<input
							type="checkbox"
							id="cookie-pref-marketing"
							name="marketing"
							class="cookie-modal__checkbox"
						>
						<span class="cookie-modal__slider" aria-hidden="true"></span>
					</label>
				</div>
			</div>

		</div>

		<div class="cookie-modal__footer">
			<button
				id="jasanika-cookie-save-prefs"
				class="cookie-banner__btn cookie-banner__btn--primary"
				type="button"
			>
				<?php esc_html_e( 'Save Preferences', 'jasanika' ); ?>
			</button>
		</div>

	</div>
</div>
