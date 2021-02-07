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
		]
	];
}

function getFormKeys() {
	return array_keys(getFormFields());
}