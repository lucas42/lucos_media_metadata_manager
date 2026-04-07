<?php
require_once("../formfields.php");
require_once("../api.php");

/**
 * Converts form post data for a single tag field to V3 format.
 * Each field type determines how values map to {"name": ..., "uri": ...} objects.
 *
 * Search fields: form sends URIs, JS adds names → {"name": name, "uri": uri}
 * Multi-text fields: form sends arrays of names → [{"name": name}, ...]
 * Other fields: form sends a single name → [{"name": value}]
 *
 * $names and $uris are parallel arrays from hidden inputs added by form-ui.js
 * on form submission. If JS fails, we fall back to using the form value for both.
 */
function formValueToV3($value, $fieldConfig, $names = null, $uris = null) {
	$type = $fieldConfig["type"] ?? "text";
	$isUriField = $type === "search";

	if (is_array($value)) {
		$result = [];
		$values = array_values($value);
		foreach ($values as $idx => $v) {
			if ($v === "") continue;
			if ($isUriField) {
				$name = (!empty($names) && isset($names[$idx])) ? $names[$idx] : $v;
				$uri = (!empty($uris) && isset($uris[$idx])) ? $uris[$idx] : $v;
				$result[] = ["name" => $name, "uri" => $uri];
			} else {
				$result[] = ["name" => $v];
			}
		}
		return $result;
	}

	if ($value === "" || $value === null) {
		return [];
	}

	if ($isUriField) {
		$name = (!empty($names) && isset($names[0])) ? $names[0] : $value;
		$uri = (!empty($uris) && isset($uris[0])) ? $uris[0] : $value;
		return [["name" => $name, "uri" => $uri]];
	}

	return [["name" => $value]];
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
		$names = $postdata["{$key}_names"] ?? null;
		$uris = $postdata["{$key}_uris"] ?? null;
		$tags[$key] = formValueToV3($postdata[$key] ?? null, $fieldConfig[$key] ?? [], $names, $uris);
	}
	$api_data["tags"] = $tags;

	try {
		fetchFromApi("/v3/tracks/{$trackid}", "PATCH", $api_data);
		header("Location: /tracks/{$trackid}?saved=true", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error updating track in API.\n\n".$error->getMessage());
	}
}
