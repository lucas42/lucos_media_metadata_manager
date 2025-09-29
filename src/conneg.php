<?php
// Content Negotiation utility functions

// Mapping MIME types to rdflib serialization format
$RDF_FORMATS = [
	"text/turtle" => "turtle",
	"application/ld+json" => "json-ld",
	"application/rdf+xml" => "xml",
	"application/n-triples" => "nt",
	"application/xml" => "xml", // Sometimes used for RDF/XML
];

/**
 * Parses an Accept header and returns a list of (mime, qvalue) sorted by qvalue descending.
 */
function parse_accept_header() {
	$accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
	$parts = explode(",", $accept);
	$mimes = [];

	foreach ($parts as $part) {
		$subparts = explode(";", $part);
		$mime = trim($subparts[0]);
		$q = 1.0;

		for ($i = 1; $i < count($subparts); $i++) {
			$sub = trim($subparts[$i]);
			if (strpos($sub, "q=") === 0) {
				$val = substr($sub, 2);
				if (is_numeric($val)) {
					$q = floatval($val);
				}
			}
		}
		$mimes[] = [$mime, $q];
	}

	// Sort by qvalue descending
	usort($mimes, function($a, $b) {
		return $b[1] <=> $a[1];
	});

	return $mimes;
}

/**
 * Returns the best RDF mime type and rdflib serialization format for the Accept header.
 */
function pick_best_rdf_format() {
	global $RDF_FORMATS;
	$parsed = parse_accept_header();

	foreach ($parsed as $pair) {
		list($mime, $q) = $pair;
		if (array_key_exists($mime, $RDF_FORMATS)) {
			return [$RDF_FORMATS[$mime], $mime];
		} elseif ($mime === "*/*") {
			// Once */* is reached in priority order, don't consider anything lower
			continue;
		}
	}

	// Default to the first in RDF_FORMATS
	$firstKey = array_key_first($RDF_FORMATS);
	return [$RDF_FORMATS[$firstKey], $firstKey];
}

/**
 * Returns true if the client would prefer some form of RDF more than HTML.
 * Otherwise returns false.
 */
function choose_rdf_over_html() {
	global $RDF_FORMATS;
	$parsed = parse_accept_header();
	$rdf_weight = 0.0;
	$html_weight = 0.0;

	foreach ($parsed as $pair) {
		list($mime, $q) = $pair;
		if (array_key_exists($mime, $RDF_FORMATS)) {
			if ($q > $rdf_weight) {
				$rdf_weight = $q;
			}
		}
		if ($mime === "text/html") {
			if ($q > $html_weight) {
				$html_weight = $q;
			}
		}
	}

	// Only redirect to RDF if rdf_weight is non-zero and is preferred or equal to html
	return ($rdf_weight > 0 && $rdf_weight >= $html_weight);
}
