<?php
require_once("../formfields.php");
require_once("../api.php");

/**
 * Converts form post data for a single tag field to V3 format.
 * Each field type determines how values map to {"name": ..., "uri": ...} objects.
 *
 * Search fields: lucos-search injects indexed structured pairs via the formdata
 * event, so PHP parses field[N][uri] / field[N][name] into nested arrays directly.
 * Multi-text fields: form sends arrays of names → [{"name": name}, ...]
 * Other fields: form sends a single name → [{"name": value}]
 */
function formValueToV3($value, $fieldConfig) {
	$type = $fieldConfig["type"] ?? "text";
	$isUriField = $type === "search";

	if (is_array($value)) {
		$result = [];
		foreach (array_values($value) as $v) {
			if ($v === "" || $v === null) continue;
			if ($isUriField && is_array($v)) {
				// Indexed structured format from lucos-search: ['uri' => ..., 'name' => ...]
				$uri = $v['uri'] ?? '';
				$name = $v['name'] ?? $uri;
				if ($uri === '') continue;
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
		return [["name" => $value, "uri" => $value]];
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
		$tags[$key] = formValueToV3($postdata[$key] ?? null, $fieldConfig[$key] ?? []);
	}
	$api_data["tags"] = $tags;

	try {
		fetchFromApi("/v3/tracks/{$trackid}", "PATCH", $api_data);
		header("Location: /tracks/{$trackid}?saved=true", true, 303);
	} catch (ApiError $error) {
		displayError(502, "Error updating track in API.\n\n".$error->getMessage());
	}
}
