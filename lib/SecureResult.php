<?php

namespace Moneris;

/**
 * 3-D Secure Protocol response.
 *
 * @package Moneris
 */
class SecureResult extends Result
{
	protected $_is_enrolled = false;

	/**
	 * If the card isn't enrolled, we may still be eligable for protection (response code 'N')
	 * @return string A string number though, so that's cool.
	 */
	public function fallback_encryption_type()
	{
		return 'N' == $this->response()->message ? '6' : '7';
	}

	/**
	 * Is the provided card enrolled in the 3D Secure program.
	 * @return bool
	 */
	public function is_enrolled()
	{
		return $this->_is_enrolled;
	}

	/**
	 * Moneris reference number.
	 * @return string
	 */
	public function reference_number()
	{
		return $this->response()->PaReq;
	}

	/**
	 * The response from Moneris.
	 *
	 * @return \SimpleXmlElement
	 */
	public function response()
	{
		return $this->transaction()->response();
	}

	/**
	 * Moneris' response code.
	 *
	 * @return string
	 */
	public function response_code()
	{
		return $this->response()->message;
	}

	/**
	 * Moneris' response message.
	 *
	 * @return string
	 */
	public function response_message()
	{
		return $this->response()->message;
	}

	public function submit_url()
	{
		return $this->response()->ACSUrl;
	}

	public function term_url()
	{
		return $this->response()->TermUrl;
	}

	/**
	 * Validate the response from Moneris to see if it was successful.
	 *
	 * @return SecureResult
	 */
	public function validate_response()
	{
		$response = $this->response();
		$gateway = $this->transaction()->gateway();

		// did the transaction go through?
		if ('Error' == $response->type) {
			$this->error_code(Result::ERROR)
				->was_successful(false);
		} else {
			$this->was_successful("true" == $response->success);

			if ($this->was_successful() && isset($response->message)) {
				$this->_is_enrolled = 'Y' == $response->message;
			}
		}

		return $this;
	}

	/**
	 * Get the value from the response object.
	 * @return string
	 */
	public function value()
	{
		$response = $this->response();
		$value = isset($response->PaReq) && 'null' != $response->PaReq ? $response->PaReq : $response->cavv;
		return (string) $value;
	}
}
