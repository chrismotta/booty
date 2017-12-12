<?php

	namespace backend\components;

	use Yii;
	use yii\base\Component;
	use yii\base\InvalidConfigException;
	 
	class KimiaAPI extends Component
	{
		const URL = 'products.kimia.mobi/cpi';

		protected $_msg;
		protected $_status;

		public function requestCampaigns ( $api_key, $user_id = null  )
		{

			$url    = 'https://'.$user_id.':'.$api_key.'@'.self::URL;
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, $url );

			$json_response = curl_exec($curl);
			$response = json_decode($json_response);

			$this->_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );


			if  ( isset($_GET['source']) && $_GET['source']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $response, JSON_PRETTY_PRINT );
				die();
			}	

			if ( !$response )
			{
				$this->_msg = 'Response without body';
				return false;
			}
			else if ( !isset($response->data->items[0]->Id) )
			{
				$this->_msg = 'No campaign data in response';				
				return false;
			}
			else
			{
				$next = true;
			}

			$result = [];

			while ( $next )
			{
				foreach ( $response->data->items AS $campaign )
				{
					if ( !isset( $campaign->ProductURL ) )
						continue;

					$countries = [];

					if ( $campaign->GeoProvisioning )
					{
						$countryCode = $this->getCountryCode($campaign->GeoProvisioning);

						if ( $countryCode )
							$countries[] = $countryCode;
					}		

					$oss		 = ApiHelper::getOs($campaign->PlatformProvisioning, false);
					$deviceTypes = ApiHelper::getDeviceTypes($campaign->PlatformProvisioning, false);				

					switch ( strtolower($campaign->Status) )
					{
						case 'active':
							$status = 'active';
						break;
						default:
							$status = 'aff_paused';
						break;
					}

					if ( $campaign->PreviewURL )
					{
						$packageIds = ApiHelper::getAppIdFromUrl( $campaign->PreviewURL );	
					}
					else
					{
						$packageIds = [];
					}						

					$result[] = [
						'ext_id' 			=> $campaign->Id,
						'name'				=> $campaign->Name,
						'desc'				=> null,
						'payout' 			=> $campaign->DefaultPrice,
						'landing_url'		=> $campaign->ProductURL,
						'country'			=> $countries,
						'device_type'		=> $deviceTypes,
						'connection_type'	=> null,
						'carrier'			=> null,
						'os'				=> $oss,
						'os_version'		=> null,
						'package_id'		=> empty($packageIds) ? null : $packageIds,
						'status'			=> $status,
						'currency'			=> 'USD'
					];

					unset( $countries );
					unset( $deviceTypes);
					unset( $oss );
					unset( $packageIds );					
				}

				if ( isset($resoponse->data->links->next) && $resoponse->data->links->next )
				{
					curl_setopt($curl, CURLOPT_URL, $resoponse->data->links->next );
					$json_response = curl_exec($curl);
					$response = json_decode($json_response);

					if ( $response && isset($response->data->items[0]->Id) )
						$next = true;
					else
						$next = false;									
				}
				else
				{
					$next = false;
				}
			}

			if  ( isset($_GET['test']) && $_GET['test']==1 )
			{
				header('Content-Type: text/json');
				echo json_encode( $result, JSON_PRETTY_PRINT );
				die();
			}

			return $result;
		}

		public function getMessages ( )
		{
			return $this->_msg;
		}

		public function getStatus ( )
		{
			return $this->_status;
		}

		public function getCountryCode ( $name )
		{
			$isos = [
				'Andorra'                                      => 'AD',
				'United Arab Emirates'                         => 'AE',
				'Afghanistan'                                  => 'AF',
				'Antigua And Barbuda'                          => 'AG',
				'Anguilla'                                     => 'AI',
				'Albania'                                      => 'AL',
				'Armenia'                                      => 'AM',
				'Netherlands Antilles'                         => 'AN',
				'Angola'                                       => 'AO',
				'Antarctica'                                   => 'AQ',
				'Argentina'                                    => 'AR',
				'American Samoa'                               => 'AS',
				'Austria'                                      => 'AT',
				'Australia'                                    => 'AU',
				'Aruba'                                        => 'AW',
				'Aaland Islands'                               => 'AX',
				'Azerbaijan'                                   => 'AZ',
				'Bosnia And Herzegowina'                       => 'BA',
				'Barbados'                                     => 'BB',
				'Bangladesh'                                   => 'BD',
				'Belgium'                                      => 'BE',
				'Burkina Faso'                                 => 'BF',
				'Bulgaria'                                     => 'BG',
				'Bahrain'                                      => 'BH',
				'Burundi'                                      => 'BI',
				'Benin'                                        => 'BJ',
				'Saint Barthelemy'                             => 'BL',
				'Bermuda'                                      => 'BM',
				'Brunei Darussalam'                            => 'BN',
				'Bolivia'                                      => 'BO',
				'Bonaire'                                      => 'BQ',
				'Brazil'                                       => 'BR',
				'Bahamas'                                      => 'BS',
				'Bhutan'                                       => 'BT',
				'Bouvet Island'                                => 'BV',
				'Botswana'                                     => 'BW',
				'Belarus'                                      => 'BY',
				'Belize'                                       => 'BZ',
				'Canada'                                       => 'CA',
				'Cocos (Keeling) Islands'                      => 'CC',
				'Congo, Democratic Republic Of (Was Zaire)'    => 'CD',
				'Central African Republic'                     => 'CF',
				'Congo, Republic Of'                           => 'CG',
				'Switzerland'                                  => 'CH',
				'Cote Divoire'                                 => 'CI',
				'Cook Islands'                                 => 'CK',
				'Chile'                                        => 'CL',
				'Cameroon'                                     => 'CM',
				'China'                                        => 'CN',
				'Colombia'                                     => 'CO',
				'Costa Rica'                                   => 'CR',
				'Cuba'                                         => 'CU',
				'Cape Verde'                                   => 'CV',
				'Curazao'                                      => 'CW',
				'Christmas Island'                             => 'CX',
				'Cyprus'                                       => 'CY',
				'Czech Republic'                               => 'CZ',
				'Germany'                                      => 'DE',
				'Djibouti'                                     => 'DJ',
				'Denmark'                                      => 'DK',
				'Dominica'                                     => 'DM',
				'Dominican Republic'                           => 'DO',
				'Algeria'                                      => 'DZ',
				'Ecuador'                                      => 'EC',
				'Estonia'                                      => 'EE',
				'Egypt'                                        => 'EG',
				'Western Sahara'                               => 'EH',
				'Eritrea'                                      => 'ER',
				'Spain'                                        => 'ES',
				'Ethiopia'                                     => 'ET',
				'Finland'                                      => 'FI',
				'Fiji'                                         => 'FJ',
				'Falkland Islands (Malvinas)'                  => 'FK',
				'Micronesia, Federated States Of'              => 'FM',
				'Faroe Islands'                                => 'FO',
				'France'                                       => 'FR',
				'Gabon'                                        => 'GA',
				'United Kingdom'                               => 'GB',
				'Grenada'                                      => 'GD',
				'Georgia'                                      => 'GE',
				'French Guiana'                                => 'GF',
				'Guernesey'                                    => 'GG',
				'Ghana'                                        => 'GH',
				'Gibraltar'                                    => 'GI',
				'Greenland'                                    => 'GL',
				'Gambia'                                       => 'GM',
				'Guinea'                                       => 'GN',
				'Guadeloupe'                                   => 'GP',
				'Equatorial Guinea'                            => 'GQ',
				'Greece'                                       => 'GR',
				'South Georgia And The South Sandwich Islands' => 'GS',
				'Guatemala'                                    => 'GT',
				'Guam'                                         => 'GU',
				'Guinea-Bissau'                                => 'GW',
				'Guyana'                                       => 'GY',
				'Hong Kong'                                    => 'HK',
				'Heard And Mc Donald Islands'                  => 'HM',
				'Honduras'                                     => 'HN',
				'Croatia (Local Name: Hrvatska)'               => 'HR',
				'Haiti'                                        => 'HT',
				'Hungary'                                      => 'HU',
				'Indonesia'                                    => 'ID',
				'Ireland'                                      => 'IE',
				'Israel'                                       => 'IL',
				'Isle of Man'                                  => 'IM',
				'India'                                        => 'IN',
				'British Indian Ocean Territory'               => 'IO',
				'Iraq'                                         => 'IQ',
				'Iran'                                         => 'IR',
				'Iceland'                                      => 'IS',
				'Italy'                                        => 'IT',
				'Jersey'                                       => 'JE',
				'Jamaica'                                      => 'JM',
				'Jordan'                                       => 'JO',
				'Japan'                                        => 'JP',
				'Kenya'                                        => 'KE',
				'Kyrgyzstan'                                   => 'KG',
				'Cambodia'                                     => 'KH',
				'Kiribati'                                     => 'KI',
				'Comoros'                                      => 'KM',
				'Saint Kitts And Nevis'                        => 'KN',
				'Korea, Democratic Peoples Republic Of'        => 'KP',
				'Korea'                                        => 'KR',
				'Kuwait'                                       => 'KW',
				'Cayman Islands'                               => 'KY',
				'Kazakhstan'                                   => 'KZ',
				'Lao Peoples Democratic Republic'              => 'LA',
				'Lebanon'                                      => 'LB',
				'Saint Lucia'                                  => 'LC',
				'Liechtenstein'                                => 'LI',
				'Sri Lanka'                                    => 'LK',
				'Liberia'                                      => 'LR',
				'Lesotho'                                      => 'LS',
				'Lithuania'                                    => 'LT',
				'Luxembourg'                                   => 'LU',
				'Latvia'                                       => 'LV',
				'Libyan Arab Jamahiriya'                       => 'LY',
				'Morocco'                                      => 'MA',
				'Monaco'                                       => 'MC',
				'Moldova, Republic Of'                         => 'MD',
				'Montenegro'                                   => 'ME',
				'Saint Martin'                                 => 'MF',
				'Madagascar'                                   => 'MG',
				'Marshall Islands'                             => 'MH',
				'Macedonia'                                    => 'MK',
				'Mali'                                         => 'ML',
				'Myanmar'                                      => 'MM',
				'Mongolia'                                     => 'MN',
				'Macau'                                        => 'MO',
				'Northern Mariana Islands'                     => 'MP',
				'Martinique'                                   => 'MQ',
				'Mauritania'                                   => 'MR',
				'Montserrat'                                   => 'MS',
				'Malta'                                        => 'MT',
				'Mauritius'                                    => 'MU',
				'Maldives'                                     => 'MV',
				'Malawi'                                       => 'MW',
				'Mexico'                                       => 'MX',
				'Malaysia'                                     => 'MY',
				'Mozambique'                                   => 'MZ',
				'Namibia'                                      => 'NA',
				'New Caledonia'                                => 'NC',
				'Niger'                                        => 'NE',
				'Norfolk Island'                               => 'NF',
				'Nigeria'                                      => 'NG',
				'Nicaragua'                                    => 'NI',
				'Netherlands'                                  => 'NL',
				'Norway'                                       => 'NO',
				'Nepal'                                        => 'NP',
				'Nauru'                                        => 'NR',
				'Niue'                                         => 'NU',
				'New Zealand'                                  => 'NZ',
				'Oman'                                         => 'OM',
				'Panama'                                       => 'PA',
				'Peru'                                         => 'PE',
				'French Polynesia'                             => 'PF',
				'Papua New Guinea'                             => 'PG',
				'Philippines'                                  => 'PH',
				'Pakistan'                                     => 'PK',
				'Poland'                                       => 'PL',
				'Saint Pierre And Miquelon'                    => 'PM',
				'Pitcairn'                                     => 'PN',
				'Puerto Rico'                                  => 'PR',
				'Palestinian Territory'                        => 'PS',
				'Portugal'                                     => 'PT',
				'Palau'                                        => 'PW',
				'Paraguay'                                     => 'PY',
				'Qatar'                                        => 'QA',
				'Reunion'                                      => 'RE',
				'Romania'                                      => 'RO',
				'Serbia'                                       => 'RS',
				'Russia'                                       => 'RU',
				'Rwanda'                                       => 'RW',
				'Saudi Arabia'                                 => 'SA',
				'Solomon Islands'                              => 'SB',
				'Seychelles'                                   => 'SC',
				'Sudan'                                        => 'SD',
				'Sweden'                                       => 'SE',
				'Singapore'                                    => 'SG',
				'Saint Helena'                                 => 'SH',
				'Slovenia'                                     => 'SI',
				'Svalbard And Jan Mayen Islands'               => 'SJ',
				'Slovakia'                                     => 'SK',
				'Sierra Leone'                                 => 'SL',
				'San Marino'                                   => 'SM',
				'Senegal'                                      => 'SN',
				'Somalia'                                      => 'SO',
				'Suriname'                                     => 'SR',
				'South Sudan'                                  => 'SS',
				'Sao Tome And Principe'                        => 'ST',
				'El Salvador'                                  => 'SV',
				'Sint Maarten'                                 => 'SX',
				'Syrian Arab Republic'                         => 'SY',
				'Swaziland'                                    => 'SZ',
				'Turks And Caicos Islands'                     => 'TC',
				'Chad'                                         => 'TD',
				'French Southern Territories'                  => 'TF',
				'Togo'                                         => 'TG',
				'Thailand'                                     => 'TH',
				'Tajikistan'                                   => 'TJ',
				'Tokelau'                                      => 'TK',
				'Timor-Leste'                                  => 'TL',
				'Turkmenistan'                                 => 'TM',
				'Tunisia'                                      => 'TN',
				'Tonga'                                        => 'TO',
				'Turkey'                                       => 'TR',
				'Trinidad And Tobago'                          => 'TT',
				'Tuvalu'                                       => 'TV',
				'Taiwan'                                       => 'TW',
				'Tanzania, United Republic Of'                 => 'TZ',
				'Ukraine'                                      => 'UA',
				'Uganda'                                       => 'UG',
				'United States Minor Outlying Islands'         => 'UM',
				'United States of America'                     => 'US',
				'Uruguay'                                      => 'UY',
				'Uzbekistan'                                   => 'UZ',
				'Vatican City State (Holy See)'                => 'VA',
				'Saint Vincent And The Grenadines'             => 'VC',
				'Venezuela'                                    => 'VE',
				'Virgin Islands (British)'                     => 'VG',
				'Virgin Islands (U.S.)'                        => 'VI',
				'Vietnam'                                      => 'VN',
				'Vanuatu'                                      => 'VU',
				'Wallis And Futuna Islands'                    => 'WF',
				'Samoa'                                        => 'WS',
				'Kosovo'                                       => 'XK',
				'Yemen'                                        => 'YE',
				'Mayotte'                                      => 'YT',
				'South Africa'                                 => 'ZA',
				'Zambia'                                       => 'ZM',
				'Zimbabwe'                                     => 'ZW'
			];

			if ( array_key_exists($name, $isos) )
				return $isos[$name];
			else
				return false;
		}

	}

?>