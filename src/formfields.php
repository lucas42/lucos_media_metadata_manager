<?php

function getFormFields() {
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
		"rating" => [
			"type" => "range",
		],
		"event" => [
			"type" => "select",
			"values" => [
				"xmas" => "🎄 Christmas",
				"hallowe'en" => "🎃 Hallowe'en",
				"eurovision" => "✨ Eurovision Song Contest",
			],
			"hint" => "Which occasion is this track associated with?",
		],
		"format" => [
			"type" => "select",
			"values" => [
				"speech" => "🗣️ Speech",
				"fx" => "🔊 Sound Effect",
				"podcast" => "🎙️ Podcast",
			],
		],
		"singalong" => [
			"type" => "select",
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
			"type" => "select",
			"values" => [
				"en" => "English",
				"ga" => "Irish",
				"fr" => "French",
				"de" => "German",
				"cy" => "Welsh",
				"gd" => "Scottish Gaelic",
				"it" => "Italian",
				"sco" => "Scots",
				"br" => "Breton",
				"sv" => "Swedish",
				"ru" => "Russian",
				"la" => "Latin",
				"he" => "Hebrew",
				"es" => "Spanish",
				"pa" => "Punjabi",
				"owl" => "Old Welsh",
				"is" => "Icelandic",
				"mul" => "Multiple Languages",
				"zxx" => "Instrumental / No Language",
			],
			"hint" => "The primary language used for lyrics in this track",
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
				"radio" => "Recorded from Radio",
				"live" => "Live recording",
				"audio-software" => "Produced using audio software", // For example, an original composition created using musescore or audacity
				"ambiguous" => "Ambiguous", // The source of this track isn't entirely clear
				"game" => "Game Soundtrack", // For example, included in the resources folder of a computer game
			],
			"hint" => "Where this track was sourced from", // Not covering the entire provenance chain, just how it arrived in this collection.
		],
	];
}

function getFormKeys() {
	return array_keys(getFormFields());
}
