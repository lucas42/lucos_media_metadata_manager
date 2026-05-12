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
		throw new ApiError(error_get_last()["message"], 0, $latencyMs);
	}
	preg_match('/([0-9])\d+/', $http_response_header[0], $status_matches);
  	$responseCode = intval($status_matches[0]);
	if ($responseCode >= 300) {
		throw new ApiError("API returned unexpected status code {$responseCode}", $responseCode, $latencyMs, $responseBody);
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
		?\Throwable $previous = null,
	) {
		parent::__construct($message, $code, $previous);
	}
}