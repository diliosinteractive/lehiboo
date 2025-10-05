<?php
/**
 * EventList Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package EventList\Functions
 * @version 1.0
 */
defined( 'ABSPATH' ) || exit;

// var_dump
if ( ! function_exists( 'dd' ) ) {
    function dd( ...$args ) {
        echo '<pre>';
        var_dump( ...$args );
        echo '</pre>';
        die;
    }
}

if( !function_exists( 'el_locate_template' ) ){
	
	function el_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		
		// Set variable to search in templates folder of theme.
		if ( ! $template_path ) :
			$template_path = el_template_path();
		endif;

		// Set default plugin templates path.
		if ( ! $default_path ) :
			$default_path = EL_PLUGIN_PATH . 'templates/'; // Path to the template folder
		endif;

		// Search template file in theme folder.
		$template = locate_template( array(
			trailingslashit( $template_path ) . $template_name
			// $template_name
		) );

		// Get plugins template file.
		if ( ! $template ) :
			$template = $default_path . $template_name;
		endif;

		return apply_filters( 'el_locate_template', $template, $template_name, $template_path, $default_path );
	}

}


function el_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;

	$template_file = el_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html($template_file) ), '1.0.0' );
		return;
	endif;

	// Allow 3rd party plugin filter template file
	$template_file = apply_filters( 'el_get_template', $template_file, $template_name, $args, $template_path, $default_path );

	do_action( 'el_before_template', $template_name, $template_path, $template_file, $args );

	include $template_file;

	do_action( 'el_after_template', $template_name, $template_path, $template_file, $args );
}

if ( ! function_exists( 'el_template_path' ) ) {

	function el_template_path() {
		return apply_filters( 'el_template_path', 'eventlist' );
	}

}

if ( ! function_exists( 'el_get_template_part' ) ) {

	function el_get_template_part( $slug, $name = '', $args = array() ) {

		if ( is_array( $args ) && isset( $args ) ) :
			extract( $args );
		endif;
		
		$template = '';

		// Look in yourtheme/slug-name.php and yourtheme/courses-manage/slug-name.php
		if ( $name ) {
			$template = locate_template( array(
				"{$slug}-{$name}.php",
				el_template_path() . "/{$slug}-{$name}.php"
			) );
		}

		// Get default slug-name.php
		if ( ! $template && $name && file_exists( EL_PLUGIN_PATH . "/templates/{$slug}-{$name}.php" ) ) {
			$template = EL_PLUGIN_PATH . "/templates/{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/courses-manage/slug.php
		if ( ! $template ) {
			$template = locate_template( array( "{$slug}.php", el_template_path() . "{$slug}.php" ) );
		}

		// Allow 3rd party plugin filter template file from their plugin
		if ( $template ) {
			$template = apply_filters( 'el_get_template_part', $template, $slug, $name );
		}
		if ( $template && file_exists( $template ) ) {
			load_template( $template, false, $args );
		}

		return $template;
	}
}


// Get full list currency
if (! function_exists( 'el_get_currencies' )) {
	function el_get_currencies() {
		static $currencies;

		if ( ! isset( $currencies ) ) {
			$currencies = array_unique(
				apply_filters (
					'el_currencies',
					array(
						'AED' => __( 'United Arab Emirates dirham', 'eventlist' ),
						'AFN' => __( 'Afghan afghani', 'eventlist' ),
						'ALL' => __( 'Albanian lek', 'eventlist' ),
						'AMD' => __( 'Armenian dram', 'eventlist' ),
						'ANG' => __( 'Netherlands Antillean guilder', 'eventlist' ),
						'AOA' => __( 'Angolan kwanza', 'eventlist' ),
						'ARS' => __( 'Argentine peso', 'eventlist' ),
						'AUD' => __( 'Australian dollar', 'eventlist' ),
						'AWG' => __( 'Aruban florin', 'eventlist' ),
						'AZN' => __( 'Azerbaijani manat', 'eventlist' ),
						'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'eventlist' ),
						'BBD' => __( 'Barbadian dollar', 'eventlist' ),
						'BDT' => __( 'Bangladeshi taka', 'eventlist' ),
						'BGN' => __( 'Bulgarian lev', 'eventlist' ),
						'BHD' => __( 'Bahraini dinar', 'eventlist' ),
						'BIF' => __( 'Burundian franc', 'eventlist' ),
						'BMD' => __( 'Bermudian dollar', 'eventlist' ),
						'BND' => __( 'Brunei dollar', 'eventlist' ),
						'BOB' => __( 'Bolivian boliviano', 'eventlist' ),
						'BRL' => __( 'Brazilian real', 'eventlist' ),
						'BSD' => __( 'Bahamian dollar', 'eventlist' ),
						'BTC' => __( 'Bitcoin', 'eventlist' ),
						'BTN' => __( 'Bhutanese ngultrum', 'eventlist' ),
						'BWP' => __( 'Botswana pula', 'eventlist' ),
						'BYR' => __( 'Belarusian ruble (old)', 'eventlist' ),
						'BYN' => __( 'Belarusian ruble', 'eventlist' ),
						'BZD' => __( 'Belize dollar', 'eventlist' ),
						'CAD' => __( 'Canadian dollar', 'eventlist' ),
						'CDF' => __( 'Congolese franc', 'eventlist' ),
						'CHF' => __( 'Swiss franc', 'eventlist' ),
						'CLP' => __( 'Chilean peso', 'eventlist' ),
						'CNY' => __( 'Chinese yuan', 'eventlist' ),
						'COP' => __( 'Colombian peso', 'eventlist' ),
						'CRC' => __( 'Costa Rican col&oacute;n', 'eventlist' ),
						'CUC' => __( 'Cuban convertible peso', 'eventlist' ),
						'CUP' => __( 'Cuban peso', 'eventlist' ),
						'CVE' => __( 'Cape Verdean escudo', 'eventlist' ),
						'CZK' => __( 'Czech koruna', 'eventlist' ),
						'DJF' => __( 'Djiboutian franc', 'eventlist' ),
						'DKK' => __( 'Danish krone', 'eventlist' ),
						'DOP' => __( 'Dominican peso', 'eventlist' ),
						'DZD' => __( 'Algerian dinar', 'eventlist' ),
						'EGP' => __( 'Egyptian pound', 'eventlist' ),
						'ERN' => __( 'Eritrean nakfa', 'eventlist' ),
						'ETB' => __( 'Ethiopian birr', 'eventlist' ),
						'EUR' => __( 'Euro', 'eventlist' ),
						'FJD' => __( 'Fijian dollar', 'eventlist' ),
						'FKP' => __( 'Falkland Islands pound', 'eventlist' ),
						'GBP' => __( 'Pound sterling', 'eventlist' ),
						'GEL' => __( 'Georgian lari', 'eventlist' ),
						'GGP' => __( 'Guernsey pound', 'eventlist' ),
						'GHS' => __( 'Ghana cedi', 'eventlist' ),
						'GIP' => __( 'Gibraltar pound', 'eventlist' ),
						'GMD' => __( 'Gambian dalasi', 'eventlist' ),
						'GNF' => __( 'Guinean franc', 'eventlist' ),
						'GTQ' => __( 'Guatemalan quetzal', 'eventlist' ),
						'GYD' => __( 'Guyanese dollar', 'eventlist' ),
						'HKD' => __( 'Hong Kong dollar', 'eventlist' ),
						'HNL' => __( 'Honduran lempira', 'eventlist' ),
						'HRK' => __( 'Croatian kuna', 'eventlist' ),
						'HTG' => __( 'Haitian gourde', 'eventlist' ),
						'HUF' => __( 'Hungarian forint', 'eventlist' ),
						'IDR' => __( 'Indonesian rupiah', 'eventlist' ),
						'ILS' => __( 'Israeli new shekel', 'eventlist' ),
						'IMP' => __( 'Manx pound', 'eventlist' ),
						'INR' => __( 'Indian rupee', 'eventlist' ),
						'IQD' => __( 'Iraqi dinar', 'eventlist' ),
						'IRR' => __( 'Iranian rial', 'eventlist' ),
						'IRT' => __( 'Iranian toman', 'eventlist' ),
						'ISK' => __( 'Icelandic kr&oacute;na', 'eventlist' ),
						'JEP' => __( 'Jersey pound', 'eventlist' ),
						'JMD' => __( 'Jamaican dollar', 'eventlist' ),
						'JOD' => __( 'Jordanian dinar', 'eventlist' ),
						'JPY' => __( 'Japanese yen', 'eventlist' ),
						'KES' => __( 'Kenyan shilling', 'eventlist' ),
						'KGS' => __( 'Kyrgyzstani som', 'eventlist' ),
						'KHR' => __( 'Cambodian riel', 'eventlist' ),
						'KMF' => __( 'Comorian franc', 'eventlist' ),
						'KPW' => __( 'North Korean won', 'eventlist' ),
						'KRW' => __( 'South Korean won', 'eventlist' ),
						'KWD' => __( 'Kuwaiti dinar', 'eventlist' ),
						'KYD' => __( 'Cayman Islands dollar', 'eventlist' ),
						'KZT' => __( 'Kazakhstani tenge', 'eventlist' ),
						'LAK' => __( 'Lao kip', 'eventlist' ),
						'LBP' => __( 'Lebanese pound', 'eventlist' ),
						'LKR' => __( 'Sri Lankan rupee', 'eventlist' ),
						'LRD' => __( 'Liberian dollar', 'eventlist' ),
						'LSL' => __( 'Lesotho loti', 'eventlist' ),
						'LYD' => __( 'Libyan dinar', 'eventlist' ),
						'MAD' => __( 'Moroccan dirham', 'eventlist' ),
						'MDL' => __( 'Moldovan leu', 'eventlist' ),
						'MGA' => __( 'Malagasy ariary', 'eventlist' ),
						'MKD' => __( 'Macedonian denar', 'eventlist' ),
						'MMK' => __( 'Burmese kyat', 'eventlist' ),
						'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'eventlist' ),
						'MOP' => __( 'Macanese pataca', 'eventlist' ),
						'MRO' => __( 'Mauritanian ouguiya', 'eventlist' ),
						'MUR' => __( 'Mauritian rupee', 'eventlist' ),
						'MVR' => __( 'Maldivian rufiyaa', 'eventlist' ),
						'MWK' => __( 'Malawian kwacha', 'eventlist' ),
						'MXN' => __( 'Mexican peso', 'eventlist' ),
						'MYR' => __( 'Malaysian ringgit', 'eventlist' ),
						'MZN' => __( 'Mozambican metical', 'eventlist' ),
						'NAD' => __( 'Namibian dollar', 'eventlist' ),
						'NGN' => __( 'Nigerian naira', 'eventlist' ),
						'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'eventlist' ),
						'NOK' => __( 'Norwegian krone', 'eventlist' ),
						'NPR' => __( 'Nepalese rupee', 'eventlist' ),
						'NZD' => __( 'New Zealand dollar', 'eventlist' ),
						'OMR' => __( 'Omani rial', 'eventlist' ),
						'PAB' => __( 'Panamanian balboa', 'eventlist' ),
						'PEN' => __( 'Sol', 'eventlist' ),
						'PGK' => __( 'Papua New Guinean kina', 'eventlist' ),
						'PHP' => __( 'Philippine peso', 'eventlist' ),
						'PKR' => __( 'Pakistani rupee', 'eventlist' ),
						'PLN' => __( 'Polish z&#x142;oty', 'eventlist' ),
						'PRB' => __( 'Transnistrian ruble', 'eventlist' ),
						'PYG' => __( 'Paraguayan guaran&iacute;', 'eventlist' ),
						'QAR' => __( 'Qatari riyal', 'eventlist' ),
						'RON' => __( 'Romanian leu', 'eventlist' ),
						'RSD' => __( 'Serbian dinar', 'eventlist' ),
						'RUB' => __( 'Russian ruble', 'eventlist' ),
						'RWF' => __( 'Rwandan franc', 'eventlist' ),
						'SAR' => __( 'Saudi riyal', 'eventlist' ),
						'SBD' => __( 'Solomon Islands dollar', 'eventlist' ),
						'SCR' => __( 'Seychellois rupee', 'eventlist' ),
						'SDG' => __( 'Sudanese pound', 'eventlist' ),
						'SEK' => __( 'Swedish krona', 'eventlist' ),
						'SGD' => __( 'Singapore dollar', 'eventlist' ),
						'SHP' => __( 'Saint Helena pound', 'eventlist' ),
						'SLL' => __( 'Sierra Leonean leone', 'eventlist' ),
						'SOS' => __( 'Somali shilling', 'eventlist' ),
						'SRD' => __( 'Surinamese dollar', 'eventlist' ),
						'SSP' => __( 'South Sudanese pound', 'eventlist' ),
						'STD' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'eventlist' ),
						'SYP' => __( 'Syrian pound', 'eventlist' ),
						'SZL' => __( 'Swazi lilangeni', 'eventlist' ),
						'THB' => __( 'Thai baht', 'eventlist' ),
						'TJS' => __( 'Tajikistani somoni', 'eventlist' ),
						'TMT' => __( 'Turkmenistan manat', 'eventlist' ),
						'TND' => __( 'Tunisian dinar', 'eventlist' ),
						'TOP' => __( 'Tongan pa&#x2bb;anga', 'eventlist' ),
						'TRY' => __( 'Turkish lira', 'eventlist' ),
						'TTD' => __( 'Trinidad and Tobago dollar', 'eventlist' ),
						'TWD' => __( 'New Taiwan dollar', 'eventlist' ),
						'TZS' => __( 'Tanzanian shilling', 'eventlist' ),
						'UAH' => __( 'Ukrainian hryvnia', 'eventlist' ),
						'UGX' => __( 'Ugandan shilling', 'eventlist' ),
						'USD' => __( 'United States (US) dollar', 'eventlist' ),
						'UYU' => __( 'Uruguayan peso', 'eventlist' ),
						'UZS' => __( 'Uzbekistani som', 'eventlist' ),
						'VEF' => __( 'Venezuelan bol&iacute;var', 'eventlist' ),
						'VES' => __( 'Bol&iacute;var soberano', 'eventlist' ),
						'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'eventlist' ),
						'VUV' => __( 'Vanuatu vatu', 'eventlist' ),
						'WST' => __( 'Samoan t&#x101;l&#x101;', 'eventlist' ),
						'XAF' => __( 'Central African CFA franc', 'eventlist' ),
						'XCD' => __( 'East Caribbean dollar', 'eventlist' ),
						'XOF' => __( 'West African CFA franc', 'eventlist' ),
						'XPF' => __( 'CFP franc', 'eventlist' ),
						'YER' => __( 'Yemeni rial', 'eventlist' ),
						'ZAR' => __( 'South African rand', 'eventlist' ),
						'ZMW' => __( 'Zambian kwacha', 'eventlist' ),
					)
)
);
}

return $currencies;
}
}


// Get full list currency symbol
if (! function_exists( 'el_get_currency_symbol' )) {
	function el_get_currency_symbol( $currency = '' ) {
		
		$symbols = apply_filters(
			'el_currency_symbols',
			array(
				'AED' => '&#x62f;.&#x625;',
				'AFN' => '&#x60b;',
				'ALL' => 'L',
				'AMD' => 'AMD',
				'ANG' => '&fnof;',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '&#36;',
				'AWG' => 'Afl.',
				'AZN' => 'AZN',
				'BAM' => 'KM',
				'BBD' => '&#36;',
				'BDT' => '&#2547;&nbsp;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'Fr',
				'BMD' => '&#36;',
				'BND' => '&#36;',
				'BOB' => 'Bs.',
				'BRL' => '&#82;&#36;',
				'BSD' => '&#36;',
				'BTC' => '&#3647;',
				'BTN' => 'Nu.',
				'BWP' => 'P',
				'BYR' => 'Br',
				'BYN' => 'Br',
				'BZD' => '&#36;',
				'CAD' => '&#36;',
				'CDF' => 'Fr',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&yen;',
				'COP' => '&#36;',
				'CRC' => '&#x20a1;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => '&#36;',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fr',
				'DKK' => 'DKK',
				'DOP' => 'RD&#36;',
				'DZD' => '&#x62f;.&#x62c;',
				'EGP' => 'EGP',
				'ERN' => 'Nfk',
				'ETB' => 'Br',
				'EUR' => '&euro;',
				'FJD' => '&#36;',
				'FKP' => '&pound;',
				'GBP' => '&pound;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&pound;',
				'GMD' => 'D',
				'GNF' => 'Fr',
				'GTQ' => 'Q',
				'GYD' => '&#36;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x639;.&#x62f;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => 'kr.',
				'JEP' => '&pound;',
				'JMD' => '&#36;',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&yen;',
				'KES' => 'KSh',
				'KGS' => '&#x441;&#x43e;&#x43c;',
				'KHR' => '&#x17db;',
				'KMF' => 'Fr',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '&#36;',
				'KZT' => 'KZT',
				'LAK' => '&#8365;',
				'LBP' => '&#x644;.&#x644;',
				'LKR' => '&#xdbb;&#xdd4;',
				'LRD' => '&#36;',
				'LSL' => 'L',
				'LYD' => '&#x644;.&#x62f;',
				'MAD' => '&#x62f;.&#x645;.',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'Ks',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRO' => 'UM',
				'MUR' => '&#x20a8;',
				'MVR' => '.&#x783;',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => 'MT',
				'NAD' => '&#36;',
				'NGN' => '&#8358;',
				'NIO' => 'C&#36;',
				'NOK' => '&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => '&#8370;',
				'QAR' => '&#x631;.&#x642;',
				'RMB' => '&yen;',
				'RON' => 'lei',
				'RSD' => '&#x434;&#x438;&#x43d;.',
				'RUB' => '&#8381;',
				'RWF' => 'Fr',
				'SAR' => '&#x631;.&#x633;',
				'SBD' => '&#36;',
				'SCR' => '&#x20a8;',
				'SDG' => '&#x62c;.&#x633;.',
				'SEK' => '&#107;&#114;',
				'SGD' => '&#36;',
				'SHP' => '&pound;',
				'SLL' => 'Le',
				'SOS' => 'Sh',
				'SRD' => '&#36;',
				'SSP' => '&pound;',
				'STD' => 'Db',
				'SYP' => '&#x644;.&#x633;',
				'SZL' => 'L',
				'THB' => '&#3647;',
				'TJS' => '&#x405;&#x41c;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T&#36;',
				'TRY' => '&#8378;',
				'TTD' => '&#36;',
				'TWD' => '&#78;&#84;&#36;',
				'TZS' => 'Sh',
				'UAH' => '&#8372;',
				'UGX' => 'UGX',
				'USD' => '&#36;',
				'UYU' => '&#36;',
				'UZS' => 'UZS',
				'VEF' => 'Bs F',
				'VES' => 'Bs.S',
				'VND' => '&#8363;',
				'VUV' => 'Vt',
				'WST' => 'T',
				'XAF' => 'CFA',
				'XCD' => '&#36;',
				'XOF' => 'CFA',
				'XPF' => 'Fr',
				'YER' => '&#xfdfc;',
				'ZAR' => '&#82;',
				'ZMW' => 'ZK',
			)
		);
		
		$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

		return apply_filters( 'el_currency_symbol', $currency_symbol, $currency );
	}
}

if ( ! function_exists ( '_el_symbol_price' ) ) {
	function _el_symbol_price () {
		$currency = EL()->options->general->get( 'currency','USD' );
		$symbol = el_get_currency_symbol( $currency );
		return apply_filters ( 'el_currency_symbol', $symbol, $currency, $symbol );
	}
}

if ( ! function_exists( 'el_price' ) ) {
	function el_price( $price = 0 ) {

		$currency = _el_symbol_price();
		$currency_position = EL()->options->general->get( 'currency_position', 'left' );
		$thousand_separator = EL()->options->general->get( 'thousand_separator', ',' );
		$decimal_separator = EL()->options->general->get( 'decimal_separator', '.' );
		$number_decimals = (int)EL()->options->general->get( 'number_decimals', 2 );

		$price = number_format( (float)$price, $number_decimals, $decimal_separator, $thousand_separator );

		switch ( $currency_position ) {
			case "left" :
				$price = $currency . $price;
				break;

			case "left_space" : 
				$price = $currency . ' ' . $price;
				break;
			
			case "right" : 
				$price = $price . $currency ;
				break;
			
			case "right_space" : 
				$price = $price . ' ' . $currency ;
				break;
			
			default:
				$price = $currency . $price;
				break;
		}

		return $price;
	}
}

if ( ! function_exists('el_pdf_price') ) {
	function el_pdf_price( $price = 0 ){
		$currency = _el_symbol_price();
		$currency_position 	= EL()->options->general->get( 'currency_position', 'left' );
		$thousand_separator = EL()->options->general->get( 'thousand_separator', ',' );
		$decimal_separator 	= EL()->options->general->get( 'decimal_separator', '.' );
		$number_decimals 	= (int)EL()->options->general->get( 'number_decimals', 2 );

		$price = number_format( $price, $number_decimals, $decimal_separator, $thousand_separator );

		$price = '<bdi>'.$price.'</bdi>';

		switch ( $currency_position ) {
			case "left" :
				$price = $currency . $price;
				break;

			case "left_space" : 
				$price = $currency . ' ' . $price;
				break;
			
			case "right" : 
				$price = $price . $currency ;
				break;
			
			case "right_space" : 
				$price = $price . ' ' . $currency ;
				break;
			
			default:
				$price = $currency . $price;
				break;
		}

		return $price;
	}
}

// Get full list social
if (! function_exists( 'el_get_social' )) {
	function el_get_social() {
		static $socials;

		if ( ! isset( $socials ) ) {
			$socials = array_unique(
				apply_filters (
					'el_socials',
					array(
						'social_facebook_circle' => __( 'Facebook', 'eventlist' ),
						'social_twitter_circle' => __( 'Twitter', 'eventlist' ),
						'social_tiktok_circle' => __( 'TikTok', 'eventlist' ),
						'social_pinterest_circle' => __( 'Pinterest', 'eventlist' ),
						'social_googleplus_circle' => __( 'Google Plus', 'eventlist' ),
						'social_tumblr_circle' => __( 'Tumblr', 'eventlist' ),
						'social_tumbleupon' => __( 'StumbleUpon', 'eventlist' ),
						'social_wordpress' => __( 'Wordpress', 'eventlist' ),
						'social_instagram_circle' => __( 'Instagram', 'eventlist' ),
						'social_dribbble_circle' => __( 'Dribbble', 'eventlist' ),
						'social_vimeo_circle' => __( 'Vimeo', 'eventlist' ),
						'social_linkedin_circle' => __( 'LinkedIn', 'eventlist' ),
						'social_myspace_circle' => __( 'Myspace', 'eventlist' ),
						'social_skype_circle' => __( 'Skype', 'eventlist' ),
						'social_youtube_circle' => __( 'Youtube', 'eventlist' ),
						'social_picassa_circle' => __( 'Picassa', 'eventlist' ),
						'social_googledrive_alt2' => __( 'Google Drive', 'eventlist' ),
						'social_flickr_circle' => __( 'Flickr', 'eventlist' ),
						'social_blogger_circle' => __( 'Blogger', 'eventlist' ),
						'social_spotify_circle' => __( 'Spotify', 'eventlist' ),
						'social_delicious_circle' => __( 'Delicious', 'eventlist' ),
					)
				)
			);
		}
		return $socials;
	}
}

function get_myaccount_page(){
	$myaccount_page_id = EL()->options->general->get('myaccount_page_id');
	$myaccount_page_id_wpml = apply_filters( 'wpml_object_id', $myaccount_page_id, 'event' );
	return $myaccount_page_id_wpml ? esc_url( get_permalink( $myaccount_page_id_wpml ) ) : home_url();
}

function get_login_page(){
	if( class_exists( 'Ova_Login_Plugin' ) ){

		$ops = get_option('ovalg_options');
		$login_page = isset( $ops['login_page'] ) ? $ops['login_page'] : '';
		$login_page_id_wpml = apply_filters( 'wpml_object_id', $login_page, 'event' );
		
		return get_the_permalink( $login_page_id_wpml );

	}else{

		return wp_login_url();
	}
}

function get_cart_page(){
	$cart_page_id = EL()->options->general->get('cart_page_id');
	$cart_page_id_wpml = apply_filters( 'wpml_object_id', $cart_page_id, 'event' );
	return $cart_page_id_wpml ? esc_url( get_permalink( $cart_page_id_wpml ) ) : home_url();
}

function el_payment_gateways_active(){
	return EL()->payment_gateways->el_payment_gateways_active();
}

function get_thanks_page(){
	$thanks_page_id = EL()->options->general->get('thanks_page_id');
	$thanks_page_id_wpml = apply_filters( 'wpml_object_id', $thanks_page_id, 'event' );
	return $thanks_page_id_wpml ? esc_url( get_permalink( $thanks_page_id_wpml ) ) : home_url();
}

function get_search_result_page(){
	$search_result_page_id = EL()->options->general->get('search_result_page_id');
	$search_result_page_id_wpml = apply_filters( 'wpml_object_id', $search_result_page_id, 'event' );
	return $search_result_page_id_wpml ? esc_url( get_permalink( $search_result_page_id_wpml ) ) : home_url();
}

function get_checkout_woo_page(){
	$woocommerce_checkout_page_id =  get_option( ' woocommerce_checkout_page_id ' ) ;

	$woocommerce_checkout_page_id_wpml = apply_filters( 'wpml_object_id', $woocommerce_checkout_page_id, 'product' );

	return $woocommerce_checkout_page_id_wpml ? esc_url( get_permalink( $woocommerce_checkout_page_id_wpml ) ) : home_url();

}

// Listing posts per page
add_action( 'pre_get_posts', 'el_listing_posts_per_page' );
function el_listing_posts_per_page ( $query ) {
	$vendor = isset($_GET['vendor']) ? $_GET['vendor'] : '';
	if ( ! is_admin() ) {
		if ($vendor == 'listing') {
			$query->set('posts_per_page', EL()->options->event->get( 'listing_posts_per_page' ) );
			remove_action( 'pre_get_posts', 'el_listing_posts_per_page' );
		}
	}
};

// Fix get data for Author Page
add_action( 'pre_get_posts', 'el_custom_author_query' );
function el_custom_author_query( $query ) {

	if (  ! is_admin() && $query->is_main_query() && is_author() ) {

		$event_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : apply_filters( 'el_author_listing_event_status', 'all' );
		$event_status_first_time = EL()->options->general->get('event_status_first_time','');
		$current_time = current_time( 'timestamp' );

		$query->set( 'post_type', 'event' );
		$query->set( 'posts_per_page', apply_filters( 'el_my_listing_posts_per_page', 9 ) );
		$query->set( 'author', get_query_var( 'author' ) );
		$query->set( 'post_status', 'publish' );

		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', OVA_METABOX_EVENT.'start_date_str' );

		if ( $event_status !== 'all' ) {

			if ( $event_status_first_time !== 'pass' ) {

				switch ( $event_status ) {
					case 'opening':
						$query->set('meta_query',array(
						'relation' => 'AND',
							array(
								'key' => OVA_METABOX_EVENT . 'start_date_str',
								'value' => $current_time,
								'compare' => '<=',
								'type'	=> 'NUMERIC'
							),
							array(
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '>=',
								'type'	=> 'NUMERIC'
							)
						));
						break;
					case 'upcoming':
						$query->set('meta_query',array(
							'relation' => 'AND',
								array(
									'key' => OVA_METABOX_EVENT . 'end_date_str',
									'value' => $current_time,
									'compare' => '>',
									'type'	=> 'NUMERIC'
								),
								array(
									'relation' => 'OR',
									array(
										'key' => OVA_METABOX_EVENT . 'start_date_str',
										'value' => $current_time,
										'compare' => '>',
										'type'	=> 'NUMERIC',
									),
									array(
										'key' => OVA_METABOX_EVENT . 'option_calendar',
										'value' => 'auto',
										'compare' => '=',
									),
								)
							));
						break;
					case 'past':

					$query->set('meta_query',array(
						'relation' => 'AND',
							array(
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '<',
								'type'	=> 'NUMERIC'
							),
						));
						break;
					default:
						break;
				}

			} else {

				switch ( $event_status ) {
					case 'opening':
						$query->set('meta_query',array(
						'relation' => 'AND',
							array(
								'key' => OVA_METABOX_EVENT . 'event_status',
								'value' => 'opening',
								'compare' => '=',
							),
						));
						break;
					case 'upcoming':
						$query->set('meta_query',array(
						'relation' => 'AND',
							array(
								'key' => OVA_METABOX_EVENT . 'event_status',
								'value' => 'upcoming',
								'compare' => '=',
							),
						));
						break;
					case 'past':
						$query->set('meta_query',array(
						'relation' => 'AND',
							array(
								'key' => OVA_METABOX_EVENT . 'event_status',
								'value' => 'past',
								'compare' => '=',
							),
						));
						break;
					default:
						break;

				}
			}
		}


		remove_action( 'pre_get_posts', 'el_custom_author_query' );
	}
}

function el_sql_upcoming(){

	$current_time = current_time('timestamp');
	$event_status_first_time = EL()->options->general->get('event_status_first_time','');

	$agrs_upcoming = [
		'meta_query' => 
		[
			'relation' => 'AND',
			[
				'key' => OVA_METABOX_EVENT . 'end_date_str',
				'value' => $current_time,
				'compare' => '>',
				'type'	=> 'NUMERIC'
			],
			[
				'relation' => 'OR',
				[
					'key' => OVA_METABOX_EVENT . 'start_date_str',
					'value' => $current_time,
					'compare' => '>',
					'type'	=> 'NUMERIC'
				],
				[
					'key' => OVA_METABOX_EVENT . 'option_calendar',
					'value' => 'auto',
					'compare' => '='
				],
			]

		]
	];

	if ( $event_status_first_time == 'pass' ) {
		$agrs_upcoming = [
			'meta_query' => 
			[
				'relation' => 'AND',
				[
					'key' => OVA_METABOX_EVENT . 'event_status',
					'value' => 'upcoming',
					'compare' => '=',
				],
			]
		];
	}

	return $agrs_upcoming;
}

function el_sql_filter_status_event( $filter_events ){

	$current_time 				= current_time('timestamp');
	$event_status_first_time 	= EL()->options->general->get('event_status_first_time','');
	$args_filter_events 		= array();
	
	switch ($filter_events) {

		case 'upcoming':

		if ( $event_status_first_time == 'pass' ) {
			$args_filter_events = array(
				'meta_query' => array(
					array(
						'relation' => 'AND',
						array(
							'key' => OVA_METABOX_EVENT . 'event_status',
							'value' => 'upcoming',
							'compare' => '=',
						),
					)
				)
			);
		} else {
			$args_filter_events = array(
				'meta_query' => array(
					array(
						'relation' => 'AND',
						array(
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '>',
							'type'	=> 'NUMERIC'
						),
						array(
							'relation' => 'OR',
							array(
								'key' => OVA_METABOX_EVENT . 'start_date_str',
								'value' => $current_time,
								'compare' => '>',
								'type'	=> 'NUMERIC'
							),
							array(
								'key' => OVA_METABOX_EVENT . 'option_calendar',
								'value' => 'auto',
								'compare' => '='
							),
						)
					)
				)
			);
		}

		break;

		case 'opening_upcoming':

				$args_filter_events = array(
					'meta_query' => array(
						array(
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						)
					)
				);

		break;

		case 'opening':

		if ( $event_status_first_time == 'pass' ) {
				
			$args_filter_events = array(
				'meta_query' => array(
					array(
						'relation' => 'AND',
						array(
							'key' => OVA_METABOX_EVENT . 'event_status',
							'value' => 'opening',
							'compare' => '=',
						),
					)
				)
			);

		} else {

			$args_filter_events = array(
				'meta_query' => array(
					array(
						'relation' => 'AND',
						array(
							'key' => OVA_METABOX_EVENT . 'start_date_str',
							'value' => $current_time,
							'compare' => '<=',
							'type'	=> 'NUMERIC'
						),
						array(
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '>=',
							'type'	=> 'NUMERIC'
						)
					)
				)
			);
		}

		break;

		case 'past':

		if ( $event_status_first_time == 'pass' ) {
			$args_filter_events = array(
				'meta_query' => array(
					array(
						'relation' => 'AND',
						array(
							'key' => OVA_METABOX_EVENT . 'event_status',
							'value' => 'past',
							'compare' => '=',
						),
					)
				)
			);
		} else {
			$args_filter_events = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT . 'end_date_str',
						'value' => $current_time,
						'compare' => '<',
						'type'	=> 'NUMERIC'
					)
				)
			);
		}

		break;

		default:
		break;
	}

	return $args_filter_events;
}

// Posts per page Archive 
add_action( 'pre_get_posts', 'el_post_per_page_archive' );
function el_post_per_page_archive( $query ) {

	if ( (is_post_type_archive( 'event' )  && !is_admin())  || (is_tax('event_cat') && !is_admin()) || (is_tax('event_loc') && !is_admin()) || (is_tax('event_tag') && !is_admin()) ) {

		$query->set('posts_per_page', EL()->options->event->get( 'listing_posts_per_page' ) );

		$orderby 	= EL()->options->event->get( 'archive_order_by' );
		$order 		= EL()->options->event->get( 'archive_order' );

		$event_status_first_time 	= EL()->options->general->get('event_status_first_time','');
		$filter_status_events 		= EL()->options->event->get('filter_events', 'all');

		$current_time = current_time('timestamp');

		$filter_event = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
		
		if ( empty($filter_event) ) {

			switch ($filter_status_events) {

				case 'upcoming':

				$el_sql_upcoming = el_sql_upcoming();
				$query->set(
					'meta_query', $el_sql_upcoming['meta_query']
				);
				break;

				case 'opening_upcoming':

					$query->set(
						'meta_query', array(
							array(
								'key'      => OVA_METABOX_EVENT . 'end_date_str',
								'value'    => $current_time,
								'compare'  => '>',
								'type' => 'NUMERIC',
							)
						)
					);

				break;

				case 'opening':

				if ( $event_status_first_time == 'pass' ) {

					$query->set(
						'meta_query', 
						array(
							array(
								'key'      => OVA_METABOX_EVENT . 'event_status',
								'value'    => 'opening',
								'compare'  => '='
							)
						)
					);

				} else {

					$query->set(
						'meta_query',
							array(
								'relation' => 'AND',
								array(
									'key' => OVA_METABOX_EVENT . 'start_date_str',
									'value' => $current_time,
									'compare' => '<=',
									'type' => 'NUMERIC',
								),
								array(
									'key' => OVA_METABOX_EVENT . 'end_date_str',
									'value' => $current_time,
									'compare' => '>=',
									'type' => 'NUMERIC',
								)
							)
					);

				}

				
				break;

				case 'past':

				if ( $event_status_first_time == 'pass' ) {

					$query->set(
						'meta_query',
						array(
							array(
								'key' => OVA_METABOX_EVENT . 'event_status',
								'value' => 'past',
								'compare' => '=',
							)
						)
					);

				} else {

					$query->set(
						'meta_query',
						array(
							array(
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '<',
								'type' => 'NUMERIC',
							)
						)
					);

				}

				break;
				
				default:
				break;
			}

		} else {

			//is category event_cat
			if ( is_tax('event_cat') || is_tax('event_loc') || is_tax('event_tag') ) {

				switch ( $filter_event ) {

					case 'feature' :

						if( apply_filters( 'el_show_past_in_feature', true ) ){

							$query->set(
								'meta_query',
								[
									[
										'key' => OVA_METABOX_EVENT . 'event_feature',
										'value' => 'yes',
										'compare' => '=',
									]
								]
							);

						}else{

							$query->set(
								'meta_query',
								[
									'relation' => 'AND',
									[
										'key' => OVA_METABOX_EVENT . 'event_feature',
										'value' => 'yes',
										'compare' => '=',
									],
									[
										'key'      => OVA_METABOX_EVENT . 'end_date_str',
										'value'    => $current_time,
										'compare'  => '>',
										'type' => 'NUMERIC',
									]
								]
							);

						}

					break;

					case 'upcoming' :
						
						$el_sql_upcoming = el_sql_upcoming();
						$query->set(
							'meta_query', $el_sql_upcoming['meta_query']
						);
					break;

					case 'selling' :

						if ( $event_status_first_time == 'pass' ) {

							$query->set(
								'meta_query',
								array(
									array(
										'key'      => OVA_METABOX_EVENT . 'event_status',
										'value'    => 'opening',
										'compare'  => '='
									)
								)
							);

						} else {

							$query->set(
								'meta_query',
									array(
										'relation' => 'AND',
										array(
											'key' => OVA_METABOX_EVENT . 'start_date_str',
											'value' => $current_time,
											'compare' => '<=',
											'type' => 'NUMERIC',
										),
										array(
											'key' => OVA_METABOX_EVENT . 'end_date_str',
											'value' => $current_time,
											'compare' => '>=',
											'type' => 'NUMERIC',
										)
									)
							);

						}

					break;

					case 'closed' :

						if ( $event_status_first_time == 'pass' ) {

							$query->set(
								'meta_query',
								array(
									array(
										'key' => OVA_METABOX_EVENT . 'event_status',
										'value' => 'past',
										'compare' => '=',
									)
								)
							);

						} else {

							$query->set(
								'meta_query',
								array(
									array(
										'key' => OVA_METABOX_EVENT . 'end_date_str',
										'value' => $current_time,
										'compare' => '<',
										'type' => 'NUMERIC',
									)
								)
							);

						}

					break;
				}
			}
		}


		switch ( $orderby ) {
			case 'start_date':
				$query->set('orderby', array( 'meta_value_num' => $order ) );
				$query->set('meta_key', OVA_METABOX_EVENT . 'start_date_str');
			break;

			case 'end_date':
				$query->set('orderby', array( 'meta_value_num' => $order ) );
				$query->set('meta_key', OVA_METABOX_EVENT . 'end_date_str');
			break;
			case 'near':
				$query->set('orderby', 'post__in' );
				$query->set('order','ASC');
			break;

			case 'date_desc':
				$query->set('orderby', 'date' );
				$query->set('order','DESC');
			break;

			case 'date_asc':
				$query->set('orderby', 'date' );
				$query->set('order','ASC');
			break;

			default:
				$query->set( 'order',  'DESC');
				$query->set('orderby', 'ID' );
			break;
		}
		
		remove_action( 'pre_get_posts', 'el_post_per_page_archive' );
	}
}

//post per page event_cat, event_tag, event_loc
add_action( 'pre_get_posts', 'el_post_per_page_event_cat_tag_loc' );
function el_post_per_page_event_cat_tag_loc ( $query ) {

	if ( is_tax( 'event_cat' ) || is_tax( 'event_tag' ) || is_tax( 'event_loc' ) && !is_admin() ) {
		$query->set('posts_per_page', EL()->options->event->get( 'listing_posts_per_page' ) );
	}
}

if ( ! function_exists( 'el_get_time_int_by_date_and_hour' ) ) {
	function el_get_time_int_by_date_and_hour ($date = 0, $time = 0) {
		$time_arr = explode(':', $time);
		$hour_time = 0;

		if ( !empty( $time_arr ) && is_array( $time_arr ) && count( $time_arr ) > 1) {
			$hour_time = floatval( $time_arr[0] );

			if ( strpos($time_arr[1], "AM") !== false )  {
				$time_arr[1] = str_replace('AM', '', $time_arr[1]);
				$hour_time = ($hour_time != 12) ? $hour_time : 0;
			}

			if ( strpos($time_arr[1], "PM") !== false && $time_arr[0] !== "12" )  {
				$time_arr[1] = str_replace('PM', '', $time_arr[1]);
				$hour_time = $hour_time + 12;
			}

			if ( strpos($time_arr[1], "PM") !== false && $time_arr[0] == "12" ) {
				$time_arr[1] = str_replace('PM', '', $time_arr[1]);
				$hour_time = $hour_time;
			}

			$min_time = floatval( $time_arr[1] );
			$hour_time = $hour_time + $min_time / 60;
		}
		$total_time = strtotime( $date ) + $hour_time * 3600;

		return $total_time;
	}
}

if ( ! function_exists( 'get_recurrence_days' ) ) {
	function get_recurrence_days( $recurrence_freq, $recurrence_interval, $recurrence_bydays, $recurrence_byweekno, $recurrence_byday, $start_date, $end_date ){
		/* get timestampes for start and end dates, both at 12AM */
		$start_date = (new DateTime($start_date))->setTime(0,0,0);
		$end_date = (new DateTime($end_date))->setTime(0,0,0);
		$start_date_str = $start_date->getTimestamp();
		$end_date_str = $end_date->getTimestamp();
		$weekdays = $recurrence_bydays; //what days of the week (or if monthly, one value at index 0)
		$weekday = $recurrence_byday; //what day of the week

		$matching_days = array(); //the days we'll be returning in timestamps
		/* generate matching dates based on frequency type */
		switch ( $recurrence_freq ){
			

			case 'weekly':
			/* sort out week one, get starting days and then days that match time span of event (i.e. remove past events in week 1) */
			$current_date = $start_date;
			$start_of_week = get_option('start_of_week'); //Start of week depends on WordPress

			/* then get the timestamps of weekdays during this first week, regardless if within event range */
			$start_weekday_dates = array(); //Days in week 1 where there would events, regardless of event date range
			for($i = 0; $i < 7; $i++){
				if( in_array( $current_date->format('w'), $weekdays) ){
					$start_weekday_dates[] = $current_date->getTimestamp(); //it's in our starting week day, so add it
				}
				$current_date->add( new DateInterval('P1D') ); //add a day
			}

			/* for each day of eventful days in week 1, add 7 days * weekly intervals */
			foreach ($start_weekday_dates as $weekday_date){
				/* Loop weeks by interval until we reach or surpass end date */
				$current_date->setTimestamp($weekday_date);
				while($current_date->getTimestamp() <= $end_date_str){
					if( $current_date->getTimestamp() >= $start_date_str && $current_date->getTimestamp() <= $end_date_str ){
						$matching_days[] = $current_date->getTimestamp();
					}
					$current_date->add( new DateInterval('P'. ($recurrence_interval * 7 ) .'D'));
				}
			} 
			break; 

			case 'monthly':
			/* loop months starting this month by intervals */
			$current_date = $start_date->modify('first day of this month'); //Start date on first day of month
			while( $current_date->getTimestamp() <= $end_date_str ){
				$last_day_of_month = $current_date->format('t');
				/* Now find which day we're talking about */
				$current_week_day = $current_date->format('w');
				$matching_month_days = array();
				/* Loop through days of this years month and save matching days to temp array */
				for($day = 1; $day <= $last_day_of_month; $day++){
					if((int) $current_week_day == $weekday){
						$matching_month_days[] = $day;
					}
					$current_week_day = ($current_week_day < 6) ? $current_week_day + 1 : 0;							
				}
				/* Now grab from the array the x day of the month */
				$matching_day = false;
				if( $recurrence_byweekno > 0 ){
					/* date might not exist (e.g. fifth Sunday of a month) so only add if it exists */
					if( !empty($matching_month_days[$recurrence_byweekno-1]) ){
						$matching_day = $matching_month_days[$recurrence_byweekno-1];
					}
				}else{
					/* last day of month, so we pop the last matching day */
					$matching_day = array_pop($matching_month_days);
				}
				/* if we have a matching day, get the timestamp, make sure it's within our start/end dates for the event, and add to array if it is */
				if( !empty($matching_day) ){
					$matching_date = $current_date->setDate( $current_date->format('Y'), $current_date->format('m'), $matching_day )->getTimestamp();
					if($matching_date >= $start_date_str && $matching_date <= $end_date_str){
						$matching_days[] = $matching_date;
					}
				}
				/* add the monthly interval to the current date */
				$current_date->add( new DateInterval('P'.$recurrence_interval.'M') )->modify('first day of this month');
			}
			break;

			case 'yearly':
			/* Yearly is easy, we get the start date as a cloned EL_DateTime and keep adding a year until it surpasses the end EL_DateTime value. */
			$EL_DateTime = $start_date;
			while( $EL_DateTime <= $end_date ){
				$matching_days[] = $EL_DateTime->getTimestamp();
				$EL_DateTime->add( new DateInterval('P'.$recurrence_interval.'Y'));
			}			
			break;

			
			default:
			/* If daily, it's simple. Get start date, add interval timestamps to that and create matching day for each interval until end date.*/
			$current_date = $start_date;
			while( $current_date->getTimestamp() <= $end_date_str ){
				$matching_days[] = $current_date->getTimestamp();
				$current_date->add( new DateInterval('P'.$recurrence_interval.'D') ) ;
			}
			break;
		}
		sort($matching_days);
		return apply_filters('el_events_get_recurrence_days', $matching_days);
		
	}
}

if ( ! function_exists('get_arr_list_calendar_by_id_event') ) {
	function get_arr_list_calendar_by_id_event ( $id_event ) {
		$option_calendar = get_post_meta( $id_event, OVA_METABOX_EVENT . 'option_calendar' );
		$option = "";
		if (is_array($option_calendar) && isset($option_calendar[0]) ) {
			$option = $option_calendar[0];
		}

		switch ( $option ) {
			case "manual" : {
				$calendars = get_post_meta( $id_event, OVA_METABOX_EVENT.'calendar', true );
				
				break;
			}
			case "auto" : {
				$calendars = get_post_meta( $id_event, OVA_METABOX_EVENT.'calendar_recurrence', true );
				break;
			}
			default : {
				$calendars = [];
			}
		}
		if ( empty( $calendars ) ) {
			$calendars = [];
		}
		return $calendars;
	}
}



function el_get_calendar_core( $id_event, $id_cal ){
	if( ! $id_event || ! $id_cal ) return;
	$list_calendar = get_arr_list_calendar_by_id_event($id_event);

	if( is_array($list_calendar) && !empty($list_calendar) ){
		foreach ($list_calendar as $key => $cal) {
			if( (string)$cal['calendar_id'] === $id_cal ) {
				return $cal;
			}
		}
	}

	return;
}

/* Only Show User Images Upload */
function only_show_user_images( $query = array() ) {
	$current_userID = get_current_user_id();
	if ( $current_userID && !current_user_can('administrator')) {
		$query['author'] = $current_userID;
	}
	return $query;
}
add_filter( 'ajax_query_attachments_args', 'only_show_user_images' );


function hex2rgb($hex) {
	$hex = str_replace("#", "", $hex);

	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);

	return $rgb; 
}

function pagination_vendor($total, $paged = null) {

	$current_page = (empty($paged)) ? get_query_var( 'paged' ) : $paged;

	$html = '<nav class="el-pagination">';
	$html .= paginate_links( apply_filters( 'el_pagination_args', array(
		'base'         => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
		'format'       => '',
		'add_args'     => '',
		'current'      => max( 1,  $current_page),
		'total'        => $total,
		'prev_text'    => __( 'Previous', 'eventlist' ),
		'next_text'    => __( 'Next', 'eventlist' ),
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3
	) ) );
	$html .= '</nav>';
	return $html;
}

add_action('after_setup_theme', 'el_hooks');
function el_hooks(){
	/* Image thumbnail event author page */
	$el_thumbnail = apply_filters( 'el_thumbnail', array( 150, 150 ) );
	add_image_size( 'el_thumbnail', $el_thumbnail[0], $el_thumbnail[1], false );

	/* Image thumbnail event author page */
	$thumbnail_single_page = apply_filters( 'thumbnail_single_page', array( 1920, 739 ) );
	add_image_size( 'thumbnail_single_page', $thumbnail_single_page[0], $thumbnail_single_page[1], true );

	// Image for archive
	$el_img_rec = apply_filters( 'el_img_rec', array( 710, 355 ) );
	add_image_size( 'el_img_rec', $el_img_rec[0], $el_img_rec[1], true );

	$el_img_squa = apply_filters( 'el_img_squa', array( 710, 480 ) );
	add_image_size( 'el_img_squa', $el_img_squa[0], $el_img_squa[1], true );

	
	/* Thumbnail for gallery in event detail */
	$el_thumbnail_gallery = apply_filters( 'el_thumbnail_gallery', array( 150, 150 ) );
	add_image_size( 'el_thumbnail_gallery', $el_thumbnail_gallery[0], $el_thumbnail_gallery[1], true );	

	/* Large Image for Gallery */
	$el_large_gallery = apply_filters( 'el_large_gallery', array( 710, 480 ) );
	add_image_size( 'el_large_gallery', $el_large_gallery[0], false );
}



/* Create the rating interface. */
add_action( 'comment_form_before_fields', 'comment_rating_field' );
add_action( 'comment_form_logged_in_after', 'comment_rating_field' );

function comment_rating_field () {
	if ( is_singular( 'event' ) ) {
		?>
		<div class="wrap_rating">
			<label for="rating" class="second_font"><?php esc_html_e( 'Rating', 'eventlist' ); ?></label>
			<fieldset class="comments-rating">
				<span class="rating-container">
					<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
						<input type="radio" id="rating-<?php echo esc_attr( $i ); ?>" name="rating" value="<?php echo esc_attr( $i ); ?>" data-value="<?php echo esc_attr( $i ); ?>"/>
						<label class="star" for="rating-<?php echo esc_attr( $i ); ?>" ><?php echo esc_html( $i ); ?></label>
					<?php endfor; ?>
				</span>
			</fieldset>
		</div>
		<?php
	}
}

/* Save the rating submitted by the user. */
add_action( 'comment_post', 'comment_rating_save' );
function comment_rating_save( $comment_id ) {
	if ( ( isset( $_POST['rating'] ) ) && ( '' !== $_POST['rating'] ) ) {
		$rating = intval( $_POST['rating'] );
	} elseif ( !isset( $_POST['rating'] ) || 0 === intval( $_POST['rating'] || '' !== $_POST['rating'] ) )  {
		$rating = 0;
	}

	if($rating > 5) $rating = 5 ;

	add_comment_meta( $comment_id, 'rating', $rating );
}

/* Display the rating on a submitted comment. */
// add_filter( 'comment_text', 'comment_rating_display_rating');
function comment_rating_display_rating(){
	$comment_text = '';
	if ( $rating = get_comment_meta( get_comment_ID(), 'rating', true ) ) {
		$stars = '<p class="stars">';
		for ( $i = 1; $i <= 5; $i++ ) {
			if ( $i <= $rating ) {
				$stars .= '<span class="icon_star"></span>';
			} else {
				$stars .= '<span class="icon_star_alt"></span>';
			}
		}
		$stars .= '</p>';
		$count_stars = '<p class="count_star">'.esc_html($rating).'</p>';
		$comment_text = $comment_text . $count_stars . $stars;
		return $comment_text;
	} else {
		return $comment_text;
	}
}



//function sub string in word
function sub_string_word ($content = "", $number = 0) {
	$content = sanitize_text_field($content);
	$number = (int)$number;
	if (empty($content) || empty($number)) return $content;
	$sub_string = substr($content, 0, $number);
	if( $sub_string == $content ) return $content;
	$content = substr($sub_string, 0, strrpos($sub_string, ' ', 0));
	return $content.'...';
}

// date time format
if ( ! function_exists( 'el_date_time_format_js' ) ) {
	function el_date_time_format_js() {
		
		// set detault datetime format datepicker
		$EL_Setting = EL()->options->general;

		$date_format = $EL_Setting->get( 'cal_date_format', 'dd-mm-yy' ) ? $EL_Setting->get( 'cal_date_format', 'dd-mm-yy' ) : 'dd-mm-yy';
		

		return apply_filters( 'el_date_time_format_js', $date_format );
	}
}

// date time format reverse
if ( ! function_exists( 'el_date_time_format_js_reverse' ) ) {
	function el_date_time_format_js_reverse($dateFormat) {
		// set detault datetime format datepicker

		switch ( $dateFormat ) {

			case 'dd-mm-yy':
			$return = 'd-m-Y';
			break;

			case 'mm/dd/yy':
			$return = 'm/d/Y';
			break;


			case 'yy/mm/dd':
			$return = 'Y/m/d';
			break;

			case 'yy-mm-dd':
			$return = 'Y-m-d';
			break;


			default:
			$return = 'd-m-Y';
			break;
		}

		return apply_filters( 'el_date_time_format_js_reverse', $return );
	}
}

// Get full list languge calendar
if (! function_exists( 'el_get_calendar_language' )) {
	function el_get_calendar_language() {

		$symbols = array(
			'en-GB' => 'English/UK',
			'af' => 'Afrikaans',
			'ar-DZ' => 'Algerian Arabic',
			'ar' => 'Algerian',
			'ar' => 'Arabic',
			'az' => 'Azerbaijani',
			'be' => 'Belarusian',
			'bg' => 'Bulgarian',
			'bs' => 'Bosnian',
			'ca' => 'Inicialització',
			'cs' => 'Czech',
			'cy-GB' => 'Welsh/UK',
			'da' => 'Danish',
			'de' => 'German',
			'el' => 'Greek',
			'en-AU' => 'English/Australia',
			'en-NZ' => 'English/New Zealand',
			'eo' => 'Esperanto',
			'es' => 'Spanish',
			'et' => 'Estonian',
			'eu' => 'Karrikas-ek',
			'fa' => 'Persian (Farsi)',
			'fi' => 'Finnish',
			'fo' => 'Faroese',
			'fr-CA' => 'Canadian-French',
			'fr-CH' => 'Swiss-French',
			'fr' => 'French',
			'gl' => 'Galician',
			'he' => 'Hebrew',
			'hi' => 'Hindi',
			'hr' => 'Croatian',
			'hu' => 'Hungarian',
			'hy' => 'Armenian',
			'id' => 'Indonesian',
			'is' => 'Icelandic',
			'it-CH' => 'Italian',
			'ja' => 'Japanese',
			'ka' => 'Georgian',
			'kk' => 'Kazakh',
			'km' => 'Khmer',
			'ko' => 'Korean',
			'ky' => 'Kyrgyz',
			'lb' => 'Luxembourgish',
			'lt' => 'Lithuanian',
			'lv' => 'Latvian',
			'mk' => 'Macedonian',
			'ml' => 'Malayalam',
			'ms' => 'Malaysian',
			'nb' => 'Norwegian Bokmål',
			'nl-BE' => 'Dutch (Belgium)',
			'nl' => 'Dutch',
			'nn' => 'Norwegian Nynorsk',
			'no' => 'Norwegian',
			'pl' => 'Polish',
			'pt-BR' => 'Brazilian',
			'pt' => 'Portuguese',
			'rm' => 'Romansh',
			'ro' => 'Romanian',
			'ru' => 'Russian',
			'sk' => 'Slovak',
			'sl' => 'Slovenian',
			'sq' => 'Albanian',
			'sr-SR' => 'Serbian',
			'sr' => 'Serbian',
			'sv' => 'Swedish',
			'ta' => 'Tamil',
			'th' => 'Thai',
			'tj' => 'Tajiki',
			'tr' => 'Turkish',
			'uk' => 'Ukrainian',
			'vi' => 'Vietnamese',
			'zh-CN' => 'Chinese',
			'zh-HK' => 'Chinese (Hong Kong)',
			'zh-TW' => 'Chinese (Taiwan)',
		);

		return apply_filters( 'el_get_calendar_language', $symbols );
	}
}


if ( ! function_exists('get_commission_admin')) {
	function get_commission_admin($id_event, $total_before_tax, $number_ticket_paid, $number_ticket_free) {
		if ( $id_event == null ) return 0;

		$membership_id = get_post_meta( $id_event, OVA_METABOX_EVENT.'membership_id', true );

		if ( ! empty( $membership_id ) ) {
			$package_slug 	= get_post_meta( $membership_id, OVA_METABOX_EVENT.'membership_package_id', true );
			$package_id 	= EL_Package::get_id_package_by_id_meta( $package_slug );
		} else {
			$package_id = get_post_id_package_by_event( $id_event );
		}

		$fee_percent_paid_ticket = floatval( get_post_meta( $package_id, OVA_METABOX_EVENT . 'fee_percent_paid_ticket', true ) );
		$fee_default_paid_ticket = floatval( get_post_meta( $package_id, OVA_METABOX_EVENT . 'fee_default_paid_ticket', true ) );

		$fee_percent_free_ticket = floatval( get_post_meta( $package_id, OVA_METABOX_EVENT . 'fee_percent_free_ticket', true ) );
		$fee_default_free_ticket = floatval( get_post_meta( $package_id, OVA_METABOX_EVENT . 'fee_default_free_ticket', true ) );
		
		$total_admin = ( $fee_percent_paid_ticket * floatval( $total_before_tax ) ) / 100 + ( absint( $number_ticket_paid ) * $fee_default_paid_ticket ) + ( $fee_default_free_ticket * absint( $number_ticket_free ) );
		
		return apply_filters('el_get_commission_admin', floatval( $total_admin ) );
	}
}


// Get Total before tax in an Event
if ( ! function_exists('get_total_before_tax_by_id_event')) {
	function get_total_before_tax_by_id_event( $id_event = null ) {
		
		if ($id_event == null) return ;

		$list_booking_complete_by_id_event = EL_Booking::instance()->get_list_booking_complete_by_id_event($id_event);

		$total_before_tax = 0;

		if (!empty($list_booking_complete_by_id_event) && is_array($list_booking_complete_by_id_event)) {

			foreach($list_booking_complete_by_id_event as $booking_id) {

				$total_before_tax += get_post_meta( $booking_id, OVA_METABOX_EVENT . 'total', true );

			}
		}

		return apply_filters('el_get_total_before_tax_id_event', $total_before_tax, $id_event);
	}
}


// Get Total after tax in an Event
if ( ! function_exists('get_total_after_tax_by_id_event')) {
	function get_total_after_tax_by_id_event( $id_event = null ) {
		
		if ($id_event == null) return ;

		$list_booking_complete_by_id_event = EL_Booking::instance()->get_list_booking_complete_by_id_event($id_event);

		$total_after_tax = 0;

		if (!empty($list_booking_complete_by_id_event) && is_array($list_booking_complete_by_id_event)) {

			foreach($list_booking_complete_by_id_event as $booking_id) {

				$total_after_tax += get_post_meta( $booking_id, OVA_METABOX_EVENT . 'total_after_tax', true );

			}
		}

		return apply_filters('el_get_total_after_tax_id_event', $total_after_tax, $id_event);
	}
}


// Get Total Profit in an Event
if ( ! function_exists('get_profit_by_id_event')) {
	function get_profit_by_id_event( $id_event = null ) {

		if ($id_event == null) return ;

		$list_booking_complete_by_id_event = EL_Booking::instance()->get_list_booking_complete_by_id_event($id_event);

		$profit = 0;

		if (!empty($list_booking_complete_by_id_event) && is_array($list_booking_complete_by_id_event)) {

			foreach($list_booking_complete_by_id_event as $booking_id) {

				if( get_post_meta( $booking_id, OVA_METABOX_EVENT . 'profit', true ) ){ // Use from version 1.3.7
					$profit += get_post_meta( $booking_id, OVA_METABOX_EVENT . 'profit', true );
				}else{
					$profit += EL_Booking::instance()->get_profit_by_id_booking( $booking_id );	
				}

			}
		}

		return apply_filters('el_get_profit_id_event', $profit, $id_event);
	}
}

// Get Total Commission in an Event
if ( ! function_exists('get_commission_by_id_event')) {
	function get_commission_by_id_event( $id_event = null ) {

		if ($id_event == null) return ;

		$list_booking_complete_by_id_event = EL_Booking::instance()->get_list_booking_complete_by_id_event($id_event);

		$commission = 0;

		if (!empty($list_booking_complete_by_id_event) && is_array($list_booking_complete_by_id_event)) {

			foreach($list_booking_complete_by_id_event as $booking_id) {

				if( get_post_meta( $booking_id, OVA_METABOX_EVENT . 'commission', true ) ){ // Use from version 1.3.7
					$commission += get_post_meta( $booking_id, OVA_METABOX_EVENT . 'commission', true );
				}else{
					$commission += EL_Booking::instance()->get_commission_by_id_booking( $booking_id );	
				}

			}
		}

		return apply_filters('el_get_profit_id_event', $commission, $id_event);
	}
}



// Get Total Tax in an Event
if ( ! function_exists('get_tax_by_id_event')) {
	function get_tax_by_id_event( $id_event = null ) {
		
		if ($id_event == null) return ;

		$list_booking_complete_by_id_event = EL_Booking::instance()->get_list_booking_complete_by_id_event($id_event);

		$tax = 0;

		if (!empty($list_booking_complete_by_id_event) && is_array($list_booking_complete_by_id_event)) {

			foreach($list_booking_complete_by_id_event as $booking_id) {

				if( get_post_meta( $booking_id, OVA_METABOX_EVENT . 'tax', true ) ){ // Use from version 1.3.7
					$tax += get_post_meta( $booking_id, OVA_METABOX_EVENT . 'tax', true );
				}else{
					$tax += EL_Booking::instance()->get_tax_by_id_booking( $booking_id );	
				}

			}
		}

		return apply_filters('el_get_total_id_event', $tax, $id_event);
	}
}


if ( !function_exists('el_pagination_event_ajax') ) {
	function el_pagination_event_ajax( $total, $limit, $current  ) {

		$pages = ceil($total / $limit);

		if ($pages > 1) {
			?>
			<input type="hidden" name="pagination_submit" value="1">
			<ul class="page-numbers">

				<?php if( $current > 1 ) { ?>
					<li><a href="#"><span data-paged="<?php echo esc_attr($current - 1); ?>" class="prev page-numbers" ><?php esc_html_e( 'Previous', 'eventlist' ); ?></span></a></li>
				<?php } ?>

				<?php for ($i = 1; $i < $pages+1; $i++) { ?>
					<li><a href="#"><span data-paged="<?php echo esc_attr($i); ?>" class="page-numbers <?php echo esc_attr( ($current == $i) ? 'current' : '' ); ?>"><?php echo esc_html($i); ?></span></a></li>
				<?php } ?>

				<?php if( $current < $pages ) { ?>
					<li><a href="#"><span data-paged="<?php echo esc_attr($current + 1); ?>" class="next page-numbers" ><?php esc_html_e( 'Next', 'eventlist' ); ?></span></a></li>
				<?php } ?>

			</ul>
			<?php
		}
	}
}

if ( !function_exists('get_number_event_by_seting_element_cat') ) {
	function get_number_event_by_seting_element_cat ( $category, $filter_event ) {
		$current_time = current_time( 'timestamp' );
		$event_status_first_time = EL()->options->general->get('event_status_first_time','');
		$agr_base = [
			'fields' => 'ids',
			'post_type' => 'event',
			'post_status' => 'publish',
			'posts_per_page' => -1, 
			'numberposts' => -1,
			'nopaging' => true,
		];

		$agrs_cat = [];
		if ($category != 'all') {
			$agrs_cat = [
				'tax_query' =>[
					[
						'taxonomy' => 'event_cat',
						'field' => 'slug',
						'terms' => $category,
					]
				]
			];
		}

		switch ( $filter_event ) {
			case 'feature' : {

				if( apply_filters( 'el_show_past_in_feature', true ) ){

					$agrs_status = [
						'meta_query' => [
							[
								'key' => OVA_METABOX_EVENT . 'event_feature',
								'value' => 'yes',
								'compare' => '=',
							],
						],
					];

				}else{

					$agrs_status = [
						'meta_query' => [
							'relation' => 'AND',
							[
								'key' => OVA_METABOX_EVENT . 'event_feature',
								'value' => 'yes',
								'compare' => '=',
							],
							[
								'key'      => OVA_METABOX_EVENT . 'end_date_str',
								'value'    => $current_time,
								'compare'  => '>',
								'type'	=> 'NUMERIC'
							]
						],
					];

				}

				break;
			}
			case 'upcoming' : {

				$agrs_status = el_sql_upcoming();
				break;
			}
			case 'opening_upcoming':

					$agrs_status = [
						'meta_query' => [
							[
								'key'      => OVA_METABOX_EVENT . 'end_date_str',
								'value'    => $current_time,
								'compare'  => '>',
								'type'	=> 'NUMERIC',
							],
						],
					];

				break;
			case 'selling' : {

				if ( $event_status_first_time == 'pass' ) {

					$agrs_status = [
						'meta_query' => [
							[
								'key'      => OVA_METABOX_EVENT . 'event_status',
								'value'    => 'opening',
								'compare'  => '=',
							],
						],
					];

				} else {

					$agrs_status = [
						'meta_query' => [
							'relation' => 'AND',
							[
								'key' => OVA_METABOX_EVENT . 'start_date_str',
								'value' => $current_time,
								'compare' => '<=',
								'type'	=> 'NUMERIC'
							],
							[
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '>=',
								'type'	=> 'NUMERIC'
							]
						],
					];

				}

				break;
			}

			case 'closed' : {

				if ( $event_status_first_time == 'pass' ) {
					$agrs_status = [
						'meta_query' => [
							[
								'key'      => OVA_METABOX_EVENT . 'event_status',
								'value'    => 'past',
								'compare'  => '=',
							],
						],
					];
				} else {
					$agrs_status = [
						'meta_query' => [
							[
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '<',
								'type'	=> 'NUMERIC'
							]
						],
					];
				}

				break;
			}

			default : {
				$agrs_status = [];
			}
		}

		$args = array_merge($agr_base, $agrs_status, $agrs_cat);
		$events = get_posts($args);
		$number_event = count($events);
		return $number_event;
	}
}


if ( !function_exists('get_number_event_by_seting_element_loc') ) {
	function get_number_event_by_seting_element_loc ( $id_loc, $filter_event ) {

		$event_status_first_time = EL()->options->general->get('event_status_first_time','');
		$current_time = current_time( 'timestamp' );
		$agr_base = [
			'fields' => 'ids',
			'post_type' => 'event',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'numberposts' => -1,
			'nopaging' => true,

		];

		switch ( $filter_event ) {
			case 'feature' : {

				if( apply_filters( 'el_show_past_in_feature', true ) ){

					$agrs_status = [
						'meta_query' => [
							[
								'key' => OVA_METABOX_EVENT . 'event_feature',
								'value' => 'yes',
								'compare' => '=',
							],
						],
					];

				}else{

					$agrs_status = [
						'meta_query' => [
							'relation' => 'AND',
							[
								'key' => OVA_METABOX_EVENT . 'event_feature',
								'value' => 'yes',
								'compare' => '=',
							],
							[
								'key'      => OVA_METABOX_EVENT . 'end_date_str',
								'value'    => $current_time,
								'compare'  => '>',
								'type'	=> 'NUMERIC'
							]
						],
					];

				}
				break;
			}
			case 'upcoming' : {

				$agrs_status = el_sql_upcoming();
				
				break;
			}
			case 'selling' : {

				if ( $event_status_first_time == 'pass' ) {

					$agrs_status = [
						'meta_query' => [
							[
								'key'      => OVA_METABOX_EVENT . 'event_status',
								'value'    => 'opening',
								'compare'  => '=',
							],
						],
					];

				} else {

					$agrs_status = [
						'meta_query' => [
							'relation' => 'AND',
							[
								'key' => OVA_METABOX_EVENT . 'start_date_str',
								'value' => $current_time,
								'compare' => '<=',
								'type'	=> 'NUMERIC'
							],
							[
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '>=',
								'type'	=> 'NUMERIC'
							]
						],
					];

				}

				break;
			}

			case 'closed' : {

				if ( $event_status_first_time == 'pass' ) {

					$agrs_status = [
						'meta_query' => [
							[
								'key'      => OVA_METABOX_EVENT . 'event_status',
								'value'    => 'past',
								'compare'  => '=',
							],
						],
					];

				} else {
					$agrs_status = [
						'meta_query' => [
							[
								'key' => OVA_METABOX_EVENT . 'end_date_str',
								'value' => $current_time,
								'compare' => '<',
								'type'	=> 'NUMERIC'
							]
						],
					];
				}

				break;
			}

			default : {
				$agrs_status = [];
			}
		}
		//end switch

		$agrs_loc = [];
		if ($id_loc) {
			$agrs_loc = [
				'tax_query' => [
					[
						'taxonomy' => 'event_loc',
						'field' => 'id',
						'terms' => $id_loc,
					],
				],
			];
		}

		$args = array_merge($agr_base, $agrs_status, $agrs_loc);

		$events = get_posts($args);
		$number_event = count($events);
		return $number_event;
	}
}

function get_list_event_grid_elementor( $_args = array() ){

	$term_id_filter = isset( $_args['term_id_filter'] ) ? $_args['term_id_filter'] : '';
	$order 			= isset( $_args['order'] ) ? $_args['order'] : '';
	$order_by 		= isset( $_args['order_by'] ) ? $_args['order_by'] : '';
	$total_post 	= isset( $_args['total_count'] ) ? $_args['total_count'] : '';
	$filter_event 	= isset( $_args['filter_event'] ) ? $_args['filter_event'] : '';

	$event_status_first_time = EL()->options->general->get('event_status_first_time','');
	$current_time = current_time('timestamp');
	$args_terms = array();

	$args = [
		'post_type' 		=> 'event',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> $total_post,
		'order' 			=> $order,
	];

	if ( $term_id_filter ) {
		$args_terms = [
			'tax_query' => [
				[
					'taxonomy' => 'event_cat',
					'field'    => 'id',
					'terms'    => $term_id_filter,
				]
			]
		];
	}

	$args_orderby = array();

	switch ( $order_by ) {
		case 'date':
		$args_orderby =  array( 'orderby' => 'date' );

		break;
		case 'title':
		$args_orderby =  array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => OVA_METABOX_EVENT.'start_date_str' );
		break;

		default:
		$args_orderby =  array( 'orderby' => 'ID');
		break;
	}


	switch ( $filter_event ) {
		case 'feature' : {

			if( apply_filters( 'el_show_past_in_feature', true ) ){

				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
					],
				];

			}else{

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}
			
			break;
		}
		case 'upcoming' : {
			
			$agrs_status = el_sql_upcoming();
			break;
		}
		case 'selling' : {

			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'opening',
							'compare'  => '=',
						],
					],
				];

			} else {

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'start_date_str',
							'value' => $current_time,
							'compare' => '<=',
							'type'	=> 'NUMERIC',
						],
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '>=',
							'type'	=> 'NUMERIC',
						]
					],
				];

			}

			break;
		}

		case 'upcoming_selling': {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC',
						]
					],
				];

			break;
		}

		case 'closed' : {

			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'past',
							'compare'  => '=',
						],
					],
				];

			} else {
				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '<',
							'type'	=> 'NUMERIC',
						]
					],
				];
			}

			break;
		}

		default : {
			$agrs_status = [];
		}
	}

	$args = array_merge( $args, $agrs_status, $args_orderby, $args_terms );

	$events = new \WP_Query($args);
	return $events;
}

function get_list_event_recent_elementor (  $order = null, $order_by = null, $total_post = null, $event_ids = null ) {

	$args = [
		'post_type' 		=> 'event',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> $total_post,
		'order' 			=> $order,
	];

	if ( ! $event_ids ) {
		return false;
	} else {
		$args['post__in'] = $event_ids;
	}

	$args_orderby = array();


	switch ( $order_by ) {
		case 'date':
		$args_orderby = array( 'orderby' => 'date' );

		break;
		case 'title':
		$args_orderby = array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby =  array(
			'orderby' 	=> 'meta_value_num',
			'meta_key' 	=> OVA_METABOX_EVENT.'start_date_str'
		);
		break;

		default:
		$args_orderby = array( 'orderby' => 'ID' );
		break;
	}

	$args = array_merge( $args, $args_orderby );

	$events = new \WP_Query($args);
	return $events;
}

function get_list_event_near_elementor( $order = null, $order_by = null, $total_post = null, $filter_event = null, $event_type = null ) {

	$current_time = current_time('timestamp');
	$event_status_first_time = EL()->options->general->get('event_status_first_time','');
	$args = [
		'post_type' 		=> 'event',
		'post_status' 		=> 'publish',
		'posts_per_page' 	=> $total_post,
		'order' 			=> $order,
	];

	$args_event_type = array();
	if ( $event_type ) {
		$args_event_type = array(
			'meta_query' => array(
				array(
					'key' => OVA_METABOX_EVENT . 'event_type',
					'value' => $event_type,
				)
			),
		);
	}
	
	$args_orderby = array();

	switch ( $order_by ) {
		case 'date':
		$args_orderby = array( 'orderby' => 'date' );
		break;

		case 'title':
		$args_orderby = array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby = array(
			'orderby' 	=> 'meta_value_num',
			'meta_key' 	=> OVA_METABOX_EVENT.'start_date_str'
		);
		break;

		default:
		$args_orderby = array( 'orderby' => 'ID');
		break;
	}


	switch ( $filter_event ) {
		case 'feature' : {

			if( apply_filters( 'el_show_past_in_feature', true ) ){

				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
					],
				];

			}else{

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}
			
			break;
		}
		case 'upcoming' : {
			
			$agrs_status = el_sql_upcoming();
			break;
		}
		case 'selling' : {

			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'opening',
							'compare'  => '=',
						],
					],
				];

			} else {

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'start_date_str',
							'value' => $current_time,
							'compare' => '<=',
							'type'	=> 'NUMERIC'
						],
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '>=',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}

			break;
		}

		case 'upcoming_selling': {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			break;
		}

		case 'closed' : {

			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'past',
							'compare'  => '=',
						],
					],
				];

			} else {
				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '<',
							'type'	=> 'NUMERIC'
						]
					],
				];
			}

			break;
		}

		default : {
			$agrs_status = [];
		}
	}

	$args = array_merge($args, $agrs_status, $args_orderby, $args_event_type );

	$events = new \WP_Query($args);
	return $events;
}

function get_list_event_location_by_time_filter ( $order = null, $order_by = null, $total_post = null, $time = null, $ids = null, $status = null ) {

	$args = [
		'post_type' => 'event',
		'post_status' => 'publish',
		'posts_per_page' => $total_post,
		'order' => $order,
		'post__in' => $ids,
	];

	$args_time = array();
	$args_orderby = array();
	
	switch ( $order_by ) {
		case 'date':
		$args_orderby =  array( 'orderby' => 'date' );

		break;
		case 'title':
		$args_orderby =  array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => OVA_METABOX_EVENT.'start_date_str' );
		break;

		default:
		$args_orderby =  array( 'orderby' => 'ID');
		break;
	}
	if ( $time ) {
		$date_format = 'Y-m-d 00:00';
		$today_day = current_time( $date_format);

		// Return number of current day
		$num_day_current = gmdate('w', strtotime( $today_day ) );

		// Check start of week in wordpress
		$start_of_week = get_option('start_of_week');

		// This week
		$week_start = gmdate( 'Y-m-d', strtotime($today_day) - ( ($num_day_current - $start_of_week) *24*60*60) );
		$week_end = gmdate( 'Y-m-d', strtotime($today_day)+ (7 - $num_day_current + $start_of_week )*24*60*60 );
		$this_week = el_getDatesFromRange( $week_start, $week_end );
		$this_week_regexp = implode( '|', $this_week );


		// Get Saturday in this week
		$saturday = strtotime( gmdate($date_format, strtotime('this Saturday')));
		// Get Sunday in this week
		$sunday = strtotime( gmdate( $date_format, strtotime('this Sunday')));
		// Weekend
		$week_end = el_getDatesFromRange( gmdate( 'Y-m-d', $saturday ), gmdate( 'Y-m-d', $sunday ) );
		$week_end_regexp = implode('|', $week_end );



		// Next week Start
		$next_week_start = strtotime($today_day)+ (7 - $num_day_current + $start_of_week )*24*60*60;
				// Next week End
		$next_week_end = $next_week_start+7*24*60*60;

		// Next week
		$next_week = el_getDatesFromRange( gmdate( 'Y-m-d', $next_week_start ), gmdate( 'Y-m-d', $next_week_end ) );
		$next_week_regexp = implode( '|', $next_week );


		// Month Current
		$num_day_current = gmdate('n', strtotime( $today_day ) );

		// First day of next month
		$first_day_next_month = strtotime( gmdate( $date_format, strtotime('first day of next month') ) );
		$last_day_next_month = strtotime ( gmdate( $date_format, strtotime('last day of next month') ) )+24*60*60+1;
		// Next month
		$next_month = el_getDatesFromRange( gmdate( 'Y-m-d', $first_day_next_month ), gmdate( 'Y-m-d', $last_day_next_month ) );
		$next_month_regexp = implode( '|', $next_month );

		switch ( $time ) {
			case 'today':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => strtotime($today_day),
						'compare' => 'LIKE'	
					),
				)
			);

			break;

			case 'tomorrow':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => strtotime($today_day) + 24*60*60,
						'compare' => 'LIKE'	
					),
				)
			);
			break;

			case 'this_week':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $this_week_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			case 'this_weekend':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $week_end_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			case 'next_week':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $next_week_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			case 'next_month':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $next_month_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			default:
						# code...
			break;
		}
	}

	$args_status = array();

	if ( $status ) {
		$args_status = array(
			'meta_query' => array(
				array(
					'key' => OVA_METABOX_EVENT . 'event_type',
					'value' => $status,
				)
			),
		);
	}

	$args = array_merge($args, $args_orderby, $args_time, $args_status );

	$events = new \WP_Query($args);
	return $events;
}

function get_list_event_near_location_elementor ( $order = null, $order_by = null, $filter_event = null ) {

	$current_time = current_time('timestamp');
	$event_status_first_time = EL()->options->general->get('event_status_first_time','');
	$args = [
		'post_type' => 'event',
		'post_status' => 'publish',
		'order' => $order,
		'meta_query' => array(
			array(
				'key' => OVA_METABOX_EVENT . 'event_type',
				'value' => 'classic',
			)
		),
	];

	$args_orderby = array();

	switch ($order_by) {
		case 'date':
		$args_orderby =  array( 'orderby' => 'date' );

		break;
		case 'title':
		$args_orderby =  array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => OVA_METABOX_EVENT.'start_date_str' );
		break;

		default:
		$args_orderby =  array( 'orderby' => 'ID');
		break;
	}


	switch ( $filter_event ) {
		case 'feature' : {

			if( apply_filters( 'el_show_past_in_feature', true ) ){

				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
					],
				];

			}else{

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}
			
			break;
		}
		case 'upcoming' : {
			
			$agrs_status = el_sql_upcoming();
			break;
		}
		case 'selling' : {

			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'opening',
							'compare'  => '=',
						],
					],
				];

			} else {

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'start_date_str',
							'value' => $current_time,
							'compare' => '<=',
							'type'	=> 'NUMERIC'
						],
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '>=',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}

			break;
		}

		case 'upcoming_selling': {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			break;
		}

		case 'closed' : {
			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'past',
							'compare'  => '=',
						],
					],
				];

			} else {
				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '<',
							'type'	=> 'NUMERIC'
						]
					],
				];
			}
			break;
		}

		default : {
			$agrs_status = [];
		}
	}

	$args = array_merge($args, $agrs_status, $args_orderby );

	$events = new \WP_Query($args);
	return $events;
}

function get_list_event_near_by_id ( $order = null, $order_by = null, $total_post = null, $ids = null, $cate_id = null, $status = null ) {
	$args = [
		'post_type' 		=> 'event',
		'post_status' 		=> 'publish',
		'order' 			=> $order,
		'posts_per_page' 	=> $total_post,
	];

	if ( $ids ) {
		$args['post__in'] = $ids;
	}

	$args_orderby = array();

	switch ( $order_by ) {
		case 'date':
		$args_orderby =  array( 'orderby' => 'date' );

		break;
		case 'title':
		$args_orderby =  array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => OVA_METABOX_EVENT.'start_date_str' );
		break;

		default:
		$args_orderby =  array( 'orderby' => 'ID');
		break;
	}

	$args_time = array();

	if ( ! is_numeric( $cate_id ) ) {
		$date_format = 'Y-m-d 00:00';
		$today_day = current_time( $date_format);

		// Return number of current day
		$num_day_current = gmdate('w', strtotime( $today_day ) );

		// Check start of week in wordpress
		$start_of_week = get_option('start_of_week');

		// This week
		$week_start = gmdate( 'Y-m-d', strtotime($today_day) - ( ($num_day_current - $start_of_week) *24*60*60) );
		$week_end = gmdate( 'Y-m-d', strtotime($today_day)+ (7 - $num_day_current + $start_of_week )*24*60*60 );
		$this_week = el_getDatesFromRange( $week_start, $week_end );
		$this_week_regexp = implode( '|', $this_week );


		// Get Saturday in this week
		$saturday = strtotime( gmdate($date_format, strtotime('this Saturday')));
		// Get Sunday in this week
		$sunday = strtotime( gmdate( $date_format, strtotime('this Sunday')));
		// Weekend
		$week_end = el_getDatesFromRange( gmdate( 'Y-m-d', $saturday ), gmdate( 'Y-m-d', $sunday ) );
		$week_end_regexp = implode('|', $week_end );



		// Next week Start
		$next_week_start = strtotime($today_day)+ (7 - $num_day_current + $start_of_week )*24*60*60;
				// Next week End
		$next_week_end = $next_week_start+7*24*60*60;

		// Next week
		$next_week = el_getDatesFromRange( gmdate( 'Y-m-d', $next_week_start ), gmdate( 'Y-m-d', $next_week_end ) );
		$next_week_regexp = implode( '|', $next_week );


		// Month Current
		$num_day_current = gmdate('n', strtotime( $today_day ) );

		// First day of next month
		$first_day_next_month = strtotime( gmdate( $date_format, strtotime('first day of next month') ) );
		$last_day_next_month = strtotime ( gmdate( $date_format, strtotime('last day of next month') ) )+24*60*60+1;
		// Next month
		$next_month = el_getDatesFromRange( gmdate( 'Y-m-d', $first_day_next_month ), gmdate( 'Y-m-d', $last_day_next_month ) );
		$next_month_regexp = implode( '|', $next_month );

		switch ( $cate_id ) {
			case 'today':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => strtotime($today_day),
						'compare' => 'LIKE'	
					),
				)
			);

			break;

			case 'tomorrow':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => strtotime($today_day) + 24*60*60,
						'compare' => 'LIKE'	
					),
				)
			);
			break;

			case 'this_week':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $this_week_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			case 'this_weekend':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $week_end_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			case 'next_week':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $next_week_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			case 'next_month':
			$args_time = array(
				'meta_query' => array(
					array(
						'key' => OVA_METABOX_EVENT.'event_days',
						'value' => $next_month_regexp,
						'compare' => 'REGEXP'	
					),
				)
			);
			break;

			default:
						# code...
			break;
		}
	} else {
		if ( $cate_id != 0 ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'event_cat',
					'field' => 'id',
					'terms' => $cate_id,
					),
			);
		}
	}

	$args_status = array();

	if ( $status ) {
		$args_status = array(
			'meta_query' => array(
				array(
					'key' => OVA_METABOX_EVENT . 'event_type',
					'value' => $status,
				)
			),
		);
	}

	// return $args_cate_id;
	$args = array_merge($args, $args_orderby, $args_status, $args_time );

	$events = new \WP_Query($args);
	return $events;
}

function get_list_event_time_categories(){
	$categories = array(
			'today' 		=> esc_html__( 'Today', 'eventlist' ),
			'tomorrow' 		=> esc_html__( 'Tomorrow', 'eventlist' ),
			'this_week' 	=> esc_html__( 'This Week', 'eventlist' ),
			'this_weekend' 	=> esc_html__( 'This Weekend', 'eventlist' ),
			'next_week' 	=> esc_html__( 'Next Week', 'eventlist' ),
			'next_month' 	=> esc_html__( 'Next Month', 'eventlist' ),
		);
	return $categories;
}

function get_list_event_data( $the_query ){
	if ( ! $the_query ) {
		return false;
	}
	$data_event = [];
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$post_id = get_the_ID();
			$map_lat = get_post_meta( $post_id, OVA_METABOX_EVENT .'map_lat', true );
			$map_lng = get_post_meta( $post_id, OVA_METABOX_EVENT .'map_lng', true );
			if ( $map_lat && $map_lng ) {
				array_push($data_event, array('id' => $post_id, 'lat' => $map_lat, 'lng' => $map_lng ));
			}
		}
	}
	return $data_event;
}

function get_term_id_filter_event_cat_element ( $include_cat = null, $show_all = null ) {
	
	$terms = get_term_by_cat_include ( $include_cat );
	$count = count($terms);
	$term_id_filter = array();
	$first_term = '';
	if (!empty($terms)) {
		$i = 0;
		foreach ( $terms as $term ) {
			$i++;
			$term_id_filter[] = $term->term_id;
			if ($i === 1) {
				$first_term = $term->term_id;
			}
		}
	}

	if ( $show_all === null ) {
		//return string id term
		$term_id_filter_string = implode(",", $term_id_filter);
		return $term_id_filter_string;
	}

	if ($show_all === 'yes' ) {
		//return array id term
		return $term_id_filter;
	} else {
		//return first id term
		return $first_term;
	}
}

function get_term_by_cat_include( $include_cat = null ) {
	$cat_include = [];
	if (!empty($include_cat)) {
		$cat_include =  explode(",",$include_cat);
	}

	$terms = get_terms([
		'taxonomy' => 'event_cat',
		'include' => $cat_include,
	]);

	return $terms;
}

function get_term_ids_by_cat_include( $include_cat = null ) {
	$cat_include = [];
	if (!empty($include_cat)) {
		$cat_include =  explode(",",$include_cat);
	}
	return $cat_include;
}

function get_term_cat_event_by_slug_cat ($category = null) {
	$terms = get_term_by('slug', $category, 'event_cat' );
	$term['cat_name'] = !empty($terms->name) ? $terms->name : "";
	$term['cat_slug'] = !empty($terms->slug) ? $terms->slug : "";
	$term['link'] = !empty($terms->term_id) ? get_term_link($terms->term_id, 'event_cat') : '';
	return $term;
}

function get_term_loc_event_by_id_loc ( $id_loc = null ) {
	$terms = get_term_by('id', $id_loc, 'event_loc' );
	$term['loc_name'] = !empty($terms->name) ? $terms->name : "";
	$term['loc_slug'] = !empty($terms->slug) ? $terms->slug : '';
	$term['loc_link'] = !empty($terms->term_id) ? get_term_link($terms->term_id, 'event_loc') : '';
	return $term;
}

function get_list_event_slider_elementor($category = null, $total_post = null, $order = null, $order_by = null, $filter_event = null) {
	$current_time = current_time('timestamp');
	$event_status_first_time = EL()->options->general->get('event_status_first_time','');
	$args = $args_orderby = [];
	if ($category == 'all') {
		$args=[
			'post_type' 		=> 'event',
			'posts_per_page' 	=> $total_post,
			'no_found_rows'		=> true,
			'order' 			=> $order,
			'post_status' 		=> 'publish',
		];
	} else {
		$args=[
			'post_type' 		=> 'event',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> $total_post,
			'no_found_rows'		=> true,
			'order' 			=> $order,
			'tax_query' => array(
				array(
					'taxonomy' => 'event_cat',
					'field'    => 'slug',
					'terms'    => $category,
				),
			),
		];
	}

	switch ( $order_by ) {
		case 'date':
		$args_orderby =  array( 'orderby' => 'date' );

		break;
		case 'title':
		$args_orderby =  array( 'orderby' => 'title' );
		break;

		case 'start_date':
		$args_orderby =  array( 'orderby' => 'meta_value_num', 'meta_key' => OVA_METABOX_EVENT.'start_date_str' );
		break;

		default:
		$args_orderby =  array( 'orderby' => 'ID');
		break;
	}

	switch ( $filter_event ) {
		case 'feature' : {

			if( apply_filters( 'el_show_past_in_feature', true ) ){

				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
					],
				];

			}else{

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'event_feature',
							'value' => 'yes',
							'compare' => '=',
						],
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}
			break;
		}
		case 'upcoming' : {
			
			$agrs_status = el_sql_upcoming();

			break;
		}
		case 'selling' : {
			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'opening',
							'compare'  => '=',
						],
					],
				];

			} else {

				$agrs_status = [
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => OVA_METABOX_EVENT . 'start_date_str',
							'value' => $current_time,
							'compare' => '<=',
							'type'	=> 'NUMERIC'
						],
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '>=',
							'type'	=> 'NUMERIC'
						]
					],
				];

			}
			break;
		}

		case 'upcoming_selling': {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'end_date_str',
							'value'    => $current_time,
							'compare'  => '>',
							'type'	=> 'NUMERIC'
						]
					],
				];

			break;
		}

		case 'closed' : {
			if ( $event_status_first_time == 'pass' ) {

				$agrs_status = [
					'meta_query' => [
						[
							'key'      => OVA_METABOX_EVENT . 'event_status',
							'value'    => 'past',
							'compare'  => '=',
						],
					],
				];

			} else {
				$agrs_status = [
					'meta_query' => [
						[
							'key' => OVA_METABOX_EVENT . 'end_date_str',
							'value' => $current_time,
							'compare' => '<',
							'type'	=> 'NUMERIC'
						]
					],
				];
			}
			break;
		}

		default : {
			$agrs_status = [];
		}
	}


	$args = array_merge($args, $agrs_status, $args_orderby);

	$events = new \WP_Query($args);
	return $events;
}

// Remove default size image in WordPress
function el_remove_default_image_sizes( $sizes) {
	
	unset( $sizes['thumbnail']);
	unset( $sizes['medium']);
	unset( $sizes['large']);
	unset( $sizes['medium_large']);
	return $sizes;
}
if( EL()->options->general->get('remove_img_size', 'yes') == 'yes' ){
	add_filter('intermediate_image_sizes_advanced', 'el_remove_default_image_sizes');
}

function el_remove_woo_image_sizes( $sizes) {	
	unset( $sizes['woocommerce_gallery_thumbnail'] );
	unset( $sizes['woocommerce_thumbnail'] );
	unset( $sizes['woocommerce_single'] );
	unset( $sizes['shop_thumbnail'] );
	unset( $sizes['shop_catalog'] );
	unset( $sizes['shop_single'] );
	return $sizes;
}
if( EL()->options->general->get( 'remove_woo_img_size', 'yes' ) == 'yes' ){
	add_filter('intermediate_image_sizes_advanced', 'el_remove_woo_image_sizes');
}

if ( !is_admin() ) {
	add_filter('upload_mimes','el_only_upload_image_file'); 
	function el_only_upload_image_file($mimes) { 
		$mimes = array( 
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'bmp'          => 'image/bmp',
			'tif|tiff'     => 'image/tiff',
			'ico'          => 'image/x-icon',
		);

		// Admin Allow Additional File Types to be Uploaded
		if ( EL()->options->general->get('event_upload_file', '') ) {
			$event_upload_file = explode(',', EL()->options->general->get('event_upload_file', '') );

			foreach ($event_upload_file as $value) {
				foreach ( wp_get_mime_types() as $k1 => $v1 ) {
					if ( trim($value) == $k1 ) {
						$mimes[$k1] = $v1;
					}
					if ( trim($value) == 'svg' ) {
						$mimes['svg'] = 'image/svg+xml';
					}
				}
			}
		}

		return $mimes;
	}
}

add_action( 'register_form', 'ova_vender_user_registration_form' );
function ova_vender_user_registration_form() {
	?>
	<p class="form-row">
		<span class="raido_input">
			<input type="radio" name="ova_type_user" value="vendor" id="vendor"><label for="vendor"><?php esc_html_e( 'Vendor', 'eventlist' ); ?></label>
		</span>
		<span class="raido_input">
			<input type="radio" name="ova_type_user" value="user" checked id="user"><label for="user"><?php esc_html_e( 'User', 'eventlist' ); ?></label>
		</span>
	</p>
	<?php
}


if ( ! function_exists( 'report_sales_get_total_after_tax' ) ) {
	function report_sales_get_total_after_tax ( $post_ID = array(), $after = '', $before = '' ) {

		if( empty( $post_ID ) ) return 0;

		$agrs_base_booking = array(
			'post_type' => 'el_bookings',
			'post_status' => 'publish',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					array(
						'key' => OVA_METABOX_EVENT . 'id_event',
						'value' => $post_ID,
						'compare' => 'IN'
					),
					array(
						'key' => OVA_METABOX_EVENT . 'status',
						'value' => 'completed'
					)
				)
			),
			'date_query' => array(
				array(
					'after' => $after,
					'before' => $before,
					'inclusive' => true
				)
			)
		);

		$args_booking = new WP_Query( $agrs_base_booking );

		$total_after_tax = 0;
		if( $args_booking->have_posts() ): while ( $args_booking->have_posts() ) : $args_booking->the_post();
			$total_after_tax += floatval( get_post_meta( get_the_ID(), OVA_METABOX_EVENT . 'total_after_tax', true ) );
		endwhile; wp_reset_query(); endif;

		return $total_after_tax;
	}
}

if ( ! function_exists( 'report_sales_get_data_total_after_tax' ) ) {
	function report_sales_get_data_total_after_tax ( $after, $total_after_tax ) {
		$time_column = strtotime($after) * 1000;

		$data_total_after_tax = array(
			$time_column,
			$total_after_tax
		);

		return $data_total_after_tax ;
	}
}


if ( ! function_exists( 'report_users_get_total_user_registered' ) ) {
	function report_users_get_total_user_registered ( $role, $after, $before ) {
		$args = array(
			'role' => $role,
			'date_query' => array(
				array(
					'after' => $after,
					'before' => $before,
					'inclusive' => true
				)
			)
		);

		
		$users = get_users($args);
		return count($users);
	}
}

if ( ! function_exists( 'report_users_get_data_total_user_registered' ) ) {
	function report_users_get_data_total_user_registered ( $after, $total_user ) {
		$time_column = strtotime($after) * 1000;

		$data_total_user = array(
			$time_column,
			$total_user
		);

		return $data_total_user ;
	}
}




if ( ! function_exists( 'el_calendar_time_format' ) ) {
	function el_calendar_time_format () {

		$EL_Setting = EL()->options->general;
		return $EL_Setting->get('calendar_time_format') == '' ? '12' : $EL_Setting->get('calendar_time_format');
		
	}
}

if ( ! function_exists( 'el_calendar_language' ) ) {
	function el_calendar_language () {

		$EL_Setting = EL()->options->general;
		return $EL_Setting->get('calendar_language', 'en-GB') ? $EL_Setting->get('calendar_language') : 'en-GB';
		
	}
}

if ( ! function_exists( 'el_first_day_of_week' ) ) {
	function el_first_day_of_week () {
		$EL_Setting = EL()->options->general;
		return $EL_Setting->get('first_day_of_week', '1') != '' ? $EL_Setting->get('first_day_of_week', '1') : '1';
		
	}
}


//Lets add Open Graph Meta Info
add_action( 'wp_head', 'el_add_meta_share_facebook', 5 );
function el_add_meta_share_facebook() {

	el_get_template( 'single/share_facebook.php' );

}



// Get WeekDay, Day, Month individual
function el_get_event_w_d_m( $event_id, $type="full" ){

	$time_start = get_post_meta( $event_id, OVA_METABOX_EVENT . 'start_date_str', true  );

	$option_calendar = get_post_meta( $event_id, OVA_METABOX_EVENT.'option_calendar', true);
	$calendar_recurrence = get_post_meta( $event_id, OVA_METABOX_EVENT.'calendar_recurrence', true);
	$calendar = get_post_meta( $event_id, OVA_METABOX_EVENT.'calendar', true);

	$arr_start_date = [];
	if ($option_calendar == 'auto') {
		if ( $calendar_recurrence ) {
			foreach ( $calendar_recurrence as $value ) {
				if ( ( strtotime($value['date']) - strtotime('today') ) >= 0 ) {
					$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
				}
			}
		}
	} else {
		if ($calendar) {
			foreach ( $calendar as $value ) {
				if ( ( strtotime($value['date']) - strtotime('today') ) >= 0 ) {
					$arr_start_date[] = strtotime( $value['date'] .' '. $value['start_time'] );
				}
			}
		}
	}

	if ( $arr_start_date != array() ) {
		$start_date = min($arr_start_date);
	} else {
		$start_date = $time_start;
	}

	if ( !empty($time_start) ) {

		$month_type = $type == 'full' ? 'F' : 'M';

		$month = $start_date ? date_i18n($month_type, $start_date) : '';
		$day = $start_date ? date_i18n('d', $start_date) : '';
		$weekday = $start_date ? date_i18n('l', $start_date) : ''; 

		return array(  'weekday' => $weekday, 'day' => $day, 'month' => $month );

	}else{
		return false;
	}

}


/**
 * Validate selling Ticket
 */
function el_validate_selling_ticket( $start_time, $end_time, $number_time, $event_id = null ){
	$current_time = current_time('timestamp') + $number_time;

	if ( $event_id ) {
		$timezone = get_post_meta( $event_id, OVA_METABOX_EVENT . 'time_zone', true );

		if ( $timezone ) {
			$tz_string 	= el_get_timezone_string( $timezone );
			$datetime 	= new DateTime('now', new DateTimeZone( $tz_string ) );
			$time_now 	= $datetime->format('Y-m-d H:i');

			if ( strtotime( $time_now ) ) {
				$current_time = strtotime( $time_now ) + $number_time;
			}
		}
	}

	if ( $current_time < $start_time || ( $current_time > $start_time && $current_time < $end_time && apply_filters( 'el_allow_book_opening_event', true ) ) ) {
		return true;
	}
	return false;
}

// validate can preview event
function el_can_preview_event(){

	if( isset( $_GET['p'] ) && is_user_logged_in() ){

		if( verify_current_user_post( $_GET['p'] ) && get_post_status( $_GET['p'] ) !== 'publish'  ){

			add_filter( 'body_class', 'el_custom_class' );
			add_filter( 'pre_get_document_title', 'el_filter_document_title' );

			return true;
		}
		return false;
	}
	
}


if( !function_exists('el_custom_class') ){
	function el_custom_class( $classes ) {
	    if ( el_can_preview_event() ) {
	        $classes[] = 'single single-event';
	    }
	    return $classes;
	}
}


function el_filter_document_title( $title ) {

    $title = isset( $_GET['p'] ) ? get_the_title( $_GET['p'] ) : $title;

    return $title; 

}


// Add Min, Max, radius
add_action( 'wp_head', 'el_map_range_radius', 5 );
function el_map_range_radius() { ?>

	<script type="text/javascript">
		var map_range_radius = <?php echo esc_html( apply_filters( 'el_map_range_radius', 50 ) ); ?>;
		var map_range_radius_min = <?php echo esc_html( apply_filters( 'map_range_radius_min', 0 ) ); ?>;
		var map_range_radius_max = <?php echo esc_html( apply_filters( 'map_range_radius_max', 100 ) ); ?>;
	</script>
	

<?php }


// Get time zone of event
function el_get_timezone_event( $eid ){
	if( apply_filters( 'el_show_timezone', true ) ){
		return get_post_meta( $eid, OVA_METABOX_EVENT.'time_zone', true );
	}
}


// Check Cancel Booking
function el_cancellation_booking_valid( $booking_id ){
	$check = false;
	
	// ID of event in booking
	$event_id = get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_event', true );

	// Calendar's ID of ticket in booking
	$id_cal  = get_post_meta( $booking_id, OVA_METABOX_EVENT.'id_cal', true );

	if ( get_post_meta( $event_id, OVA_METABOX_EVENT.'allow_cancellation_booking', true ) == 'yes' ) {
		$event_start_date = el_get_calendar_core( $event_id, $id_cal );	

		if ( $event_start_date ) {
			$event_start_date_tmp 	= strtotime( $event_start_date['date'].' '.$event_start_date['start_time'] );
			$cancel_before_x_day 	= floatval( get_post_meta( $event_id, OVA_METABOX_EVENT.'cancel_before_x_day', true) )*24*60*60;

			if ( $event_start_date_tmp - current_time( 'timestamp' ) > $cancel_before_x_day ) $check = true;
		}
	}

	$cond_other_cancel_booking_valid = apply_filters( 'cond_other_cancel_booking_valid', true, $booking_id );

	return ( $check && $cond_other_cancel_booking_valid );
}


// Function to get all the dates in given range 
function el_getDatesFromRange($start, $end, $format = 'Y-m-d') { 
      
    // Declare an empty array 
    $array = array(); 
      
    // Variable that store the date interval 
    // of period 1 day 
    $interval = new DateInterval('P1D'); 
  
    // $realEnd = new DateTime($end); 
    // $realEnd->add($interval); 
  
    $period = new DatePeriod(new DateTime($start), $interval, new DateTime($end)); 
  
    // Use loop to store date into array 
    foreach($period as $date) {                  
        $array[] = strtotime( $date->format($format));
    } 
  
    // Return the array elements 
    return $array; 
} 

// placeholder dateformat
function el_placeholder_dateformat(){

	$time = el_calendar_time_format();
	$format = el_date_time_format_js();
	return apply_filters( 'el_placeholder_dateformat', el_date_time_format_js_reverse($format) );

}

// placeholder timeformat
function el_placeholder_timeformat(){
	$time = el_calendar_time_format();
	$format = el_date_time_format_js();
	
	return ( $time == '12' ) ? esc_html__( 'HH:MM PM', 'eventlist' ) : esc_html__( 'HH:MM', 'eventlist' );
}

// Create account
if( !function_exists('el_create_account') ){
	function el_create_account( $post_data ){

		$first_name = $post_data['first_name'];
		$last_name = $post_data['last_name'];
		$name = $first_name . $last_name;
		$email = $post_data['email'];
		$phone = $post_data['phone'];
		$address = $post_data['address'];

		$username = el_create_new_customer_username( $email, "" );

		if ( ! validate_username( $username ) ) {
			return false;
		}

		if ( username_exists( $username ) ) {
			return false;
		}

		if ( ! is_email( $email ) ) {
			return false;
		}

		if ( email_exists( $email ) ) {
			return false;
		}


		$user_data = array(
			'user_login'    => $username,
			'user_email'    => $email,
			'first_name'    => $first_name,
			'last_name' 	=> $last_name,
			'nickname'      => $username,
			'user_phone'	=> $phone,
			'user_address'	=> $address,
		);

		$user_id = wp_insert_user( $user_data );

		return $user_id;

	}
}

function el_create_new_customer_username( $email, $new_user_args, $suffix = '' ) {
	
	$username_parts = array();

	$email = strtolower( $email );
	
	// Remove empty parts.
	$username_parts = array( $new_user_args );


	// If there are no parts, e.g. name had unicode chars, or was not provided, fallback to email.
	if ( empty( $new_user_args ) ) {
		$email_parts    = explode( '@', $email );
		$email_username = $email_parts[0];

		// Exclude common prefixes.
		if ( in_array(
			$email_username,
			array(
				'sales',
				'hello',
				'mail',
				'contact',
				'info',
			),
			true
		) ) {
			// Get the domain part.
			$email_username = $email_parts[1];
		}

		$username_parts[] = sanitize_user( $email_username, true );
	}

	$username = implode( '', $username_parts );

	if ( $suffix ) {
		$username .= $suffix;
	}

	/**
	 * WordPress 4.4 - filters the list of blocked usernames.
	 *
	 * @since 3.7.0
	 * @param array $usernames Array of blocked usernames.
	 */
	$illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

	// Stop illegal logins and generate a new random username.
	if ( in_array( strtolower( $username ), array_map( 'strtolower', $illegal_logins ), true ) ) {
		$new_args = array();

		/**
		 * Filter generated customer username.
		 *
		 * @since 3.7.0
		 * @param string $username      Generated username.
		 * @param string $email         New customer email address.
		 * @param array  $new_user_args Array of new user args, maybe including first and last names.
		 * @param string $suffix        Append string to username to make it unique.
		 */
		$new_args = apply_filters(
			'el_generated_customer_username',
			'el_user_' . zeroise( wp_rand( 0, 9999 ), 4 ),
			$email,
			$new_user_args,
			$suffix
		);

		return el_create_new_customer_username( $email, $new_args, $suffix );
	}

	if ( username_exists( $username ) ) {
		// Generate something unique to append to the username in case of a conflict with another user.
		$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
		return el_create_new_customer_username( $email, $new_user_args, $suffix );
	}

	/**
	 * Filter new customer username.
	 *
	 * @since 3.7.0
	 * @param string $username      Customer username.
	 * @param string $email         New customer email address.
	 * @param array  $new_user_args Array of new user args, maybe including first and last names.
	 * @param string $suffix        Append string to username to make it unique.
	 */
	return apply_filters( 'el_new_customer_username', $username, $email, $new_user_args, $suffix );
}


/* Filter Media with Current User */
add_filter( 'ajax_query_attachments_args', 'el_show_current_user_attachments', 10, 1 );	
function el_show_current_user_attachments( $query = array() ) {

	$user_role = wp_get_current_user()->roles;
	$role = isset( $user_role[0] ) ? $user_role[0] : '';

	if(  $role == 'el_event_manager' ){

		$user_id = get_current_user_id();
		if( $user_id ) {
			$query['author'] = $user_id;
		}
		
	}
	return $query;
}



/**
 * Removes the media 'From URL' string.
 *
 * @see wp-includes|media.php
 */
add_filter( 'media_view_strings', 'el_cor_media_view_strings' );
function el_cor_media_view_strings( $strings ) {

	$user_role = wp_get_current_user()->roles;
	$role = isset( $user_role[0] ) ? $user_role[0] : '';

	if(  $role == 'el_event_manager' ){
		
		$user_id = get_current_user_id();
		if( $user_id ) {
			unset( $strings['insertFromUrlTitle'] );
		}
		
	}

	
    
    return $strings;
}


// Chart
function el_get_chart( $get ){

	$id_event = isset($_GET['eid']) ? sanitize_text_field($_GET['eid']) : "";

	$range = isset( $get['range'] ) ? sanitize_text_field( $get['range'] ) : '7_day';
	if ( $range == 'custom' ) {
		$start_date = ( $get['start_date'] && isset( $get['start_date'] ) ) ? sanitize_text_field( $get['start_date'] ) : gmdate( 'Y-m-d', strtotime('-3 years', current_time('timestamp') ) );
		$end_date = ( $get['end_date'] && isset( $get['end_date'] ) ) ? sanitize_text_field( $get['end_date'] ) : gmdate('Y-m-d', current_time('timestamp') );
	} else {
		$start_date = isset( $get['start_date'] ) ? sanitize_text_field( $get['start_date'] ) : gmdate( 'Y-m-d', strtotime('-10 years', current_time('timestamp') ) );
		$end_date = isset( $get['end_date'] ) ? sanitize_text_field( $get['end_date'] ) : gmdate('Y-m-d', current_time('timestamp') );
	}

	$str_start_date = strtotime($start_date);
	$str_end_date = strtotime($end_date);

	$day_start_date = ( new DateTime($start_date) )->format('d');
	$month_start_date = ( new DateTime($start_date) )->format('m');
	$year_start_date = ( new DateTime($start_date) )->format('y');

	$day_end_date = ( new DateTime($end_date) )->format('d');
	$month_end_date = ( new DateTime($end_date) )->format('m');
	$year_end_date = ( new DateTime($end_date) )->format('y');

	$month_current_date = ( new DateTime() )->format('m');
	$year_current_date = ( new DateTime() )->format('y');

	$last_month_current_date = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );

	$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
	$first_month_current_year = strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) );

	$last_month_current_year = strtotime( gmdate( 'Y-12-01', current_time( 'timestamp' ) ) );

	$first_day_last_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );

	$currency = _el_symbol_price();
	$currency_position = EL()->options->general->get( 'currency_position','left' );

	

	if(  $id_event){
		
		$post_ID[] = $id_event;

	}else{
		$post_ID = [];

		// Query Event
		$args_base_event = array(
			'post_type' => 'event',
			'posts_per_page' => -1,
			'author' => wp_get_current_user()->ID,
		);

		$events = new WP_Query( $args_base_event );

		if( $events->have_posts() ): while ( $events->have_posts() ) : $events->the_post();
			$post_ID[] = get_the_ID();
		endwhile; wp_reset_query(); endif;
	}
	
	if ( $range == '7_day' ) {
		$chart_interval = absint( ceil( max( 0, ( $str_end_date - strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ) / ( 60 * 60 * 24 ) ) ) );

	} elseif ($range == 'month') {
		$chart_interval = absint( ceil( max( 0, ( $str_end_date - strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ) / ( 60 * 60 * 24 ) ) ) );

	} elseif ($range == 'last_month') {
		$chart_interval = absint( floor( max( 0, ( strtotime( gmdate( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ) ) - strtotime( gmdate( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) ) ) / ( 60 * 60 * 24 ) ) ) );

	} elseif ($range == 'year') {
		$chart_interval = ( new DateTime() )->format('m');

	} elseif ($range == 'custom') {
		$chart_interval = absint( ceil( max( 0, ( $str_end_date - $str_start_date ) / ( 60 * 60 * 24 ) ) ) );
	}

	// day, this month, last month, year
	if ( $range != 'custom' ) {

		if ( $range == 'year' ) {
			$chart_groupby = 'month';
			$i = $chart_interval;
		} else {
			$chart_groupby = 'day';
			$i = $chart_interval + 1;
		}

		while ( $i > 0  ) {
			$i--;
			if ( $range == 'last_month' ) {
				$after = gmdate('Y-m-d', strtotime( ( '-' . $i ).' days', strtotime( '-1 DAY', $first_day_current_month ) ) );
				$before = $after;

			} elseif ( $range == 'year' ) {
				$after = gmdate('Y-m-01',  strtotime( ('-' . $i . ' Month'), $last_month_current_date ) );
				$before = gmdate( "Y-m-t", strtotime( $after ) );

			} else {
				$after = gmdate('Y-m-d', strtotime( ( '-' . $i ).' days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) );
				$before = $after;
			}

			// Query Booking
			$total_after_tax = report_sales_get_total_after_tax( $post_ID, $after, $before );

			$data_total_after_tax[] = report_sales_get_data_total_after_tax( $after, $total_after_tax );
		}
	}

	// Custom
	if ( $range == 'custom' && $chart_interval >= 100 ) {
		$chart_groupby = 'month';
		$count_month = 0;
		while ( ($str_start_date = strtotime("+1 MONTH", $str_start_date) ) <= $str_end_date) {
			$count_month++;
		}

		$m = ($count_month + 1);

		while ( $m >= 0 ) {
			if ( $m == $count_month + 1 ) {
				$after = gmdate( ( $year_start_date . '-'. $month_start_date .'-' . $day_start_date ) );
				$after = gmdate('Y-m-d',strtotime( $after ) );
				$before = gmdate( "Y-m-t", strtotime( $after ) );

			} elseif ( ( $m > 0 ) && ( $m <= $count_month ) ) {
				$after = gmdate('Y-m-01',  strtotime( ('-' .($m). ' month'), $last_month_current_date ) );
				$before = gmdate( "Y-m-t", strtotime( $after ) );

			} elseif ( $m == 0 ) {
				$after = gmdate( ( $year_end_date . '-'. $month_end_date .'-01' ) );
				$after = gmdate('Y-m-d',strtotime( $after ) );
				$before = gmdate('Y-m-d', $str_end_date);
			}

			// Query Booking
			$total_after_tax = report_sales_get_total_after_tax( $post_ID, $after, $before );

			$data_total_after_tax[] = report_sales_get_data_total_after_tax( $after, $total_after_tax );

			$m --;
		}
	} elseif ( $range == 'custom' && $chart_interval < 100 ) {
		$chart_groupby = 'day';
		$i = $chart_interval;
		while ( $i >= 0  ) {
			$after = gmdate('Y-m-d', strtotime( ( '-' . $i ).' days', $str_end_date ) );
			$before = $after;

			// Query Booking
			$total_after_tax = report_sales_get_total_after_tax( $post_ID, $after, $before );

			$data_total_after_tax[] = report_sales_get_data_total_after_tax( $after, $total_after_tax );

			$i--;
		}
	}

	// Return data chart
	$data_chart = wp_json_encode( [ 
		$data_total_after_tax 
	] );

	$chart_color = get_theme_mod( 'chart_color', '#e86c60' );
	$name_month = array_reduce( range(1,12), function($rslt,$m){ $rslt[$m] = date_i18n('M',mktime(0,0,0,$m,10)); return $rslt; } );

	$timeformat = ( 'day' === $chart_groupby ) ? '%d %b' : '%b';
	$monthNames = rawurlencode( wp_json_encode( array_values( $name_month ) ) );

	return array( 'chart' => $data_chart, 'name_month' => $name_month, 'currency_position' => $currency_position, 'currency' => $currency, 'chart_groupby' => $chart_groupby, 'chart_color' => $chart_color, 'timeformat' => $timeformat, 'monthnames' => $monthNames );

}

function el_get_data_coupon( $id ){
	$list_type_ticket = get_post_meta( get_the_ID(), OVA_METABOX_EVENT . 'ticket', true);
	$event_coupons = get_post_meta($id, OVA_METABOX_EVENT . 'coupon', true);
	$coupon_data = array();
	foreach ( $list_type_ticket as $ticket ) {

		if ( !empty( $event_coupons ) && is_array( $event_coupons ) ) {
			
			foreach ( $event_coupons as $coupon ) {

				$number_coupon_used = EL_Booking::instance()->get_number_coupon_code_used(get_the_ID(),$coupon['discount_code'] );
				$time_start_discount = el_get_time_int_by_date_and_hour($coupon['start_date'], $coupon['start_time']);
				$time_end_discount = el_get_time_int_by_date_and_hour($coupon['end_date'], $coupon['end_time']);
				$current_time = current_time('timestamp');

				if ( $time_start_discount < $current_time && $current_time < $time_end_discount && $coupon['quantity'] > 0  && $coupon['quantity'] > $number_coupon_used ) {

					if ( isset( $coupon['list_ticket'] ) && !empty( $coupon['list_ticket'] ) ) {
						foreach ($coupon['list_ticket'] as $value) {
							if($value == $ticket['ticket_id']){

								if($coupon['discount_amout_number']){
									$currency = EL()->options->general->get( 'currency','USD' );
									$discount_amout = esc_html( $coupon['discount_amout_number'].' '.$currency);
								}elseif ($coupon['discount_amount_percent']) {
									$discount_amout = esc_html( $coupon['discount_amount_percent'].'%');
								}

								$remaining = $coupon['quantity'] - $number_coupon_used;

								$coupon_data[]=[
									'name'=> $coupon['discount_code'],
									'discount'=> $discount_amout,
									'reamaing'=>$remaining,
									'id'=>$value,
								];
							}
						}
					}
				}

			}
		}
	}

	return $coupon_data;

}



// Get Array Product ID with WPML
function el_get_product_ids_multi_lang( $id ){

	$translated_ids = Array();

	// get plugin active
	$active_plugins = get_option('active_plugins');

	if ( in_array ( 'polylang/polylang.php', $active_plugins ) || in_array ( 'polylang-pro/polylang.php', $active_plugins ) ) {
			$languages = pll_languages_list();
			if ( !isset( $languages ) ) return;
			foreach ($languages as $lang) {
				$translated_ids[] = pll_get_post($id, $lang);
			}
	} elseif ( in_array ( 'sitepress-multilingual-cms/sitepress.php', $active_plugins ) ) {
		global $sitepress;
	
		if(!isset($sitepress)) return;
		
		$trid = $sitepress->get_element_trid($id, 'post_event');
		$translations = $sitepress->get_element_translations($trid, 'event');
		foreach( $translations as $lang=>$translation){
		    $translated_ids[] = $translation->element_id;
		}

	} else {
		$translated_ids[] = $id;
	}

	return apply_filters( 'el_multiple_languages', $translated_ids );

}

/**
 * Get timezone string
 */
if( !function_exists('el_get_timezone_string') ){
	function el_get_timezone_string( $timezone ){
		$tz_string = ''; 

		if ( preg_match( '/^UTC[+-]/', $timezone ) ) {
			$offset  = preg_replace( '/UTC\+?/', '', $timezone );
		    $hours   = (int) $offset;
		    $minutes = ( $offset - $hours );
		 
		    $sign      	= ( $offset < 0 ) ? '-' : '+';
		    $abs_hour  	= abs( $hours );
		    $abs_mins  	= abs( $minutes * 60 );
		    $tz_string 	= sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
		} else {
			$tz_string = $timezone;
		}
		
	    return $tz_string;
	}
}

/**
 * Recursive array replace \\
 */
if( !function_exists('recursive_array_replace') ){
	function recursive_array_replace( $find, $replace, $array ) {
	    if ( ! is_array( $array ) ) {
	        return str_replace( $find, $replace, $array );
	    }

	    foreach ( $array as $key => $value ) {
	        $array[$key] = recursive_array_replace( $find, $replace, $value );
	    }

	    return $array;
	}
}

// Get Quantity from Cart
if ( !function_exists( 'el_get_quantity_form_cart' ) ) {
	function el_get_quantity_form_cart( $cart = array() ) {
		$qty = 0;

		if ( !empty( $cart ) && is_array( $cart ) ) {
			foreach( $cart as $ticket ) {
				$qty_ticket = isset( $ticket['qty'] ) && $ticket['qty'] ? absint( $ticket['qty'] ) : 0;
				$qty += $qty_ticket;
			}
		}

		return absint( $qty );
	}
}

// Get Ticket IDs from Cart
if ( !function_exists( 'el_get_ticket_ids_form_cart' ) ) {
	function el_get_ticket_ids_form_cart( $cart = array(), $type = '' ) {
		$ids = array();

		if ( ! empty( $cart ) && is_array( $cart ) ) {
			foreach ( $cart as $ticket ) {
				if ( $type === 'map' ) {
					$id = isset( $ticket['id'] ) && $ticket['id'] ? $ticket['id'] : '';

					$qty = 0;
					if ( isset( $ticket['data_person'] ) ) {
						foreach ($ticket['data_person'] as $key => $value) {
							$qty += (int) $value['qty'];
						}
						for ($i=0; $i < $qty; $i++) { 
							array_push( $ids, $id );
						}
					} else {
						array_push( $ids, $id );

						if ( isset( $ticket['qty'] ) && absint( $ticket['qty'] ) > 1 ) {
							for ( $i = 1; $i < absint( $ticket['qty'] ); $i++ ) {
								array_push( $ids, $id );
							}
						}
					}

				} else {
					$id 	= isset( $ticket['id'] ) && $ticket['id'] ? $ticket['id'] : '';
					$qty 	= isset( $ticket['qty'] ) && $ticket['qty'] ? absint( $ticket['qty'] ) : 1;
					for( $i = 0; $i < $qty; $i++ ) {
						array_push( $ids, $id );
					}
				}
			}
		}

		return $ids;
	}
}

// Get Seat HTML from Cart ( First Ticket Form )
if ( !function_exists( 'el_get_seat_html_form_cart' ) ) {
	function el_get_seat_html_form_cart( $seat_names = array(), $k = 0 ) {

		?>
			<div class="error-empty-input error-select_seats">
				<span class="required">
					<?php echo esc_html__( "field is required", "eventlist" ); ?>
				</span>
				<span class="duplicate_seats">
					<?php echo esc_html__( "error seat duplicate", "eventlist" ); ?>
				</span>
				<span class="area_not_match">
					<?php echo esc_html__( "error area does not match", "eventlist" ); ?>
				</span>
			</div>
			<li class="first_name select_seats">
				<div class="label">
					<label for="select_seats">
						<?php echo esc_html__( "Seat", "eventlist" ); ?>
					</label>
				</div>

				<?php
					if ( isset( $seat_names[$k] ) ) {
						?>
							<input id="select_seats" class="el_multiple_seats required_mult_ticket" value="<?php echo esc_attr( $seat_names[$k] ); ?>" name="select_seats" readonly/>
					<?php } else {
						if ( $k == 0 ) {  ?>
							<input id="select_seats" class="el_multiple_seats required_mult_ticket" value="<?php echo esc_attr( $seat_names[0] ); ?>" name="select_seats" readonly/>
						<?php }
					}
					?>


			</li>

		<?php

	}
}


// Get Ticket Type HTML from Cart
if ( !function_exists( 'el_get_ticket_type_html_form_cart' ) ) {
	function el_get_ticket_type_html_form_cart( $cart = array(), $ticket_name = '' ) {


		if ( ! empty( $cart ) && is_array( $cart ) ) {
			?>
			<div class="error-empty-input error-ticket_type">
				<span class="required">
					<?php echo esc_html__( "field is required ", "eventlist" ); ?>
				</span>
			</div>

			<li class="first_name ticket_type">
				<div class="label">
					<label for="ticket_type">
						<?php echo esc_html__( "Ticket Type", "eventlist" ); ?>
					</label>
				</div>
				<?php
				if ( ! $ticket_name ) {
					foreach ( $cart as $k => $ticket ) {
						$ticket_name = isset( $ticket['name'] ) && $ticket['name'] ? $ticket['name'] : '';
						break;
					}
				}
				?>
				<input id="ticket_type" class="required_mult_ticket" type="text" name="ticket_receiver_ticket_type" value="<?php echo esc_attr($ticket_name); ?>" readonly>

			</li>
			<?php
		}
	}
}

// Check is_tax event
if ( ! function_exists( 'el_is_tax_event' ) ) {
	function el_is_tax_event() {
		$flag = false;

		// Event Taxonomy
		$number_tax = EL()->options->general->get('el_total_taxonomy', 2);

		if ( $number_tax > 0 ) {
			for ( $i = 1; $number_tax >= $i; $i++ ) {
				$param_arr = [];
				$param_arr = apply_filters( 'register_taxonomy_el_' . $i, $param_arr );

				if ( empty( $param_arr ) || ! is_array( $param_arr ) ) {
					$slug_tax = 'taxonomy_default' . $i;
				} else {
					$slug_tax = $param_arr['slug'];
				}

				if ( is_tax( $slug_tax ) ) {
					$flag = $slug_tax;
					break;
				}
			}
		}

		return $flag;
	}
}

function el_get_custom_taxonomy_slug_arr(){
	$number_tax = EL()->options->general->get('el_total_taxonomy', 2);
	$arr = [];
	for ($i=1; $i <= absint( $number_tax ); $i++) { 
		$param_arr = [];
        $param_arr = apply_filters( 'register_taxonomy_el_' . $i, $param_arr );
        $slug = ! empty( $param_arr['slug'] ) ? $param_arr['slug'] : 'taxonomy_default' . $i;
        $arr[] = $slug;
	}
	return $arr;
}

// Get Page by Title
if ( ! function_exists( 'el_get_page_by_title' ) ) {
	function el_get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;

		if ( is_array( $post_type ) ) {
			$post_type           = esc_sql( $post_type );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";


			$page = $wpdb->get_var( $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
				AND post_type IN ($post_type_in_string)",
				$page_title
			) );

		} else {
			$page = $wpdb->get_var( $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
				AND post_type = %s",
				$page_title,
				$post_type
			) );
		}

		if ( $page ) {
			return get_post( $page, $output );
		}

		return null;
	}
}

if ( ! function_exists('ova_lowercase_remove_space') ) {
	function ova_lowercase_remove_space( $value, $field_args, $field ){

		$lower_str = str_replace( " ", "",strtolower($value) );
		preg_match_all('/[a-zA-Z]/', $lower_str, $matched_value);

		$sanitized_value = implode('', $matched_value[0]);

		return $sanitized_value;
	}
}

// ISO 3166-1 alpha-2 codes
if ( ! function_exists( 'ova_event_iso_alpha2' ) ) {
	function ova_event_iso_alpha2() {
		$countries = [
		    'AD' => esc_html__('Andorra', 'eventlist'),
		    'AE' => esc_html__('United Arab Emirates', 'eventlist'),
		    'AF' => esc_html__('Afghanistan', 'eventlist'),
		    'AG' => esc_html__('Antigua and Barbuda', 'eventlist'),
		    'AI' => esc_html__('Anguilla', 'eventlist'),
		    'AL' => esc_html__('Albania', 'eventlist'),
		    'AM' => esc_html__('Armenia', 'eventlist'),
		    'AO' => esc_html__('Angola', 'eventlist'),
		    'AQ' => esc_html__('Antarctica', 'eventlist'),
		    'AR' => esc_html__('Argentina', 'eventlist'),
		    'AS' => esc_html__('American Samoa', 'eventlist'),
		    'AT' => esc_html__('Austria', 'eventlist'),
		    'AU' => esc_html__('Australia', 'eventlist'),
		    'AW' => esc_html__('Aruba', 'eventlist'),
		    'AX' => esc_html__('Åland Islands', 'eventlist'),
		    'AZ' => esc_html__('Azerbaijan', 'eventlist'),
		    'BA' => esc_html__('Bosnia and Herzegovina', 'eventlist'),
		    'BB' => esc_html__('Barbados', 'eventlist'),
		    'BD' => esc_html__('Bangladesh', 'eventlist'),
		    'BE' => esc_html__('Belgium', 'eventlist'),
		    'BF' => esc_html__('Burkina Faso', 'eventlist'),
		    'BG' => esc_html__('Bulgaria', 'eventlist'),
		    'BH' => esc_html__('Bahrain', 'eventlist'),
		    'BI' => esc_html__('Burundi', 'eventlist'),
		    'BJ' => esc_html__('Benin', 'eventlist'),
		    'BL' => esc_html__('Saint Barthélemy', 'eventlist'),
		    'BM' => esc_html__('Bermuda', 'eventlist'),
		    'BN' => esc_html__('Brunei Darussalam', 'eventlist'),
		    'BO' => esc_html__('Bolivia (Plurinational State of)', 'eventlist'),
		    'BQ' => esc_html__('Bonaire, Sint Eustatius and Saba', 'eventlist'),
		    'BR' => esc_html__('Brazil', 'eventlist'),
		    'BS' => esc_html__('Bahamas', 'eventlist'),
		    'BT' => esc_html__('Bhutan', 'eventlist'),
		    'BV' => esc_html__('Bouvet Island', 'eventlist'),
		    'BW' => esc_html__('Botswana', 'eventlist'),
		    'BY' => esc_html__('Belarus', 'eventlist'),
		    'BZ' => esc_html__('Belize', 'eventlist'),
		    'CA' => esc_html__('Canada', 'eventlist'),
		    'CC' => esc_html__('Cocos (Keeling) Islands', 'eventlist'),
		    'CD' => esc_html__('Congo, Democratic Republic of the', 'eventlist'),
		    'CF' => esc_html__('Central African Republic', 'eventlist'),
		    'CG' => esc_html__('Congo', 'eventlist'),
		    'CH' => esc_html__('Switzerland', 'eventlist'),
		    'CI' => esc_html__('Côte d\'Ivoire', 'eventlist'),
		    'CK' => esc_html__('Cook Islands', 'eventlist'),
		    'CL' => esc_html__('Chile', 'eventlist'),
		    'CM' => esc_html__('Cameroon', 'eventlist'),
		    'CN' => esc_html__('China', 'eventlist'),
		    'CO' => esc_html__('Colombia', 'eventlist'),
		    'CR' => esc_html__('Costa Rica', 'eventlist'),
		    'CU' => esc_html__('Cuba', 'eventlist'),
		    'CV' => esc_html__('Cabo Verde', 'eventlist'),
		    'CW' => esc_html__('Curaçao', 'eventlist'),
		    'CX' => esc_html__('Christmas Island', 'eventlist'),
		    'CY' => esc_html__('Cyprus', 'eventlist'),
		    'CZ' => esc_html__('Czechia', 'eventlist'),
		    'DE' => esc_html__('Germany', 'eventlist'),
		    'DJ' => esc_html__('Djibouti', 'eventlist'),
		    'DK' => esc_html__('Denmark', 'eventlist'),
		    'DM' => esc_html__('Dominica', 'eventlist'),
		    'DO' => esc_html__('Dominican Republic', 'eventlist'),
		    'DZ' => esc_html__('Algeria', 'eventlist'),
		    'EC' => esc_html__('Ecuador', 'eventlist'),
		    'EE' => esc_html__('Estonia', 'eventlist'),
		    'EG' => esc_html__('Egypt', 'eventlist'),
		    'EH' => esc_html__('Western Sahara', 'eventlist'),
		    'ER' => esc_html__('Eritrea', 'eventlist'),
		    'ES' => esc_html__('Spain', 'eventlist'),
		    'ET' => esc_html__('Ethiopia', 'eventlist'),
		    'FI' => esc_html__('Finland', 'eventlist'),
		    'FJ' => esc_html__('Fiji', 'eventlist'),
		    'FK' => esc_html__('Falkland Islands (Malvinas)', 'eventlist'),
		    'FM' => esc_html__('Micronesia (Federated States of)', 'eventlist'),
		    'FO' => esc_html__('Faroe Islands', 'eventlist'),
		    'FR' => esc_html__('France', 'eventlist'),
		    'GA' => esc_html__('Gabon', 'eventlist'),
		    'GB' => esc_html__('United Kingdom of Great Britain and Northern Ireland', 'eventlist'),
		    'GD' => esc_html__('Grenada', 'eventlist'),
		    'GE' => esc_html__('Georgia', 'eventlist'),
		    'GF' => esc_html__('French Guiana', 'eventlist'),
		    'GG' => esc_html__('Guernsey', 'eventlist'),
		    'GH' => esc_html__('Ghana', 'eventlist'),
		    'GI' => esc_html__('Gibraltar', 'eventlist'),
		    'GL' => esc_html__('Greenland', 'eventlist'),
		    'GM' => esc_html__('Gambia', 'eventlist'),
		    'GN' => esc_html__('Guinea', 'eventlist'),
		    'GP' => esc_html__('Guadeloupe', 'eventlist'),
		    'GQ' => esc_html__('Equatorial Guinea', 'eventlist'),
		    'GR' => esc_html__('Greece', 'eventlist'),
		    'GS' => esc_html__('South Georgia and the South Sandwich Islands', 'eventlist'),
		    'GT' => esc_html__('Guatemala', 'eventlist'),
		    'GU' => esc_html__('Guam', 'eventlist'),
		    'GW' => esc_html__('Guinea-Bissau', 'eventlist'),
		    'GY' => esc_html__('Guyana', 'eventlist'),
		    'HK' => esc_html__('Hong Kong', 'eventlist'),
		    'HM' => esc_html__('Heard Island and McDonald Islands', 'eventlist'),
		    'HN' => esc_html__('Honduras', 'eventlist'),
		    'HR' => esc_html__('Croatia', 'eventlist'),
		    'HT' => esc_html__('Haiti', 'eventlist'),
		    'HU' => esc_html__('Hungary', 'eventlist'),
		    'ID' => esc_html__('Indonesia', 'eventlist'),
		    'IE' => esc_html__('Ireland', 'eventlist'),
		    'IL' => esc_html__('Israel', 'eventlist'),
		    'IM' => esc_html__('Isle of Man', 'eventlist'),
		    'IN' => esc_html__('India', 'eventlist'),
		    'IO' => esc_html__('British Indian Ocean Territory', 'eventlist'),
		    'IQ' => esc_html__('Iraq', 'eventlist'),
		    'IR' => esc_html__('Iran (Islamic Republic of)', 'eventlist'),
		    'IS' => esc_html__('Iceland', 'eventlist'),
		    'IT' => esc_html__('Italy', 'eventlist'),
		    'JE' => esc_html__('Jersey', 'eventlist'),
		    'JM' => esc_html__('Jamaica', 'eventlist'),
		    'JO' => esc_html__('Jordan', 'eventlist'),
		    'JP' => esc_html__('Japan', 'eventlist'),
		    'KE' => esc_html__('Kenya', 'eventlist'),
		    'KG' => esc_html__('Kyrgyzstan', 'eventlist'),
		    'KH' => esc_html__('Cambodia', 'eventlist'),
		    'KI' => esc_html__('Kiribati', 'eventlist'),
		    'KM' => esc_html__('Comoros', 'eventlist'),
		    'KN' => esc_html__('Saint Kitts and Nevis', 'eventlist'),
		    'KP' => esc_html__('Korea (Democratic People\'s Republic of)', 'eventlist'),
		    'KR' => esc_html__('Korea, Republic of', 'eventlist'),
		    'KW' => esc_html__('Kuwait', 'eventlist'),
		    'KY' => esc_html__('Cayman Islands', 'eventlist'),
		    'KZ' => esc_html__('Kazakhstan', 'eventlist'),
		    'LA' => esc_html__('Lao People\'s Democratic Republic', 'eventlist'),
		    'LB' => esc_html__('Lebanon', 'eventlist'),
		    'LC' => esc_html__('Saint Lucia', 'eventlist'),
		    'LI' => esc_html__('Liechtenstein', 'eventlist'),
		    'LK' => esc_html__('Sri Lanka', 'eventlist'),
		    'LR' => esc_html__('Liberia', 'eventlist'),
		    'LS' => esc_html__('Lesotho', 'eventlist'),
		    'LT' => esc_html__('Lithuania', 'eventlist'),
		    'LU' => esc_html__('Luxembourg', 'eventlist'),
		    'LV' => esc_html__('Latvia', 'eventlist'),
		    'LY' => esc_html__('Libya', 'eventlist'),
		    'MA' => esc_html__('Morocco', 'eventlist'),
		    'MC' => esc_html__('Monaco', 'eventlist'),
		    'MD' => esc_html__('Moldova, Republic of', 'eventlist'),
		    'ME' => esc_html__('Montenegro', 'eventlist'),
		    'MF' => esc_html__('Saint Martin (French part)', 'eventlist'),
		    'MG' => esc_html__('Madagascar', 'eventlist'),
		    'MH' => esc_html__('Marshall Islands', 'eventlist'),
		    'MK' => esc_html__('North Macedonia', 'eventlist'),
		    'ML' => esc_html__('Mali', 'eventlist'),
		    'MM' => esc_html__('Myanmar', 'eventlist'),
		    'MN' => esc_html__('Mongolia', 'eventlist'),
		    'MO' => esc_html__('Macao', 'eventlist'),
		    'MP' => esc_html__('Northern Mariana Islands', 'eventlist'),
		    'MQ' => esc_html__('Martinique', 'eventlist'),
		    'MR' => esc_html__('Mauritania', 'eventlist'),
		    'MS' => esc_html__('Montserrat', 'eventlist'),
		    'MT' => esc_html__('Malta', 'eventlist'),
		    'MU' => esc_html__('Mauritius', 'eventlist'),
		    'MV' => esc_html__('Maldives', 'eventlist'),
		    'MW' => esc_html__('Malawi', 'eventlist'),
		    'MX' => esc_html__('Mexico', 'eventlist'),
		    'MY' => esc_html__('Malaysia', 'eventlist'),
		    'MZ' => esc_html__('Mozambique', 'eventlist'),
		    'NA' => esc_html__('Namibia', 'eventlist'),
		    'NC' => esc_html__('New Caledonia', 'eventlist'),
		    'NE' => esc_html__('Niger', 'eventlist'),
		    'NF' => esc_html__('Norfolk Island', 'eventlist'),
		    'NG' => esc_html__('Nigeria', 'eventlist'),
		    'NI' => esc_html__('Nicaragua', 'eventlist'),
		    'NL' => esc_html__('Netherlands, Kingdom of the', 'eventlist'),
		    'NO' => esc_html__('Norway', 'eventlist'),
		    'NP' => esc_html__('Nepal', 'eventlist'),
		    'NR' => esc_html__('Nauru', 'eventlist'),
		    'NU' => esc_html__('Niue', 'eventlist'),
		    'NZ' => esc_html__('New Zealand', 'eventlist'),
		    'OM' => esc_html__('Oman', 'eventlist'),
		    'PA' => esc_html__('Panama', 'eventlist'),
		    'PE' => esc_html__('Peru', 'eventlist'),
		    'PF' => esc_html__('French Polynesia', 'eventlist'),
		    'PG' => esc_html__('Papua New Guinea', 'eventlist'),
		    'PH' => esc_html__('Philippines', 'eventlist'),
		    'PK' => esc_html__('Pakistan', 'eventlist'),
		    'PL' => esc_html__('Poland', 'eventlist'),
		    'PM' => esc_html__('Saint Pierre and Miquelon', 'eventlist'),
		    'PN' => esc_html__('Pitcairn', 'eventlist'),
		    'PR' => esc_html__('Puerto Rico', 'eventlist'),
		    'PS' => esc_html__('Palestine, State of', 'eventlist'),
		    'PT' => esc_html__('Portugal', 'eventlist'),
		    'PW' => esc_html__('Palau', 'eventlist'),
		    'PY' => esc_html__('Paraguay', 'eventlist'),
		    'QA' => esc_html__('Qatar', 'eventlist'),
		    'RE' => esc_html__('Réunion', 'eventlist'),
		    'RO' => esc_html__('Romania', 'eventlist'),
		    'RS' => esc_html__('Serbia', 'eventlist'),
		    'RU' => esc_html__('Russian Federation', 'eventlist'),
		    'RW' => esc_html__('Rwanda', 'eventlist'),
		    'SA' => esc_html__('Saudi Arabia', 'eventlist'),
		    'SB' => esc_html__('Solomon Islands', 'eventlist'),
		    'SC' => esc_html__('Seychelles', 'eventlist'),
		    'SD' => esc_html__('Sudan', 'eventlist'),
		    'SE' => esc_html__('Sweden', 'eventlist'),
		    'SG' => esc_html__('Singapore', 'eventlist'),
		    'SH' => esc_html__('Saint Helena, Ascension and Tristan da Cunha', 'eventlist'),
		    'SI' => esc_html__('Slovenia', 'eventlist'),
		    'SJ' => esc_html__('Svalbard and Jan Mayen', 'eventlist'),
		    'SK' => esc_html__('Slovakia', 'eventlist'),
		    'SL' => esc_html__('Sierra Leone', 'eventlist'),
		    'SM' => esc_html__('San Marino', 'eventlist'),
		    'SN' => esc_html__('Senegal', 'eventlist'),
		    'SO' => esc_html__('Somalia', 'eventlist'),
		    'SR' => esc_html__('Suriname', 'eventlist'),
		    'SS' => esc_html__('South Sudan', 'eventlist'),
		    'ST' => esc_html__('Sao Tome and Principe', 'eventlist'),
		    'SV' => esc_html__('El Salvador', 'eventlist'),
		    'SX' => esc_html__('Sint Maarten (Dutch part)', 'eventlist'),
		    'SY' => esc_html__('Syrian Arab Republic', 'eventlist'),
		    'SZ' => esc_html__('Eswatini', 'eventlist'),
		    'TC' => esc_html__('Turks and Caicos Islands', 'eventlist'),
		    'TD' => esc_html__('Chad', 'eventlist'),
		    'TF' => esc_html__('French Southern Territories', 'eventlist'),
		    'TG' => esc_html__('Togo', 'eventlist'),
		    'TH' => esc_html__('Thailand', 'eventlist'),
		    'TJ' => esc_html__('Tajikistan', 'eventlist'),
		    'TK' => esc_html__('Tokelau', 'eventlist'),
		    'TL' => esc_html__('Timor-Leste', 'eventlist'),
		    'TM' => esc_html__('Turkmenistan', 'eventlist'),
		    'TN' => esc_html__('Tunisia', 'eventlist'),
		    'TO' => esc_html__('Tonga', 'eventlist'),
		    'TR' => esc_html__('Türkiye', 'eventlist'),
		    'TT' => esc_html__('Trinidad and Tobago', 'eventlist'),
		    'TV' => esc_html__('Tuvalu', 'eventlist'),
		    'TW' => esc_html__('Taiwan, Province of China', 'eventlist'),
		    'TZ' => esc_html__('Tanzania, United Republic of', 'eventlist'),
		    'UA' => esc_html__('Ukraine', 'eventlist'),
		    'UG' => esc_html__('Uganda', 'eventlist'),
		    'UM' => esc_html__('United States Minor Outlying Islands', 'eventlist'),
		    'US' => esc_html__('United States of America', 'eventlist'),
		    'UY' => esc_html__('Uruguay', 'eventlist'),
		    'UZ' => esc_html__('Uzbekistan', 'eventlist'),
		    'VA' => esc_html__('Holy See', 'eventlist'),
		    'VC' => esc_html__('Saint Vincent and the Grenadines', 'eventlist'),
		    'VE' => esc_html__('Venezuela (Bolivarian Republic of)', 'eventlist'),
		    'VG' => esc_html__('Virgin Islands (British)', 'eventlist'),
		    'VI' => esc_html__('Virgin Islands (U.S.)', 'eventlist'),
		    'VN' => esc_html__('Viet Nam', 'eventlist'),
		    'VU' => esc_html__('Vanuatu', 'eventlist'),
		    'WF' => esc_html__('Wallis and Futuna', 'eventlist'),
		    'WS' => esc_html__('Samoa', 'eventlist'),
		    'YE' => esc_html__('Yemen', 'eventlist'),
		    'YT' => esc_html__('Mayotte', 'eventlist'),
		    'ZA' => esc_html__('South Africa', 'eventlist'),
		    'ZM' => esc_html__('Zambia', 'eventlist'),
		    'ZW' => esc_html__('Zimbabwe', 'eventlist'),
		];

		return $countries;
	}
}

if ( ! function_exists("ova_event_verify_recapcha") ) {
	function ova_event_verify_recapcha( $secret_key , $recapcha ){
		$flag = true;
		if ( $secret_key && $recapcha ) {
				// Verify captcha
			$post_data = http_build_query(
				array(
					'secret' => $secret_key,
					'response' => $recapcha,
					'remoteip' => $_SERVER['REMOTE_ADDR']
				)
			);
			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $post_data
				)
			);
			$context  = stream_context_create($opts);
			$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
			$result = json_decode($response);
			if ( ! $result->success ) {
				$flag = false;
			}
		} else {
			$flag = false;
		}
		return $flag;
	}
}

if ( ! function_exists('ova_event_get_comments_by_event_author_id') ) {
	function ova_event_get_comments_by_event_author_id( $author_id ){
		$args = array(
			'post_author' => $author_id,
			'post_status' => 'any',
			'post_type' => 'event',
			'hierarchical' => true,
		);
		return get_comments( $args );
	}
}

if ( ! function_exists('ova_event_get_rating_average_by_event_author_id') ) {

	function ova_event_get_rating_average_by_event_author_id( $author_id ){

		$comments 		= ova_event_get_comments_by_event_author_id($author_id);
		$rating_numbers = array();
		$average 		= 0;
		if ( $comments ) {
			foreach ( $comments as $key => $comment ) {
				$comment_id = $comment->comment_ID;
				$rating_number = (int)get_comment_meta( $comment_id, 'rating', true );
				array_push($rating_numbers, $rating_number);
			}
		}
		if( count( $rating_numbers ) ) {
		    $average = array_sum($rating_numbers)/count($rating_numbers);
		}
		return $average;
	}
}

if ( ! function_exists('ova_event_author_rating_display_by_id') ) {
	function ova_event_author_rating_display_by_id( $author_id ){
		$rating = ova_event_get_rating_average_by_event_author_id( $author_id );

		$comment_text = '';
		if ( $rating ) {
			$stars = '<p class="stars">';
			for ( $i = 1; $i <= 5; $i++ ) {
				if ( $i <= $rating ) {
					$stars .= '<span class="icon_star"></span>';
				} else {
					$stars .= '<span class="icon_star_alt"></span>';
				}
			}
			$stars .= '</p>';
			$count_stars = '<p class="count_star">'.esc_html( ceil( $rating ) ).'</p>';
			//$comment_text .= '<div class="author_rating">'.$count_stars . $stars.'</div>';
			$comment_text .= '';
			echo wp_kses_post( $comment_text );
		} else {
			echo wp_kses_post( $comment_text );
		}
	}
}

// Get current time by time zone event
if ( ! function_exists( 'el_get_current_time_by_event' ) ) {
	function el_get_current_time_by_event( $event_id = null ) {
		$current_time = current_time( 'timestamp' );

		if ( ! $event_id ) return $current_time;

		$timezone = get_post_meta( $event_id, OVA_METABOX_EVENT . 'time_zone', true );

		if ( $timezone ) {
			$tz_string 	= el_get_timezone_string( $timezone );
			$datetime 	= new DateTime('now', new DateTimeZone( $tz_string ) );
			$time_now 	= $datetime->format('Y-m-d H:i');

			if ( strtotime( $time_now ) ) {
				$current_time = strtotime( $time_now );
			}
		}

		return $current_time;
	}
}

if ( ! function_exists('el_get_ticket_key_by_ticket_id') ) {
	function el_get_ticket_key_by_ticket_id( $event_ticket, $ticket_id ){
		if ( $event_ticket ) {
			foreach ( $event_ticket as $key => $value ) {
				if ( $value['ticket_id'] == $ticket_id ) {
					return $key;
				}
			}
		}
		return false;
	}
}

if ( ! function_exists( 'el_get_total_event' ) ) {
	
	function el_get_total_event(){
		global $wpdb;

		$result = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'event' AND post_status = 'publish'" );
		return $result;
	}
}

if ( ! function_exists( 'el_setting_update_event_status_manually' ) ) {
	
	function el_setting_update_event_status_manually(){
		ob_start();
		$total_event = el_get_total_event();
		?>
		<div class="el_setting_update_event_status_manually">
			<div class="events_info">
				<p class="total">
					<?php echo sprintf( esc_html__( 'Total events: %s', 'eventlist' ), esc_html( $total_event ) ); ?>
				</p>
				<p class="event_processing">
					<?php echo sprintf( esc_html__( '0/%s events have been updated.', 'eventlist' ), esc_html( $total_event ) ); ?>
				</p>
			</div>
			<div class="button_group">
				<button type="button" class="button button-primary" id="el_update_event_status" data-nonce="<?php echo esc_attr( wp_create_nonce( 'el_update_event_status' ) ); ?>"><?php esc_html_e( 'Update Event Status', 'eventlist' ); ?></button>
				<span class="spinner"></span>
			</div>
			
		</div>
		<?php
		return ob_get_clean();
	}
}

if ( ! function_exists( 'el_get_id_ticket_by_qrcode' ) ) {
	function el_get_id_ticket_by_qrcode( $qr_code ){
		$args = array(
			'meta_key'         => OVA_METABOX_EVENT.'qr_code',
			'meta_value'       => $qr_code,
			'post_type'        => 'el_tickets',
			'fields'		   => 'ids',
		);

		$ticket_id = get_posts( $args );

		return $ticket_id;
	}
}

if ( ! function_exists( 'el_get_profile_custom_field_vendor' ) ) {
	function el_get_profile_custom_field_vendor( $user_id ){
		ob_start();
		$user_meta_field = get_option( 'ova_register_form' );
		if ( $user_meta_field ) :
			foreach ( $user_meta_field as $name => $field ):

				$name = 'ova_'.$name;
				$required = $field['required'] == "on" ? "required" : "";

				if ( $field['enabled'] == "on" && $field['used_for'] != 'user' ) {
					$user_meta_value = get_user_meta( $user_id, $name, true );

					if ( $field['type'] == 'text' ) {
						?>
						<div class="vendor_field ova-cf">
							<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<input data-type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
							data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
							/>
						</div>
						<?php  } elseif ( $field['type'] == 'tel' ) { ?>
							<div class="vendor_field ova-cf">
								<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
								<input data-type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
									data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
									data-invalid="<?php echo sprintf( esc_html__( 'Please insert valid %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
								/>
							</div>
						<?php } elseif ( $field['type'] == 'email' ) { ?>
						<div class="vendor_field ova-cf">
							<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<input data-type="<?php echo esc_attr( $field['type'] ); ?>" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" type="text" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
							data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
							data-invalid="<?php echo sprintf( esc_html__( 'Please insert valid %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
							/>
						</div>
						<?php } elseif ( $field['type'] == 'password' ) { ?>
						<div class="vendor_field ova-cf">
							<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<div class="ova_input_wrap">
								<input autocomplete="off" id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" name="<?php echo esc_attr( $name ); ?>" type="password" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
									data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
								/>
								<div class="show_pass">
									<i class="dashicons dashicons-hidden"></i>
								</div>
							</div>
						</div>
						<?php
					} elseif ( $field['type'] == 'textarea' ) {
						?>
						<div class="vendor_field ova-cf textarea">
							<label class="control-label" for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<textarea id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" value="<?php echo esc_attr( $user_meta_value ); ?>" name="<?php echo esc_attr( $name ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
							data-msg="<?php echo sprintf( esc_html__( 'Please insert %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>"
							class="description form-control input-md "><?php echo esc_html( $user_meta_value ); ?></textarea>
						</div>
						<?php
					} elseif ( $field['type'] == 'select' ) {
						$ova_options_key 	= $field['ova_options_key'];
						$ova_options_text 	= $field['ova_options_text'];
						?>
						<div class="vendor_field ova-cf">

							<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
							<select id="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" name="<?php echo esc_attr( $name ); ?>" data-msg="<?php echo sprintf( esc_attr__( 'The %s cannot be empty!', 'eventlist' ), esc_attr( $field['label'] ) ); ?>" >
								<option value=""><?php echo esc_html( $field['placeholder'] ); ?></option>
								<?php if ( $ova_options_key ): ?>
									<?php foreach ( $ova_options_key as $key => $item ): ?>
										<option value="<?php echo esc_attr( $item ); ?>"
											<?php selected( $user_meta_value, $item ); ?>
											><?php echo esc_html( $ova_options_text[$key] ); ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>
						<?php
					} elseif ( $field['type'] == 'radio' ) {
						$ova_radio_key 	= $field['ova_radio_key'];
						$ova_radio_text = $field['ova_radio_text'];
						?>
						<?php if ( $ova_radio_key ): ?>
							<div class="vendor_field ova-cf">
								<label><?php echo esc_html( $field['label'] ); ?></label>
							<?php foreach ( $ova_radio_key as $key => $item ): ?>											<div class="vendor_radio_field">
									<input type="radio" class="<?php echo esc_attr( $required ); ?>" value="<?php echo esc_attr( $item ); ?>"
									id="<?php echo esc_attr( $name .'_'.$item ); ?>"
									name="<?php echo esc_attr( $name ); ?>"
									<?php $user_meta_value != '' ? checked( $user_meta_value, $item ) : checked( 0, $key ); ?>
									 />
									<label for="<?php echo esc_attr( $name .'_'.$item ); ?>"><?php echo esc_html( $ova_radio_text[$key] ); ?></label>
								</div>
							<?php endforeach; ?>
							</div>
						<?php endif;
					} elseif ( $field['type'] == 'checkbox' ) {
						$ova_checkbox_key 	= $field['ova_checkbox_key'];
						$ova_checkbox_text 	= $field['ova_checkbox_text'];
						?>
						<div class="vendor_field ova-cf checkbox">
							<label><?php echo esc_html( $field['label'] ); ?></label>
							<div class="checkbox_field_wrap" data-msg="<?php echo sprintf( esc_attr__( 'Please check %s.', 'eventlist' ), esc_attr( $field['label'] ) ); ?>">
							<?php
							foreach ( $ova_checkbox_key as $key => $item ):
								$checkbox_input = is_array( $user_meta_value ) ? $user_meta_value : array( $user_meta_value ) ;
								$checked 		= in_array($item, $checkbox_input) ? $item : '';
								?>
								<div class="vendor_checkbox_field">
									<input type="checkbox" class="<?php echo esc_attr( $required ); ?>" id="<?php echo esc_attr( $name .'_'.$item ); ?>"
									<?php checked( $checked, $item ); ?>
									name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $item ); ?>" />
									<label for="<?php echo esc_attr( $name .'_'.$item ); ?>"><?php echo esc_html( $ova_checkbox_text[$key] ); ?></label>
								</div>
							<?php endforeach;
							?>
							</div>
						</div>
						<?php
					} elseif ( $field['type'] == 'file' ) {
						$attachment_id 	= $user_meta_value;
						$file_name 		= basename( get_attached_file( $attachment_id ) );
						$file_url 		= wp_get_attachment_url( $attachment_id );
						?>
						<div class="vendor_field ova-cf file_field">
							<label><?php echo esc_html( $field['label'] ); ?></label>
							<div class="vendor_file_field">
								<div class="file__wrap">
									<?php if ( $attachment_id && get_post( $attachment_id ) ) {
										$mime_type = get_post_mime_type( $attachment_id );
										if ( ! str_contains($mime_type, "image") ) {
											?>
											<span class="file-name"><a href="<?php echo esc_url( $file_url ); ?>" target="_blank"><?php echo esc_html( $file_name ); ?></a></span>
											<?php
										} else {
											$image_url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
											?>
											<img class="ova__thumbnail" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $file_name ); ?>">
											<?php
										}
										?>
										<a class="ova_remove_file" href="#"><i class="far fa-trash-alt"></i></a>
									<?php } ?>
								</div>
								<a class="button ova_upload_file el_btn_add" href="#" data-uploader-title="<?php echo esc_attr( $field['label'] ); ?>" data-uploader-button-text="<?php esc_attr_e( 'Upload file', 'eventlist' ); ?>"><?php esc_html_e( 'Upload file', 'eventlist' ); ?></a>
								
								<input type="hidden" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( $required ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $attachment_id ); ?>" data-msg="<?php echo sprintf( esc_attr__( 'The %s cannot be empty!', 'eventlist' ), esc_attr( $field['label'] ) ); ?>" />
							</div>
						</div>
						<?php
					}
				}

			endforeach;
		
		endif;
		return ob_get_clean();
	}
}

if ( ! function_exists( 'ova_register_vendor_mailto_admin' ) ) {
	function ova_register_vendor_mailto_admin( $user_email ){
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=".get_bloginfo( 'charset' )."\r\n";

		$approve_url = add_query_arg( 'page', 'ovalg_vendor_approve', admin_url('admin.php') );
		$approve_url = add_query_arg( 's', $user_email, $approve_url );

		$body = OVALG_Settings::mail_new_vendor_content();
		$body = str_replace('[user_email]', $user_email , $body);
		$body = str_replace('[your_website]', '<a href="'.esc_url( get_bloginfo('url') ).'" target="_blank">'.get_bloginfo('url').'<a>' , $body);
		$body = str_replace('[approve_url]', '<a href="'.esc_url( $approve_url ).'" target="_blank">'.$approve_url.'<a>' , $body);

		$subject = OVALG_Settings::mail_new_vendor_subject();

		if ( apply_filters( 'ovalg_mail_new_vendor_subject_from_email_user', true ) === true ) {
			$subject .= sprintf( esc_html__( " from %s", 'eventlist' ), $user_email );
		}

		$mail_to = OVALG_Settings::mail_new_vendor_recipient();
		$mail_to = explode(",", $mail_to);
		$mail_to = array_map('trim', $mail_to);
		// check send to admin
		if ( OVALG_Settings::mail_new_vendor_send_admin() ) {
			$admin_email = get_option( 'admin_email' );
			if ( ! in_array( $admin_email , $mail_to ) ) {
				$mail_to[] = $admin_email;
			}
		}

		add_filter( 'wp_mail_from', 'wp_mail_from_register_vendor_email' );
		add_filter( 'wp_mail_from_name', 'wp_mail_from_register_vendor' );

		if ( wp_mail( $mail_to, $subject, $body, $headers ) ) {
			$result = true;
		} else {
			$result = false;
		}

		remove_filter( 'wp_mail_from', 'wp_mail_from_register_vendor_email' );
		remove_filter( 'wp_mail_from_name','wp_mail_from_register_vendor' );

		return $result;

	}
}

if ( ! function_exists( 'wp_mail_from_register_vendor' ) ) {
	function wp_mail_from_register_vendor(){
		return OVALG_Settings::mail_new_vendor_from_name();
	}
}

if ( ! function_exists( 'wp_mail_from_register_vendor_email' ) ) {
	function wp_mail_from_register_vendor_email(){
		return OVALG_Settings::mail_new_vendor_from_email();
	}
}

// Booking Stripe
if ( ! function_exists('el_get_booking_id_by_client_secret') ) {
	function el_get_booking_id_by_client_secret( $client_secret ){
		if ( ! $client_secret ) {
			return false;
		}

		$args = array(
			'post_type' => 'el_bookings',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => OVA_METABOX_EVENT.'payment_method',
					'value' => 'stripe',
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'client_secret',
					'value' => $client_secret,
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'status',
					'value' => 'Pending',
					'compare' => '=',
				),
			),
		);

		$booking_ids = get_posts( $args );
		return $booking_ids;
	}
}
// Booking Paypal
if ( ! function_exists('el_get_booking_id_by_paypal_id') ) {
	function el_get_booking_id_by_paypal_id( $paypal_id ){
		if ( ! $paypal_id ) {
			return false;
		}

		$args = array(
			'post_type' => 'el_bookings',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => OVA_METABOX_EVENT.'payment_method',
					'value' => 'paypal',
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'transaction_id',
					'value' => $paypal_id,
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'status',
					'value' => 'Pending',
					'compare' => '=',
				),
			),
		);

		$booking_ids = get_posts( $args );
		return $booking_ids;
	}
}

if ( ! function_exists('el_extra_sv_get_rest_qty') ) {
	function el_extra_sv_get_rest_qty( $id_event, $id_cal ){
		// get booking
		$booking_args = array(
			'post_type' => 'el_bookings',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => OVA_METABOX_EVENT.'id_event',
					'value' => $id_event,
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'id_cal',
					'value' => $id_cal,
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'status',
					'value' => 'Completed',
					'compare' => '=',
				),
			),
		);
		$booking = get_posts( $booking_args );

		$booking_hold_args = array(
			'post_type' => 'holding_ticket',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => OVA_METABOX_EVENT.'id_event',
					'value' => $id_event,
					'compare' => '=',
				),
				array(
					'key' => OVA_METABOX_EVENT.'id_cal',
					'value' => $id_cal,
					'compare' => '=',
				),
			),
		);

		$booking_hold = get_posts( $booking_hold_args );
		// get id => qty extra_service
		$extra_service_booking = array();
		if ( ! empty( $booking ) ) {
			foreach ( $booking as $booking_id ) {
				$extra_service_items = get_post_meta( $booking_id, OVA_METABOX_EVENT.'extra_service', true );

				if ( ! empty( $extra_service_items ) ) {
					foreach ( $extra_service_items as $k => $extra_item ) {
						if ( ! empty( $extra_item ) ) {
							foreach ( $extra_item as $key => $val ) {
								$id = isset( $val['id'] ) ? $val['id'] : '';
								$qty = isset( $val['qty'] ) ? (int)$val['qty'] : 0;

								if ( ! empty( $id ) ) {

									if ( isset( $extra_service_booking[$id] ) ) {
										$extra_service_booking[$id] += $qty;
									} else {
										$extra_service_booking[$id] = $qty;
									}
								} else {

									if ( ! empty( $val ) && is_array( $val ) ) {
										foreach ($val as $m => $n) {
											$id = isset( $n['id'] ) ? $n['id'] : '';
											$qty = isset( $n['qty'] ) ? (int)$n['qty'] : 0;

											if ( ! empty( $id ) ) {

												if ( isset( $extra_service_booking[$id] ) ) {
													$extra_service_booking[$id] += $qty;
												} else {
													$extra_service_booking[$id] = $qty;
												}

											}
										}
									}
								}
							}
						}
						
					}
				}
				
			}
		}
		// get id => qty extra_service
		$extra_service_booking_hold = array();
		if ( ! empty( $booking_hold ) ) {
			foreach ( $booking_hold as $booking_id ) {
				$extra_service = get_post_meta( $booking_id, OVA_METABOX_EVENT.'extra_service', true );
				if ( ! empty( $extra_service ) ) {

					foreach ( $extra_service as $k => $extra_item ) {
			
						if ( isset( $extra_item['id'] ) ) {
							if ( isset( $extra_service_booking_hold[$extra_item['id']] ) ) {
								$extra_service_booking_hold[$extra_item['id']] += (int)$extra_item['qty'];
							} else {
								$extra_service_booking_hold[$extra_item['id']] = (int)$extra_item['qty'];
							}
						} else {
							if ( ! empty( $extra_item ) && is_array( $extra_item ) ) {
								foreach ($extra_item as $key => $value) {
									if ( isset( $extra_service_booking_hold[$value['id']] ) ) {
										$extra_service_booking_hold[$value['id']] += (int)$value['qty'];
									} else {
										$extra_service_booking_hold[$value['id']] = (int)$value['qty'];
									}
								}
							}
						}
					}
				}
			}
		}

		$extra_service_event = get_post_meta( $id_event, OVA_METABOX_EVENT.'extra_service', true );
		$extra_service = array();
		$extra_service_rest = array();
		if ( ! empty( $extra_service_event ) ) {
			foreach ( $extra_service_event as $k => $val ) {
				$extra_service[$val['id']] = (int)$val['qty'];
			}
		}
		
		if ( ! empty( $extra_service ) ) {
			foreach ( $extra_service as $id => $qty ) {
				$rest_qty = $qty;
				if ( array_key_exists( $id, $extra_service_booking ) ) {
					if ( isset( $extra_service_booking[$id] ) ) {
						$rest_qty -= $extra_service_booking[$id];
					}
					
				}
				if ( array_key_exists( $id , $extra_service_booking_hold ) ) {
					if ( isset( $extra_service_booking[$id] ) ) {
						$rest_qty -= $extra_service_booking[$id];
					}
				}
				if ( $rest_qty < 0 ) {
					$rest_qty = 0;
				}
				$extra_service_rest[$id] = $rest_qty;
			}
		}
		return $extra_service_rest;
	}
}

if ( ! function_exists('el_handle_arr_string_to_number') ) {
	function el_handle_arr_string_to_number( $value ){
		if ( is_numeric( $value ) ) {
			return (float)$value;
		} else {
			return $value;
		}
	}
}


if ( ! function_exists('el_extra_sv_get_data_rest') ) {
	function el_extra_sv_get_data_rest( $extra_service, $extra_service_rest ){
		if ( ! empty( $extra_service ) ) {
			foreach ( $extra_service as $k => $val ) {
				$max_qty = ! empty( $val['max_qty'] ) ? $val['max_qty'] : $val['qty'];
				$val['rest_qty'] = $max_qty;
				$rest_qty = isset( $extra_service_rest[$val['id']] ) ? $extra_service_rest[$val['id']] : 0;
				$val['rest_qty'] = $rest_qty;
				if ( $rest_qty > 0 ) {
					$extra_service[$k] = array_map('el_handle_arr_string_to_number', $val);
				} else {
					unset( $extra_service[$k] );
				}
			}
		} else {
			$extra_service = [];
		}
		return $extra_service;
	}
}

if ( ! function_exists('el_extra_sv_get_data_booking') ) {
	function el_extra_sv_get_data_booking( $extra_service_booking ){
		$extra_service_data = array();
		if ( ! empty( $extra_service_booking ) ) {
			foreach ( $extra_service_booking as $k => $extra_item ) {
				if ( ! empty( $extra_item ) && is_array( $extra_item ) ) {
					foreach ( $extra_item as $j => $val ) {
						$id = isset( $val['id'] ) ? $val['id'] : '';
						if ( ! empty( $id ) ) {
							$qty 	= isset( $val['qty'] ) ? (int)$val['qty'] : 0;
							$price 	= isset( $val['price'] ) ? (float)$val['price'] : 0;

							$extra_service_data[$id]['name'] = $val['name'];

							if ( isset( $extra_service_data[$id]['qty'] ) ) {
								$extra_service_data[$id]['qty'] += $qty;
							} else {
								$extra_service_data[$id]['qty'] = $qty;
							}
							$extra_service_data[$id]['price'] = $price;
						} else {
							if ( ! empty( $val ) && is_array( $val ) ) {
								foreach ( $val as $m => $n ) {
									$jd 	= isset( $n['id'] ) ? $n['id'] : '';
									$qty 	= isset( $n['qty'] ) ? (int)$n['qty'] : 0;
									$price 	= isset( $n['price'] ) ? (float)$n['price'] : 0;

									$extra_service_data[$jd]['name'] = $n['name'];

									if ( isset( $extra_service_data[$jd]['qty'] ) ) {
										$extra_service_data[$jd]['qty'] += $qty;
									} else {
										$extra_service_data[$jd]['qty'] = $qty;
									}
									$extra_service_data[$jd]['price'] = $price;
								}
							}
							
						}

					}
				}
			}
		}
		return $extra_service_data;
	}
}

if ( ! function_exists('el_extra_sv_get_info_booking') ) {
	function el_extra_sv_get_info_booking( $extra_service ){
		$output = '';
		$extra_service_display = array();
		$data_extra_service = el_extra_sv_get_data_booking( $extra_service );
		// extra service
		if ( ! empty( $data_extra_service ) ) {
			foreach ( $data_extra_service as $k => $val ) {
				if ( $val['qty'] > 0 ) {
					$extra_service_display[] = sprintf( '%1$s - <strong>%2$s</strong>: %3$s',$val['name'], __( 'Qty', 'eventlist' ), $val['qty'] );
				}
				
			}
		}
		if ( empty( $extra_service_display ) ) {

			return apply_filters( 'el_extra_sv_get_info_booking', $output );
		}

		$output = implode(', ', $extra_service_display );

		return apply_filters( 'el_extra_sv_get_info_booking', $output );
	}
}

if ( ! function_exists('el_extra_sv_ticket') ) {
	function el_extra_sv_ticket( $extra_services ){
		$extra_service_display = array();
		if ( empty( $extra_services ) ) {
			return '';
		}

		foreach ( $extra_services as $id => $item ) {
			
			if ( isset( $item['qty'] ) && $item['qty'] > 0 ) {
				$extra_service_display[] = sprintf( '%1$s - %2$s: %3$s',$item['name'], __( 'Qty', 'eventlist' ), $item['qty'] );
			}

		}
		
		$extra_service_display = implode(', ', $extra_service_display);
		return apply_filters( 'el_extra_sv_ticket', $extra_service_display );
	}
}

if ( ! function_exists('el_extra_sv_ticket_invoice') ) {
	function el_extra_sv_ticket_invoice( $extra_services ){
		$data_extra_service = array();

		if ( ! empty( $extra_services ) ) {
			foreach ( $extra_services as $k => $extra_item ) {

				if ( isset( $extra_item['qty'] ) && $extra_item['qty'] > 0 ) {
					$id = isset( $extra_item['id'] ) ? $extra_item['id'] : '';
					if ( ! empty( $id ) ) {
						$qty 	= isset( $extra_item['qty'] ) ? (int)$extra_item['qty'] : 0;
						$price 	= isset( $extra_item['price'] ) ? (float)$extra_item['price'] : 0;

						$data_extra_service[$id]['name'] = $extra_item['name'];

						if ( isset( $data_extra_service[$id]['qty'] ) ) {
							$data_extra_service[$id]['qty'] += $qty;
						} else {
							$data_extra_service[$id]['qty'] = $qty;
						}
						$data_extra_service[$id]['price'] = $price;
					}
				} else {
					
					if ( ! empty( $extra_item ) && is_array( $extra_item ) ) {
						foreach ( $extra_item as $j => $val ) {
							$id = isset( $val['id'] ) ? $val['id'] : '';
							if ( ! empty( $id ) ) {
								$qty 	= isset( $val['qty'] ) ? (int)$val['qty'] : 0;
								$price 	= isset( $val['price'] ) ? (float)$val['price'] : 0;

								$data_extra_service[$id]['name'] = $val['name'];

								if ( isset( $data_extra_service[$id]['qty'] ) ) {
									$data_extra_service[$id]['qty'] += $qty;
								} else {
									$data_extra_service[$id]['qty'] = $qty;
								}
								$data_extra_service[$id]['price'] = $price;
							} else {
								if ( ! empty( $val ) && is_array( $val ) ) {
									foreach ( $val as $m => $n ) {
										$jd 	= isset( $n['id'] ) ? $n['id'] : '';
										$qty 	= isset( $n['qty'] ) ? (int)$n['qty'] : 0;
										$price 	= isset( $n['price'] ) ? (float)$n['price'] : 0;

										$data_extra_service[$jd]['name'] = $n['name'];

										if ( isset( $data_extra_service[$jd]['qty'] ) ) {
											$data_extra_service[$jd]['qty'] += $qty;
										} else {
											$data_extra_service[$jd]['qty'] = $qty;
										}
										$data_extra_service[$jd]['price'] = $price;
									}
								}
								
							}

						}
					}
				}

				
			}
		}

		return $data_extra_service;
	}
}

if ( ! function_exists('el_extra_sv_price_booking') ) {
	function el_extra_sv_price_booking( $extra_service ){
		$extra_service_booking = el_extra_sv_get_data_booking( $extra_service );
		$total = 0;
		if ( ! empty( $extra_service_booking ) ) {
			foreach ( $extra_service_booking as $k => $val ) {
				$qty = isset( $val['qty'] ) ? (int)$val['qty'] : 0;
				$price = isset( $val['price'] ) ? (float)$val['price'] : 0;
				$total += $price * $qty;
			}
		}
		return apply_filters( 'el_extra_sv_price_booking', $total );
	}
}

if ( ! function_exists('el_ticket_type_seat_map_cart') ) {
	function el_ticket_type_seat_map_cart( $cart ){
		$output = '';
		$output_arr = [];
		$output_str_arr = [];
		$quantity_text = __( 'Qty:', 'eventlist' );

		if ( ! empty( $cart ) ) {
			foreach ( $cart as $k => $item ) {
				$id = isset( $item['id'] ) ? $item['id'] : '';
				$qty = isset( $item['qty'] ) ? (int)$item['qty'] : 1;
				$data_person = isset( $item['data_person'] ) ? $item['data_person'] : [];
				if ( ! empty( $data_person ) ) {
					foreach ( $data_person as $k_p => $val ) {
						$p_name = isset( $val['name'] ) ? $val['name'] : '';
						$p_qty = isset( $val['qty'] ) ? (int)$val['qty'] : 0;
						if ( $p_qty > 0 ) {
							$output_arr[$id][] = $p_name.' - '.$quantity_text.$p_qty;
						}
					}
				} else {
					if ( $qty > 0 ) {
						$output_arr[$id] = $quantity_text.$qty;
					}
				}
			}
		}

		if ( ! empty( $output_arr ) ) {
			foreach ( $output_arr as $k => $item ) {
				if ( ! empty( $item ) ) {
					if ( is_array( $item ) && count( $item ) > 0 ) {
						foreach ( $item as $j => $val) {
							$output_str_arr[] = $k.' - '.$val;
						}
						
					} else {
						$output_str_arr[] = $k.' - '.$item;
					}
				}
			}
		}

		if ( ! empty( $output_str_arr ) ) {
			$output = implode( '; ', $output_str_arr );
		}

		return $output;
	}
}

if ( ! function_exists('el_check_ticket_price_show_payment') ) {
	function el_check_ticket_price_show_payment( $id_event ){
		$ticket_total_price = 0;
		$ticket_event 		= get_post_meta( $id_event, OVA_METABOX_EVENT.'ticket', true );
		$seat_option 		= get_post_meta( $id_event, OVA_METABOX_EVENT.'seat_option', true );
		$map_ticket_event 	= get_post_meta( $id_event, OVA_METABOX_EVENT.'ticket_map', true );

		if ( $seat_option != 'map' ) {
			if ( ! empty( $ticket_event ) ) {
				foreach ( $ticket_event as $key => $val ) {
					$price = ! empty( $val['price_ticket'] ) ? (float)$val['price_ticket'] : 0;
					$ticket_total_price += $price;
				}
			}
		} else {

			if ( ! empty( $map_ticket_event ) ) {
				$seats = isset( $map_ticket_event['seat'] ) ? $map_ticket_event['seat'] : array();
				$areas = isset( $map_ticket_event['area'] ) ? $map_ticket_event['area'] : array();

				if ( ! empty( $seats ) ) {
					foreach ( $seats as $key => $val ) {
						$price = isset( $val['price'] ) ? (float)$val['price'] : 0;
						$person_price = isset( $val['person_price'] ) ? json_decode( $val['person_price'] ) : [];
						$ticket_total_price += $price;
						if ( ! empty( $person_price ) ) {
							foreach ( $person_price as $p_price ) {
								if ( is_numeric( $p_price ) ) {
									$ticket_total_price += (float)$p_price;
								}
							}
						}
					}
				}

				if ( ! empty( $areas ) ) {
					foreach ( $areas as $key => $val ) {
						$person_price = isset( $val['person_price'] ) ? json_decode( $val['person_price'] ) : array();
						$price = isset( $val['price'] ) ? (float)$val['price'] : 0;

						if ( ! empty( $person_price ) ) {
							$person_price = array_map('floatval', $person_price );
							$person_price = array_filter( $person_price, function($item){ return $item > 0; } );

							if ( count( $person_price ) > 0 ) {
								$ticket_total_price += array_sum( $person_price );
							}
						}

						$ticket_total_price += $price;

					}
				}
			}
		}
		return $ticket_total_price;
	}
}


if ( ! function_exists("recursive_sanitize_text_field") ) {
	function recursive_sanitize_text_field($array) {
	    foreach ( $array as $key => &$value ) {
	        if ( is_array( $value ) ) {
	            $value = recursive_sanitize_text_field($value);
	        }
	        else {
	            $value = sanitize_text_field( $value );
	        }
	    }
	    return $array;
	}
}


if ( ! function_exists('el_hide_package_menu_item') ) {
	function el_hide_package_menu_item(){
		return apply_filters( 'el_hide_package_menu_item', EL()->options->package->get('hide_package','') );
	}
}

function el_create_data_booking_demo( $post_id = '', $number = 1 ){
	$post = get_post($post_id);
    /*
    * if you don't want current user to be the new post author,
    * then change next couple of lines to this: $new_post_author = $post->post_author;
    */
    $current_user = wp_get_current_user();
    $new_post_author = $current_user->ID;


    if (isset($post) && $post != null) {

    	for ($i=0; $i < $number; $i++) { 
	    	

	    	/*
	        * new post data array
	        */
	        $args = array(
	             'comment_status' 	=> $post->comment_status,
	             'ping_status' 		=> $post->ping_status,
	             'post_author' 		=> $new_post_author,
	             'post_content' 	=> $post->post_content,
	             'post_excerpt' 	=> $post->post_excerpt,
	             'post_parent' 		=> $post->post_parent,
	             'post_password' 	=> $post->post_password,
	             'post_status' 		=> 'publish',
	             'post_title' 		=> $post->post_title,
	             'post_type' 		=> $post->post_type,
	             'to_ping' 			=> $post->to_ping,
	             'menu_order' 		=> $post->menu_order,
	         );
	        /*
	        * insert the post by wp_insert_post() function
	        */
	        $new_post_id = wp_insert_post($args);
	        if(is_wp_error($new_post_id)){
	            return false;
	        }
	       
	        /*
	        * get all current post terms ad set them to the new post draft
	        */
	        $taxonomies = array_map('sanitize_text_field',get_object_taxonomies($post->post_type));
	        if (!empty($taxonomies) && is_array($taxonomies)):
	         foreach ($taxonomies as $taxonomy) {
	             $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
	             wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
	         }
	        endif;
	        /*
	        * duplicate all post meta
	        */
	        $post_meta_keys = get_post_custom_keys( $post_id );
	        if( !empty($post_meta_keys) ){
	            foreach ( $post_meta_keys as $meta_key ) {
	                $meta_values = get_post_custom_values( $meta_key, $post_id );
	                foreach ( $meta_values as $meta_value ) {
	                    $meta_value = maybe_unserialize( $meta_value );
	                    update_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
	                }
	            }
	        }

	        /**
	         * Elementor compatibility fixes
	         */
			if( is_plugin_active( 'elementor/elementor.php' ) ){
			    $css = Elementor\Core\Files\CSS\Post::create( $new_post_id );
			    $css->update();
			}

		}
    }
}


add_filter( 'el_event_display_date_opt', 'el_event_display_date', 10, 2 );

if ( ! function_exists('el_event_display_date') ) {
	function el_event_display_date( $options, $args ){
		$display_date = isset( $args['display_date'] ) ? $args['display_date'] : $options;
		return apply_filters( 'el_event_display_date', $display_date );
	}
}

add_filter( 'el_event_show_hours_archive_opt', 'el_event_show_hours_archive', 10, 2 );

if ( ! function_exists('el_event_show_hours_archive') ) {
	function el_event_show_hours_archive( $options, $args ){
		$show_hours = isset( $args['show_time'] ) ? $args['show_time'] : $options;
		return apply_filters( 'el_event_show_hours_archive', $show_hours, $args );
	}
}

if ( ! function_exists( 'el_event_show_hours_single' ) ) {
	function el_event_show_hours_single(){
		$show_hours_single = EL()->options->event->get( 'show_hours_single', 'yes' );
		return apply_filters( 'el_event_show_hours_single', $show_hours_single );
	}
}

if ( ! function_exists('el_is_json') ) {
	function el_is_json( $string ){
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}
}