<?php
require_once("../formfields.php");
require_once("../api.php");

/**
 * Converts form post data for a single tag field to V3 format.
 * Each field type determines how values map to {"name": ..., "uri": ...} objects.
 *
 * Search/language fields: form sends URIs → {"uri": value}
 * Other fields: form sends names → {"name": value}
 * Delimiter fields: split into multiple tag values
 */
function formValueToV3($value, $fieldConfig) {
	$type = $fieldConfig["type"] ?? "text";
	$isUriField = in_array($type, ["search", "language"]);

	if (is_array($value)) {
		// Multi-select/search fields send arrays
		return array_values(array_map(function($v) use ($isUriField) {
			return $isUriField ? ["uri" => $v] : ["name" => $v];
		}, array_filter($value, function($v) { return $v !== ""; })));
	}

	if ($value === "" || $value === null) {
		return [];
	}

	// Delimiter-separated text fields (e.g. composer)
	if (!empty($fieldConfig["delimiter"])) {
		$parts = array_map('trim', explode($fieldConfig["delimiter"], $value));
		$parts = array_filter($parts, function($v) { return $v !== ""; });
		return array_values(array_map(function($v) use ($isUriField) {
			return $isUriField ? ["uri" => $v] : ["name" => $v];
		}, $parts));
	}

	return [$isUriField ? ["uri" => $value] : ["name" => $value]];
}

/**
 * Updates the metadata of a given track id
 * Sets the value to of each field to the value in $postdata for that key
 **/
function updateTrack($trackid, $postdata) {
	$api_data = array();
	$api_data["collections"] = [];
	if (isset($postdata["collections"])) {
		foreach($postdata["collections"] as $slug) {
			$api_data["collections"][] = [
				"slug" => $slug,
			];
		}
		unset($postdata["collections"]);
	}
	$tags = array();
	$fieldConfig = getTagFields();
	foreach (getTagKeys() as $key) {
		$tags[$key] = formValueToV3($postdata[$key], $fieldConfig[$key] ?? []);
	}
	$api_data["tags"] = $tags;

	try {
		fetchFromApi("/v3/tracks/{$trackid}", "PATCH", $api_data);
		header("Location: /tracks/{$trackid}?saved=true", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error updating track in API.\n\n".$error->getMessage());
	}
}
