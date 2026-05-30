<?php

function fetchFromApi($path, $method="GET", $data=null, $headers=[], $timeout=null) {
	$url = getenv("MEDIA_API") . $path;
	$apikey = getenv("KEY_LUCOS_MEDIA_METADATA_API");
	$headers[] = "Authorization: Bearer $apikey";
	$http_params = [
		"method" => $method,
		"header" => $headers,
		"ignore_errors" => true,
	];
	if ($timeout !== null) {
		$http_params["timeout"] = $timeout;
	}
	if ($data !== null) {
		$http_params["content"] = json_encode($data);
		$http_params["header"][] = "Content-Type: application/json";
	}
	$context = stream_context_create([
		"http" => $http_params
	]);
	$startTime = microtime(true);
	$responseBody = @file_get_contents($url, false, $context);
	$latencyMs = (int) round((microtime(true) - $startTime) * 1000);
	if (empty($http_response_header)) {
		$host = parse_url($url, PHP_URL_HOST) ?: "";
		$resolvedIp = $host ? gethostbyname($host) : null;
		throw new ApiError(error_get_last()["message"], 0, $latencyMs, null, $resolvedIp);
	}
	$statusLineParts = explode(' ', $http_response_header[0], 3);
	$responseCode = isset($statusLineParts[1]) ? intval($statusLineParts[1]) : 0;
	if ($responseCode >= 300) {
		$host = parse_url($url, PHP_URL_HOST) ?: "";
		$resolvedIp = $host ? gethostbyname($host) : null;
		throw new ApiError("API returned unexpected status code {$responseCode}", $responseCode, $latencyMs, $responseBody, $resolvedIp);
	}
	$response_data = json_decode($responseBody, true);
	return $response_data;
}

class ApiError extends Exception {
	public function __construct(
		string $message,
		int $code = 0,
		public readonly ?int $latencyMs = null,
		public readonly ?string $responseBody = null,
		public readonly ?string $resolvedIp = null,
		?\Throwable $previous = null,
	) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Maps this error to the HTTP status the manager should return to the browser.
	 * Range-based (not a per-code switch) so callers never need to hard-code 502:
	 *
	 *   code == 0 (no response / connection failure)  → 502  downstream unreachable
	 *   code >= 500 (upstream 5xx)                    → 502  downstream misbehaving
	 *   code >= 400 (upstream 4xx)                    → 500  we sent bad input (our bug)
	 *   anything else                                 → 502  unexpected; treat as transient
	 *
	 * Intentional per-code special-cases (e.g. 404 → "Not Found" page) are handled
	 * by callers before reaching this fallback.
	 */
	public function managerStatus(): int {
		$code = $this->getCode();
		if ($code === 0 || $code >= 500) {
			return 502;
		}
		if ($code >= 400) {
			return 500;
		}
		return 502;
	}

	/**
	 * Extracts the human-readable reason text from the API's response body.
	 * The API returns JSON with an "error" field on 4xx rejections (e.g.
	 * {"error": "uri … does not start with an allowed origin", "code": "…"}).
	 * Returns an empty string if no parseable reason is available.
	 */
	public function detail(): string {
		if ($this->responseBody === null || $this->responseBody === '') {
			return '';
		}
		$body = json_decode($this->responseBody, true);
		if (!is_array($body) || empty($body['error'])) {
			return '';
		}
		return (string) $body['error'];
	}

	/**
	 * Returns the class-based user-facing message for this error.
	 * Two templates keyed off the same 4xx/5xx boundary:
	 *
	 *   500-class (4xx upstream — our bug):
	 *     "Something went wrong saving this change. Retrying is unlikely to help."
	 *
	 *   502-class (5xx/network — transient):
	 *     "The metadata service is temporarily unavailable. Try again in a moment."
	 *
	 * Any API reason text is exposed separately via detail() — pass it through
	 * as $errorDetail to displayError() rather than embedding it here.
	 */
	public function userMessage(): string {
		if ($this->managerStatus() === 500) {
			return "Something went wrong saving this change. Retrying is unlikely to help.";
		}
		return "The metadata service is temporarily unavailable. Try again in a moment.";
	}
}