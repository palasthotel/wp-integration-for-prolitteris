<?php

namespace Palasthotel\ProLitteris;

/**
 * Connection settings: a constant in wp-config.php wins over the settings page.
 *
 * | Setting       | Constant                      | Option                       |
 * |---------------|-------------------------------|------------------------------|
 * | enabled       | PH_PRO_LITTERIS               | _pro_litteris_enabled        |
 * | API url       | PH_PRO_LITTERIS_SYSTEM        | _pro_litteris_system         |
 * | credentials   | PH_PRO_LITTERIS_CREDENTIALS   | _pro_litteris_member_id/…    |
 * | auto messages | PRO_LITTERIS_AUTO_MESSAGES    | _pro_litteris_auto_messages  |
 */
class Config {

	const DEFAULT_SYSTEM = "https://owen.prolitteris.ch";

	const OPTION_ENABLED = "_pro_litteris_enabled";
	const OPTION_SYSTEM = "_pro_litteris_system";
	const OPTION_MEMBER_ID = "_pro_litteris_member_id";
	const OPTION_USERNAME = "_pro_litteris_username";
	const OPTION_PASSWORD = "_pro_litteris_password";
	const OPTION_AUTO_MESSAGES = "_pro_litteris_auto_messages";

	public static function isEnabledByConstant(): bool {
		return defined( 'PH_PRO_LITTERIS' );
	}

	public static function isEnabled(): bool {
		if ( self::isEnabledByConstant() ) {
			return true === PH_PRO_LITTERIS;
		}

		return (bool) get_option( self::OPTION_ENABLED, false );
	}

	public static function isSystemByConstant(): bool {
		return defined( 'PH_PRO_LITTERIS_SYSTEM' );
	}

	public static function system(): string {
		$system = self::isSystemByConstant()
			? PH_PRO_LITTERIS_SYSTEM
			: get_option( self::OPTION_SYSTEM, self::DEFAULT_SYSTEM );

		return is_string( $system ) && '' !== $system ? untrailingslashit( $system ) : self::DEFAULT_SYSTEM;
	}

	public static function areCredentialsByConstant(): bool {
		return defined( 'PH_PRO_LITTERIS_CREDENTIALS' );
	}

	/**
	 * "member number:username:password" - the API expects it base64-encoded after
	 * "OWEN " in the Authorization header - or "" if not configured.
	 */
	public static function credentials(): string {
		if ( self::areCredentialsByConstant() ) {
			return is_string( PH_PRO_LITTERIS_CREDENTIALS ) ? PH_PRO_LITTERIS_CREDENTIALS : "";
		}
		$memberId = (string) get_option( self::OPTION_MEMBER_ID, "" );
		$username = (string) get_option( self::OPTION_USERNAME, "" );
		$password = (string) get_option( self::OPTION_PASSWORD, "" );
		if ( '' === $memberId || '' === $username || '' === $password ) {
			return "";
		}

		return "$memberId:$username:$password";
	}

	/**
	 * API url and credentials are there.
	 */
	public static function hasConnection(): bool {
		return '' !== self::credentials() && '' !== self::system();
	}

	public static function isAutoMessagesByConstant(): bool {
		return defined( 'PRO_LITTERIS_AUTO_MESSAGES' );
	}

	public static function isAutoMessagesEnabled(): bool {
		if ( self::isAutoMessagesByConstant() ) {
			return true === PRO_LITTERIS_AUTO_MESSAGES;
		}

		return (bool) get_option( self::OPTION_AUTO_MESSAGES, false );
	}
}
