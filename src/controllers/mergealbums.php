<?php
require_once("../api.php");
require_once("../controllers/error.php");

/**
 * Renders the album merge page.
 *
 * The page has three phases, driven by URL state:
 *   1. No targetId   — search for the album to keep.
 *   2. targetId set  — search for source albums to merge in; builds a
 *                      running list as the user adds sources.
 *   3. confirm=1     — confirmation summary with track counts per source.
 *
 * Accepts ?targetId= and ?sourceIds[]= for pre-filling (e.g. from a future
 * duplicate-candidates view).
 */
function showMergeAlbums($params) {
	$targetId  = isset($params["targetId"]) && is_numeric($params["targetId"])
		? (int)$params["targetId"] : null;

	$rawSourceIds = isset($params["sourceIds"]) ? $params["sourceIds"] : [];
	if (!is_array($rawSourceIds)) $rawSourceIds = [$rawSourceIds];
	$sourceIds = array_values(array_map("intval", array_filter($rawSourceIds, "is_numeric")));

	$confirm     = !empty($params["confirm"]);
	$searchQuery = trim($params["q"]  ?? "");
	$sourceQuery = trim($params["sq"] ?? "");

	$target        = null;
	$searchResults = [];
	$sources       = [];
	$sourceTrackCounts = [];
	$fetchError    = null;

	// Fetch target album details.
	if ($targetId !== null) {
		try {
			$target = fetchFromApi("/v3/albums/" . $targetId);
		} catch (ApiError $error) {
			if ($error->getCode() == 404) {
				displayError(404, "Album {$targetId} Not Found");
			} else {
				displayError(502, "Can't fetch album from API.\n\n" . $error->getMessage());
			}
			return;
		}
	}

	// Fetch album search results.
	if ($searchQuery !== "" && $targetId === null) {
		// Phase 1: searching for the target.
		try {
			$data = fetchFromApi("/v3/albums?" . http_build_query(["q" => $searchQuery]));
			$searchResults = $data["albums"] ?? [];
		} catch (ApiError $error) {
			$fetchError = "Couldn't search albums: " . $error->getMessage();
		}
	} elseif ($sourceQuery !== "" && $targetId !== null) {
		// Phase 2: searching for sources — exclude the target and already-selected sources.
		try {
			$data = fetchFromApi("/v3/albums?" . http_build_query(["q" => $sourceQuery]));
			$excluded = array_merge([$targetId], $sourceIds);
			$searchResults = array_values(array_filter(
				$data["albums"] ?? [],
				fn($a) => !in_array((int)$a["id"], $excluded)
			));
		} catch (ApiError $error) {
			$fetchError = "Couldn't search albums: " . $error->getMessage();
		}
	}

	// Fetch source album details (needed for the running list and confirmation).
	foreach ($sourceIds as $sid) {
		try {
			$sources[] = fetchFromApi("/v3/albums/" . $sid);
		} catch (ApiError $error) {
			// Skip sources that can't be fetched; the API will reject them on submit.
		}
	}

	// On the confirmation step, fetch track counts per source album.
	if ($confirm && !empty($sources)) {
		foreach ($sources as $source) {
			try {
				$tdata = fetchFromApi("/v3/tracks?" . http_build_query([
					"p.album.uri" => $source["uri"],
					"page"        => 1,
				]));
				$sourceTrackCounts[$source["id"]] = $tdata["totalTracks"] ?? null;
			} catch (ApiError $error) {
				$sourceTrackCounts[$source["id"]] = null;
			}
		}
	}

	require("../views/mergealbums.php");
}

/**
 * Executes the album merge by calling POST /v3/albums/merge, then redirects
 * to the target album page on success.
 */
function mergeAlbums($postdata) {
	$targetId = isset($postdata["targetId"]) && is_numeric($postdata["targetId"])
		? (int)$postdata["targetId"] : null;

	$rawSourceIds = isset($postdata["sourceIds"]) ? $postdata["sourceIds"] : [];
	if (!is_array($rawSourceIds)) $rawSourceIds = [$rawSourceIds];
	$sourceIds = array_values(array_map("intval", array_filter($rawSourceIds, "is_numeric")));

	if ($targetId === null) {
		displayError(400, "No target album specified.");
		return;
	}
	if (empty($sourceIds)) {
		displayError(400, "No source albums specified.");
		return;
	}

	try {
		fetchFromApi("/v3/albums/merge", "POST", [
			"targetId"  => $targetId,
			"sourceIds" => $sourceIds,
		]);
		header("Location: /albums/{$targetId}?merged=true", true, 303);
	} catch (ApiError $error) {
		if ($error->getCode() == 404) {
			displayError(404, "One or more albums not found.");
		} elseif ($error->getCode() == 400) {
			displayError(400, "Invalid merge request.");
		} else {
			displayError(502, "Error merging albums.\n\n" . $error->getMessage());
		}
	}
}
