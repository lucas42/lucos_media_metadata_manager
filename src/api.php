<?php

function fetchFromApi($path, $method="GET", $data=null, $headers=[]) {
	$url = getenv("MEDIA_API") . $path;
	$apikey = getenv("KEY_LUCOS_MEDIA_METADATA_API");
	$headers[] = "Authorization: Bearer $apikey";
	$http_params = [
		"method" => $method,
		"header" => $headers,
		"ignore_errors" => true,
	];
	if ($data !== null) {
		$http_params["content"] = json_encode($data);
		$http_params["header"][] = "Content-Type: application/json";
	}
	$context = stream_context_create([
		"http" => $http_params
	]);
	$responseBody = @file_get_contents($url, false, $context);
	if (empty($http_response_header)) {
		throw new ApiError(error_get_last()["message"]);
	}
	preg_match('/([0-9])\d+/', $http_response_header[0], $status_matches);
  	$responseCode = intval($status_matches[0]);
	if ($responseCode >= 300) {
		throw new ApiError("API returned unexpected status code {$responseCode}", $responseCode);
	}
	$response_data = json_decode($responseBody, true);
	return $response_data;
}

/**
 * Extracts the form-appropriate value from a V3 tag array.
 * V3 tags are arrays of {"name": ..., "uri": ...} objects.
 *
 * For URI-based fields (search, language), returns comma-separated URIs.
 * For other fields, returns the name(s) as a string.
 *
 * @param array $v3Values V3 tag array (e.g. [{"name": "March", "uri": "https://..."}])
 * @param array $fieldConfig Field config from getTagFields() (optional)
 * @return string|null The form value
 */
function extractFormValue($v3Values, $fieldConfig = []) {
	if (!is_array($v3Values) || empty($v3Values)) {
		return null;
	}
	$useUri = in_array($fieldConfig["type"] ?? "", ["search", "language"]);
	$extracted = [];
	foreach ($v3Values as $v) {
		if ($useUri && !empty($v["uri"])) {
			$extracted[] = $v["uri"];
		} elseif (!empty($v["name"]) || $v["name"] === "0") {
			$extracted[] = $v["name"];
		}
	}
	if (empty($extracted)) {
		return null;
	}
	if (count($extracted) === 1) {
		return $extracted[0];
	}
	return implode(",", $extracted);
}

/**
 * Converts form tag data to V3 format for API writes.
 * URI-based fields (search, language) use the "uri" property.
 * Other fields use the "name" property.
 *
 * @param array $tags Tag data from form (strings or arrays)
 * @param array $fieldConfig Form field configuration from getTagFields()
 * @return array V3 formatted tags
 */
function tagsToV3Format($tags, $fieldConfig = []) {
	$result = [];
	foreach ($tags as $key => $value) {
		$config = $fieldConfig[$key] ?? [];
		$useUri = in_array($config["type"] ?? "", ["search", "language"]);
		if (is_array($value)) {
			// Multi-select fields already provide arrays
			$result[$key] = array_values(array_map(function($v) use ($useUri) {
				return $useUri ? ["uri" => $v] : ["name" => $v];
			}, array_filter($value, function($v) { return $v !== ""; })));
		} elseif ($value === "" || $value === null) {
			// Empty string means clear the tag
			$result[$key] = [];
		} else {
			// Check if the field uses a delimiter (e.g. comma-separated text)
			if (!empty($config["delimiter"])) {
				$parts = array_map('trim', explode($config["delimiter"], $value));
				$parts = array_filter($parts, function($v) { return $v !== ""; });
				$result[$key] = array_values(array_map(function($v) use ($useUri) {
					return $useUri ? ["uri" => $v] : ["name" => $v];
				}, $parts));
			} else {
				$result[$key] = [$useUri ? ["uri" => $value] : ["name" => $value]];
			}
		}
	}
	return $result;
}

class ApiError extends Exception {}