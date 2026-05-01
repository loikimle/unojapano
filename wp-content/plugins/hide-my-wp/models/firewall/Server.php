<?php
/**
 * Firewall Protection
 * Called when the Firewall Protection is activated
 *
 * @file  The Firewall file
 * @package HMWP/Firewall
 * @since 5.0
 */

defined( 'ABSPATH' ) || die( 'Cheating uh?' );

class HMWP_Models_Firewall_Server {

	private $ip;

	private $ips = array();

	/**
	 * Retrieves the client IP address from a list of server variables.
	 * The method prioritizes filtering and validating IPs, excluding localhost addresses.
	 * If a valid IP address is found, it is returned; otherwise, a default value is returned.
	 *
	 * @return string The client's IP address if a valid one is found, or '127.0.0.1' as the default.
	 */
	public function getIp() {

		if ( isset( $this->ip ) ) {
			return $this->ip;
		}

		$this->ip = '127.0.0.1';
		$ips = $this->getServerVariableIPs();

		if ( ! empty( $ips ) ) {
			foreach ( $ips as $ip ) {
				$ip = trim( (string) $ip );

				if ( $ip === '127.0.0.1' || $ip === '::1' || $this->isPrivate( $ip ) ) {
					continue;
				}

				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					$this->ip = $ip;
				}
			}
		}

		return $this->ip;
	}


	/**
	 * Get validated IPs from caller server
	 *
	 * @return array
	 */
	public function getServerVariableIPs() {

		if ( ! empty($this->ips) ) {
			return $this->ips;
		}

		$ips       = array();

		// Set valid headers
		$headers = $this->getValidHeaders();

		foreach ( $headers as $header ) {
			$ip = $_SERVER[ $header ] ?? false; //phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			if ( $ip && strpos( $ip, ',' ) !== false ) {
				$ip = preg_replace( '/[\s,]/', '', explode( ',', $ip ) );
				if ( $clean_ip = $this->getCleanIp( $ip ) ) {
					$ips[ $header ] = $clean_ip;
				}
			} else {
				if ( $clean_ip = $this->getCleanIp( $ip ) ) {
					$ips[ $header ] = $clean_ip;
				}
			}
		}

		// set the ips for this call
		$this->ips = $ips;

		return $ips;
	}

	/**
	 * Get valid headers for the real IP
	 *
	 * @return string[]
	 */
	public function getValidHeaders() {

		// List of the valid header Ips
		return array(
			// CloudFlare IP address
			'HTTP_CF_CONNECTING_IP',
			// Real IP address behind proxy
			'HTTP_X_REAL_IP',
			'HTTP_X_MIDDLETON_IP',
			// Remote IP address
			'REMOTE_ADDR',
		);
	}


    /**
     * Return the verified IP
     *
     * @param $ip
     *
     * @return array|bool|mixed|string|string[]|null
     */
	public function getCleanIp( $ip ) {

		if ( ! $this->isValidIP( $ip ) ) {
			$ip = preg_replace( '/:\d+$/', '', $ip );
		}

		if ( $this->isValidIP( $ip ) ) {
			if ( ! $this->isIPv6MappedIPv4( $ip ) ) {
				$ip = $this->inetNtop( $this->inetPton( $ip ) );
			}

			return $ip;
		}

		return false;

	}

	/**
	 * @param $ip
	 *
	 * @return bool
	 */
	private function isIPv6MappedIPv4( $ip ) {
		return preg_match( '/^(?:\:(?:\:0{1,4}){0,4}\:|(?:0{1,4}\:){5})ffff\:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/i', $ip ) > 0;
	}

	private function inetNtop( $ip ) {
		if ( strlen( $ip ) == 16 && substr( $ip, 0, 12 ) == "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" ) {
			$ip = substr( $ip, 12, 4 );
		}

		return self::isIPv6Support() ? @inet_ntop( $ip ) : $this->_inetNtop( $ip );
	}

	private function _inetNtop( $ip ) {
		// IPv4
		if ( strlen( $ip ) === 4 ) {
			return ord( $ip[0] ) . '.' . ord( $ip[1] ) . '.' . ord( $ip[2] ) . '.' . ord( $ip[3] );
		}

		// IPv6
		if ( strlen( $ip ) === 16 ) {

			// IPv4 mapped IPv6
			if ( substr( $ip, 0, 12 ) == "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" ) {
				return "::ffff:" . ord( $ip[12] ) . '.' . ord( $ip[13] ) . '.' . ord( $ip[14] ) . '.' . ord( $ip[15] );
			}

			$hex           = bin2hex( $ip );
			$groups        = str_split( $hex, 4 );
			$in_collapse   = false;
			$done_collapse = false;
			foreach ( $groups as $index => $group ) {
				if ( $group == '0000' && ! $done_collapse ) {
					if ( $in_collapse ) {
						$groups[ $index ] = '';
						continue;
					}
					$groups[ $index ] = ':';
					$in_collapse      = true;
					continue;
				}
				if ( $in_collapse ) {
					$done_collapse = true;
				}
				$groups[ $index ] = ltrim( $group, '0' );
				if ( strlen( $groups[ $index ] ) === 0 ) {
					$groups[ $index ] = '0';
				}
			}
			$ip = join( ':', array_filter( $groups, 'strlen' ) );
			$ip = str_replace( ':::', '::', $ip );

			return $ip == ':' ? '::' : $ip;
		}

		return false;
	}

    /**
     * Return the packed binary string of an IPv4 or IPv6 address.
     *
     * @param string $ip
     *
     * @return string
     */
	private function inetPton( $ip ) {
		return str_pad( self::isIPv6Support() ? @inet_pton( $ip ) : $this->_inetPton( $ip ), 16, "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff\x00\x00\x00\x00", STR_PAD_LEFT );
	}

	private function _inetPton( $ip ) {
		// IPv4
		if ( preg_match( '/^(?:\d{1,3}(?:\.|$)){4}/', $ip ) ) {
			$octets = explode( '.', $ip );

			return chr( $octets[0] ) . chr( $octets[1] ) . chr( $octets[2] ) . chr( $octets[3] );
		}

		// IPv6
		if ( preg_match( '/^((?:[\da-f]{1,4}(?::|)){0,8})(::)?((?:[\da-f]{1,4}(?::|)){0,8})$/i', $ip ) ) {
			if ( $ip === '::' ) {
				return "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
			}
			$colon_count   = substr_count( $ip, ':' );
			$dbl_colon_pos = strpos( $ip, '::' );
			if ( $dbl_colon_pos !== false ) {
				$ip = str_replace( '::', str_repeat( ':0000', ( ( $dbl_colon_pos === 0 || $dbl_colon_pos === strlen( $ip ) - 2 ) ? 9 : 8 ) - $colon_count ) . ':', $ip );
				$ip = trim( $ip, ':' );
			}

			$ip_groups = explode( ':', $ip );
			$ipv6_bin  = '';
			foreach ( $ip_groups as $ip_group ) {
				$ipv6_bin .= pack( 'H*', str_pad( $ip_group, 4, '0', STR_PAD_LEFT ) );
			}

			return strlen( $ipv6_bin ) === 16 ? $ipv6_bin : false;
		}

		// IPv4 mapped IPv6
		if ( preg_match( '/^(?:\:(?:\:0{1,4}){0,4}\:|(?:0{1,4}\:){5})ffff\:(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/i', $ip, $matches ) ) {
			$octets = explode( '.', $matches[1] );

			return "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff" . chr( $octets[0] ) . chr( $octets[1] ) . chr( $octets[2] ) . chr( $octets[3] );
		}

		return false;
	}

	/**
	 * Verify PHP was compiled with IPv6 support.
	 *
	 * @return bool
	 */
	private function isIPv6Support() {
		return defined( 'AF_INET6' );
	}

	/**
	 * Check and validate IP
	 *
	 * @param $ip
	 *
	 * @return bool
	 */
	private function isValidIP( $ip ) {
		return filter_var( $ip, FILTER_VALIDATE_IP ) !== false;
	}

	/**
	 * Checks an IP to see if it is within a private range
	 *
	 * @param  string  $ip
	 *
	 * @return bool
	 */
	public function isPrivate( $ip ) {

		$private_ips = array(
			'10.0.0.0|10.255.255.255', // single class A network
			'172.16.0.0|172.31.255.255', // 16 contiguous class B network
			'192.168.0.0|192.168.255.255', // 256 contiguous class C network
			'169.254.0.0|169.254.255.255', // Link-local address also referred to as Automatic Private IP Addressing
			'127.0.0.0|127.255.255.255' // localhost
		);

		$long_ip = ip2long( $ip );
		if ( $long_ip != - 1 ) {

			foreach ( $private_ips as $private_ip ) {
				list ( $start, $end ) = explode( '|', $private_ip );

				// If it is a private IP address
				if ( $long_ip >= ip2long( $start ) && $long_ip <= ip2long( $end ) ) {
					return true;
				}
			}
		}

		return false;
	}



}
