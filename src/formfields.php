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
		],
		"producer" => [
			"type" => "text",
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
			"type" => "multigroupselect",
			"values" => [
				"Common Languages" => [ // Put the ones I use the most at the top
					"en" => "English",
					"ga" => "Irish",
					"zxx" => "Instrumental / No Language",
				],
				"Celtic Languages" => [
					"gd" => "Scottish Gaelic", // Goidelic
					"cy" => "Welsh",           // Brythonic
					"br" => "Breton",          // Brythonic
					"kw" => "Cornish",         // Brythonic
					"owl" => "Old Welsh",      // Brythonic
				],
				"Romance Languages" => [
					"fr" => "French",
					"it" => "Italian",
					"es" => "Spanish",
					"pt" => "Portuguese",
					"la" => "Latin",
					"ro" => "Romanian",
				],
				"Germanic Languages" => [
					"de" => "German",                 // West Germanic
					"nl" => "Dutch",                  // West Germanic
					"vls" => "Flemish",               // West Germanic
					"emen" => "Early Modern English", // West Germanic
					"sco" => "Scots",                 // West Germanic
					"yi" => "Yiddish",                // West Germanic
					"sv" => "Swedish",                // North Germanic
					"is" => "Icelandic",              // North Germanic
					"da" => "Danish",                 // North Germanic
				],
				"Bantu Languaes" => [
					"sw" => "Swahili",
					"zu" => "Zulu",
					"nd" => "Northern Ndebele",
				],
				"Balto-Slavic Languages" => [
					"lt" => "Lithuanian", // Baltic
					"ru" => "Russian",    // Slavic
					"uk" => "Ukrainian",  // Slavic
				],
				"Other Languages" => [
					"ar" => "Arabic",    // Semitic language
					"he" => "Hebrew",    // Semitic language
					"pa" => "Punjabi",   // Indo-Aryan language
					"hi" => "Hindi",     // Indo-Aryan language
					"chr" => "Cherokee", // Iroquoian language
					"mjy" => "Mohican",  // Algic language
					"cmn" => "Mandarin", // Sinitic language
					"ja" => "Japanese",  // Japonic language
					"mn" => "Mongolian", // Mongolic language
					"emk" => "Maninka",  // Mande language
				],
				"Fictional Languages" => [
					"art-x-von" => "Hopelandic",  // Fictional language created and used by Sigur Rós, also known as Vonlenska
					"art-x-simlish" => "Simlish", // Fictional language used in The Sims in-game universe
					"art-x-navi" => "Na'vi",      // Fictional language used in the Avatar franchise, spoken by the Na'vi race
				],
			],
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
				"bandcamp" => "Bandcamp",
				"cd-rip" => "CD Rip",
				"sample" => "Free Sample", // For example, included when purchasing a new mp3 player
				"charity-auction" => "Charity Auction",
				"download" => "Download", // For example, free download from the artist's website
				"7digital" => "7 Digital",
				"amazon" => "Amazon Music",
				"radio" => "Recorded from Radio",
				"live" => "Live recording",
				"audio-software" => "Produced using audio software", // For example, an original composition created using musescore or audacity
				"ambiguous" => "Ambiguous", // The source of this track isn't entirely clear
				"game" => "Game Soundtrack", // For example, included in the resources folder of a computer game
				"newgrounds" => "Newgrounds",
				"itunes" => "iTunes",
				"vinyl" => "Recorded from a vinyl record",
			],
			"hint" => "Where this track was sourced from", // Not covering the entire provenance chain, just how it arrived in this collection.
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
				],
			],
			"hint" => "Things in this track which may offend some people",
		],
		"comment" => [
			"type" => "textarea",
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
		$form_fields["collections"]["values"][$collection["slug"]] = $collection["name"];
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
