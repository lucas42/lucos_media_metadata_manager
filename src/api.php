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
 * Normalizes V3 tag format for display in views.
 * V3 tags are maps of predicate to arrays of {"name": ..., "uri": ...} objects.
 * This converts them to simple strings (single-value) or comma-separated strings (multi-value),
 * matching the format the existing views expect.
 *
 * @param array $tags V3 tag data (e.g. {"title": [{"name": "Song"}], "composer": [{"name": "A"}, {"name": "B"}]})
 * @return array Normalized tags (e.g. {"title": "Song", "composer": "A,B"})
 */
function normalizeV3Tags($tags) {
	$result = [];
	foreach ($tags as $key => $values) {
		if (!is_array($values)) {
			$result[$key] = $values;
			continue;
		}
		$names = array_map(function($v) { return $v["name"] ?? ""; }, $values);
		$names = array_filter($names, function($v) { return $v !== ""; });
		if (empty($names)) {
			$result[$key] = null;
		} elseif (count($names) === 1) {
			$result[$key] = reset($names);
		} else {
			$result[$key] = implode(",", $names);
		}
	}
	return $result;
}

/**
 * Converts form tag data to V3 format for API writes.
 * Each tag value becomes an array of {"name": ...} objects.
 *
 * @param array $tags Tag data from form (strings or arrays)
 * @param array $fieldConfig Form field configuration from getTagFields()
 * @return array V3 formatted tags
 */
function tagsToV3Format($tags, $fieldConfig = []) {
	$result = [];
	foreach ($tags as $key => $value) {
		if (is_array($value)) {
			// Multi-select fields already provide arrays
			$result[$key] = array_values(array_map(function($v) {
				return ["name" => $v];
			}, array_filter($value, function($v) { return $v !== ""; })));
		} elseif ($value === "" || $value === null) {
			// Empty string means clear the tag
			$result[$key] = [];
		} else {
			// Check if the field uses a delimiter (e.g. comma-separated text)
			$config = $fieldConfig[$key] ?? null;
			if ($config && !empty($config["delimiter"])) {
				$parts = array_map('trim', explode($config["delimiter"], $value));
				$parts = array_filter($parts, function($v) { return $v !== ""; });
				$result[$key] = array_values(array_map(function($v) {
					return ["name" => $v];
				}, $parts));
			} else {
				$result[$key] = [["name" => $value]];
			}
		}
	}
	return $result;
}

class ApiError extends Exception {}