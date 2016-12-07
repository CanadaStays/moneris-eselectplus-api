<?php

namespace Moneris;

if (! function_exists('curl_init')) {
  throw new \Exception('The Moneris API requires the CURL extension.');
}

/**
 * A really simple way to get a Gateway object.
 */
class Moneris
{
	const ENV_LIVE = 'live'; // use the live API server
	const ENV_STAGING = 'staging'; // use the API sandbox
	const ENV_TESTING = 'testing'; // use the mock API

	/**
	 * Start using the API, ya dingus!
	 *
	 * @param array $params Associative array
	 * 		Required keys:
	 * 			- api_key string
	 * 			- store_id string
	 * 		Optional keys:
	 * 			- environment string
	 * 			- require_cvd bool
	 * 			- require_avs bool
	 * 			- require_avs_street_number bool
	 * 			- require_avs_street_name bool
	 * 			- require_avs_zipcode bool
	 * 			- avs_codes array
	 * @throws MonerisException
	 * @return Gateway
	 */
	static public function create(array $params)
	{
		if (! isset($params['api_key'])) throw new MonerisException("'api_key' is required.");
		if (! isset($params['store_id'])) throw new MonerisException("'store_id' is required.");

		$params['environment'] = isset($params['environment']) ? $params['environment'] : self::ENV_LIVE;

		$gateway = new Gateway($params['api_key'], $params['store_id'], $params['environment']);

		if (isset($params['require_cvd']))
			$gateway->require_cvd((bool) $params['require_cvd']);

		if (isset($params['cvd_codes']))
			$gateway->successful_cvd_codes($params['cvd_codes']);

		if (isset($params['require_avs'])) {
			$gateway->require_avs((bool) $params['require_avs']);

			$gateway->require_avs_params(
				array_merge(
					array_map(function ($param) use ($params) {
						return array($param => isset($params[$param]) ? (bool) $params[$param] : true);
					}, array(
						'require_avs_street_number',
						'require_avs_street_name',
						'require_avs_zipcode',
					))
				)
			);
		}

		if (isset($params['avs_codes']))
			$gateway->successful_avs_codes($params['avs_codes']);

		return $gateway;
	}

	// don't allow instantiation
	protected function __construct(){ }
}
