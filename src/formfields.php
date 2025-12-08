<?php

/**
 * Returns hardcoded fields used for tags
 * The API doesn't validate tags, any key/value pair is allowed.
 * So any values here are only enforced by the UI.
 * @return associative array where the key is the name of the tag and value is an associative array of settings about that field
 */
function getTagFields() {
	return [
		"title" => [
			"type" => "text",
		],
		"artist" => [
			"type" => "text",
		],
		"album" => [
			"type" => "text",
		],
		"composer" => [
			"type" => "text",
			"delimiter" => ",",
		],
		"producer" => [
			"type" => "text",
			"delimiter" => ",",
		],
		"rating" => [
			"type" => "range",
		],
		"singalong" => [
			"type" => "discrete-range",
			"values" => [
				0 => "No chance",
				1 => "Hum a Bit",
				2 => "Join in with the chorus in a club",
				3 => "Give it a go at karaoke",
				4 => "Karaoke without looking at the screen",
				5 => "Do it accapella without lyric sheet",
			],
			"hint" => "Can Luke sing along to it?",
		],
		"memory" => [
			"type" => "text",
			"hint" => "What this song reminds Luke of",
		],
		"theme_tune" => [
			"type" => "text",
			"hint" => "What this track was used as the primary theme tune for",
		],
		"soundtrack" => [
			"type" => "text",
			"hint" => "What this track appeared in the soundtrack of",
		],
		"lyrics" => [
			"type" => "textarea",
		],
		"language" => [
			"type" => "language",
			"beta" => true,
			"hint" => "The language(s) used for lyrics in this track",
		],
		"dance" => [
			"type" => "select",
			"values" => [
				"Lindy Hop" => "Lindy Hop",
				"Charleston" => "Charleston",
				"Blues" => "Blues",
				"Collegiate Shag" => "Collegiate Shag",
				"Céilí" => "Céilí",
				"Irish Step" => "Irish Step",
				"Ceilidh" => "Ceilidh",
				"Morris" => "Morris",
				"Regency" => "Regency",
				"Waltz" => "Waltz",
				"Foxtrot" => "Foxtrot",
				"Tango" => "Tango",
				"Quickstep" => "Quickstep",
				"Rhumba" => "Rhumba",
				"Samba" => "Samba",
				"Cha Cha" => "Cha Cha",
				"Jive" => "Jive",
				"Rock'n'Roll" => "Rock'n'Roll",
				"Disco" => "Disco",
				"Bhangra" => "Bhangra",
				"Bossa Nova" => "Bossa Nova",
				"Mambo" => "Mambo",
				"bespoke" => "Track has its own dance",
			],
			"hint" => "Style of dance which goes with this track",
		],
		"provenance" => [
			"type" => "select",
			"values" => [
				"7digital" => "7 Digital",
				"amazon" => "Amazon Music",
				"ambiguous" => "Ambiguous", // The source of this track isn't entirely clear
				"audio-software" => "Produced using audio software", // For example, an original composition created using musescore or audacity
				"bandcamp" => "Bandcamp",
				"cd-rip" => "CD Rip",
				"charity-auction" => "Charity Auction",
				"download" => "Download", // For example, free download from the artist's website
				"game" => "Game Soundtrack", // For example, included in the resources folder of a computer game
				"itunes" => "iTunes",
				"live" => "Live recording",
				"newgrounds" => "Newgrounds",
				"qobuz" => "Qobuz",
				"radio" => "Recorded from Radio",
				"sample" => "Free Sample", // For example, included when purchasing a new mp3 player
				"vinyl" => "Recorded from a vinyl record",
			],
			"hint" => "Where this track was sourced from", // Not covering the entire provenance chain, just how it arrived in this collection.
		],
		"availability" => [
			"type" => "discrete-range",
			"values" => [
				0 => "I have the canoncial copy", // For example, I've recorded myself playing
				1 => "Likely can't find elsewhere", // For example, a song which was never published, by a band which is now defunct
				2 => "Would need research to find", // For example, had been published on vinyl, but no longer published and not available digitally
				3 => "Could find after nontrivial searching", // For example, available on a single digital music platform
				4 => "Ubiquitous", // For example, available in multiple compilation albums
			],
			"hint" => "How easy it would be to replace this track if something happened to my collection",
		],
		"offence" => [
			"type" => "multigroupselect",
			"values" => [
				"" => [
					"swearing" => "Swearing",
					"slurs" => "Slurs",
					"sacrilege" => "Sacrilege",
					"violence" => "Violence",
					"war" => "War",
					"lèse-majesté" => "Lèse-majesté",
					"jingoism" => "Jingoism",
					"smut" => "Smut",
					"alcohol" => "Alcohol",
					"drugs" => "Drugs",
					"kink" => "Kink",
					"arson" => "Arson",
					"domestic-abuse" => "Domestic Abuse",
					"colonialism" => "Colonialism",
					"sexual-assault" => "Sexual Assault",
					"sex-work" => "Sex Work",
					"animal-cruelty" => "Animal Cruelty",
					"fascism" => "Fascism",
					"self-harm" => "Self Harm (including suicide)",

					/* Types of Discrimination */
					"racism" => "Racism",
					"religious-discrimination" => "Religious Discrimination", // Includes Antisemitism, Islamophobia and others
					"sexism" => "Sexism",
					"ableism" => "Ableism", // Includes physical and mental
					"homophobia" => "Homophobia",
					"transphobia" => "Transphobia",
				],
			],
			"hint" => "Things in this track which may offend some people",
		],
		"comment" => [
			"type" => "textarea",
		],
		"about" => [
			"type" => "search",
			"beta" => true,
		],
		"mentions" => [
			"type" => "search",
			"beta" => true,
		],
	];
}
/**
 * Get a dynamic list of collections from the API
 * @throws an exception if the request to the API fails
 * @return an array of collections, each an associative array containing "slug" and "name" keys
 */
function getCollections() {
	return fetchFromApi("/v2/collections");
}

/**
 * Returns a list of fields for use in the UI, containing both tags and a collections fields
 * @throws an exception if the request to the API for collections fails
 * @return associative array where the key is the name of the field and value is an associative array of settings about that field
 */
function getFormFields() {
	$form_fields = getTagFields();
	$form_fields["collections"] = [
		"type" => "multiselect",
		"values" => [],
		"hint" => "The collections this track is part of",
	];
	foreach (getCollections() as $collection) {
		$form_fields["collections"]["values"][$collection["slug"]] = "${collection['icon']} ${collection['name']}";
	}
	return $form_fields;
}

/**
 * Give the names of all tags managed through the UI
 * (Doesn't touch collections)
 * @return An array of strings
 */
function getTagKeys() {
	return array_keys(getTagFields());
}
