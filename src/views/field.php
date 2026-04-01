<?php
	/**
	 * @var string $key
	 * @var array|null $values  Tag values array e.g. [{"name":"...", "uri":"..."}], or null
	 * @var array $field
	 * @var bool|null $disabled
	 * @var bool|null $blank
	 * @var bool|null $missing
	 */

	$class = "key-label";
	if (mb_strlen($key) > 12) {
		$class .= " long-key";
	} elseif (mb_strlen($key) > 9) {
		$class .= " medium-key";
	}
	if (!empty($field["beta"])) {
		$class .= " beta";
	}

	// Extract a simple string value for field types that need one
	$value = null;
	if (!empty($values)) {
		$names = array_filter(array_map(function($v) { return $v["name"] ?? null; }, $values), function($n) { return $n !== null; });
		if (count($names) === 1) {
			$value = reset($names);
		} elseif (count($names) > 1) {
			$value = implode(",", $names);
		}
	}
?>
		<label
			for="<?=htmlspecialchars($key)?>"
			class="<?=$class?>"
			<?php if(!empty($field["hint"])) {?>
			title="<?=htmlspecialchars($field["hint"])?>"
			<?php }?>
		>
			<?=htmlspecialchars(str_replace('p.', '', str_replace('_', ' ', $key)))?>
		</label>
		<span class="form-input"><?php
		switch($field["type"]) {
			case "text":
				?>
				<input
					type="text"
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"
					value="<?=htmlspecialchars((string)$value)?>"
					class="input-field input-field-<?=htmlspecialchars($key)?>"
					<?=empty($field["delimiter"]) ? "" : "data-delimiter=\"{$field["delimiter"]}\""?>
					<?=empty($disabled) ? "" : "disabled"?> />
				<?php
				break;
			case "range":
				?>
				<input
					type="range"
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"
					value="<?=htmlspecialchars((string)$value)?>"
					min="0"
					max="10"
					step="0.1"
					data-bad="2"
					data-good="8"
					<?=is_null($value) ? "disabled" : ""?>
					/>
				<input
					type="hidden"
					id="<?=htmlspecialchars($key)?>_hidden"
					name="<?=htmlspecialchars($key)?>"
					value=""
					<?=is_null($value) ? "" : "disabled"?>
					/>
				<span class="preview" ></span>
				<span class="isnull">
					<input
						type="checkbox"
						id="<?=htmlspecialchars($key)?>_null"
						name="<?=htmlspecialchars($key)?>_null"
						<?=is_null($value) ? "checked" : ""?>
						/>
					<label for="<?=htmlspecialchars($key)?>_null">Null</label>
				</span>
				<?php
				break;
			case "discrete-range":
				?>
				<span class="labeled-range">
					<input
						type="range"
						id="<?=htmlspecialchars($key)?>"
						name="<?=htmlspecialchars($key)?>"
						value="<?=htmlspecialchars((string)$value)?>"
						min="<?=htmlspecialchars(is_array($field["values"]) ? (string)array_key_first($field["values"]) : "")?>"
						max="<?=htmlspecialchars(is_array($field["values"]) ? (string)array_key_last($field["values"]) : "")?>"
						step="1"
						list="<?=htmlspecialchars($key)?>_datalist"
						<?=is_null($value) ? "disabled" : ""?>
						/>
					<datalist
						id="<?=htmlspecialchars($key)?>_datalist"><?php
						if (is_array($field["values"])) {
							foreach ($field["values"] as $optionValue => $label) { ?>
								<option value="<?=htmlspecialchars((string)$optionValue)?>" label="<?=htmlspecialchars((string)$label)?>"></option><?php
							}
						}
						?>
					</datalist>
				</span>
				<input
					type="hidden"
					id="<?=htmlspecialchars($key)?>_hidden"
					name="<?=htmlspecialchars($key)?>"
					value=""
					<?=is_null($value) ? "" : "disabled"?>
					/>
				<span class="isnull">
					<input
						type="checkbox"
						id="<?=htmlspecialchars($key)?>_null"
						name="<?=htmlspecialchars($key)?>_null"
						<?=is_null($value) ? "checked" : ""?>
						/>
					<label for="<?=htmlspecialchars($key)?>_null">Null</label>
				</span>
				<?php
				break;
			case "select":
				?>
				<select
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"
					class="select-field select-field-<?=htmlspecialchars($key)?>"
					>
						<option></option><?php
					if (is_array($field["values"])) {
						foreach ($field["values"] as $option => $label) {
						?>
							<option value="<?=htmlspecialchars((string)$option)?>"<?=(strval($option) === $value)?" selected":""?>>
								<?=htmlspecialchars((string)$label)?>
							</option><?php
						}
					}?>
				</select><?php
				break;
			case "multiselect":
				// For multiselect, extract names as an array
				$selectedValues = [];
				if (!empty($values)) {
					$selectedValues = array_filter(array_map(function($v) { return $v["name"] ?? null; }, $values), function($n) { return $n !== null; });
				}
				?>
				<select
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>[]"
					class="select-field select-field-<?=htmlspecialchars($key)?>"
					multiple
					>
					<?php
					if (is_array($field["values"])) {
						foreach ($field["values"] as $option => $name) {
						?>
							<option value="<?=htmlspecialchars((string)$option)?>"<?=in_array($option, $selectedValues)?" selected":""?>>
								<?=htmlspecialchars((string)$name)?>
							</option><?php
						}
					}?>
				</select><?php
				break;
			case "multigroupselect":
				// For multigroupselect, extract names as values
				$selectedValues = [];
				if (!empty($values)) {
					$selectedValues = array_filter(array_map(function($v) { return $v["name"] ?? null; }, $values), function($n) { return $n !== null; });
				}
				?>
				<select
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>[]"
					class="select-field select-field-<?=htmlspecialchars($key)?>"
					multiple
					>
					<?php
					if (is_array($field["values"])) {
						foreach ($field["values"] as $groupname => $options) {
							if (!empty($groupname)) {
								?>
						<optgroup label="<?=htmlspecialchars((string)$groupname)?>">
								<?php
							}
							if (is_array($options)) {
								foreach ($options as $option => $name) {
								?>
									<option value="<?=htmlspecialchars((string)$option)?>"<?=in_array($option, $selectedValues)?" selected":""?>>
										<?=htmlspecialchars((string)$name)?>
									</option>
								<?php
								}
							}
							if (!empty($groupname)) {
								?>
						</optgroup>
								<?php
							}
						}
					}
					?>
				</select><?php
				break;
			case "textarea":
				?>
				<textarea
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"><?=htmlspecialchars((string)$value)?></textarea>
				<?php
				break;
			case "search":
				// Search fields use uri as <option value> and name as display text
				?>
				<span
					is="lucos-search"
					data-api-key="<?=htmlspecialchars((string)getenv('KEY_LUCOS_ARACHNE'))?>"
					data-exclude_types="Track">
					<select
						id="<?=htmlspecialchars($key)?>"
						name="<?=htmlspecialchars($key)?>[]"
						multiple
						>
						<?php
						if (!empty($values)) {
							foreach ($values as $tagValue) {
								$optionValue = $tagValue["uri"] ?? $tagValue["name"] ?? "";
								$displayName = $tagValue["name"] ?? $tagValue["uri"] ?? "";
						?>
							<option value="<?=htmlspecialchars((string)$optionValue)?>" selected>
								<?=htmlspecialchars((string)$displayName)?>
							</option><?php
							}
						}?>
					</select>
				</span>
				<?php
				break;
			case "language":
				// Language fields use code as <option value> (matching TomSelect valueField:'code')
				// and name as display text
				?>
				<span
					is="lucos-lang"
					data-api-key="<?=htmlspecialchars((string)getenv('KEY_LUCOS_ARACHNE'))?>"
					data-no-lang="Instrumental / No Language"
					data-common="en,ga,zxx"
					>
					<select
						id="<?=htmlspecialchars($key)?>"
						name="<?=htmlspecialchars($key)?>[]"
						multiple
						>
						<?php
						if (!empty($values)) {
							foreach ($values as $tagValue) {
								$uri = $tagValue["uri"] ?? "";
								$name = $tagValue["name"] ?? "";
								// Extract language code from URI for TomSelect compatibility
								// URI format: https://eolas.l42.eu/entity/{code}/Language
								$code = $name;
								if (!empty($uri)) {
									$uriParts = explode("/", rtrim($uri, "/"));
									if (count($uriParts) >= 2) {
										$code = $uriParts[count($uriParts) - 2];
									}
								}
								$displayName = $name ?: $code;
						?>
							<option value="<?=htmlspecialchars((string)$code)?>" selected>
								<?=htmlspecialchars((string)$displayName)?>
							</option><?php
							}
						}?>
					</select>
				</span>
				<?php
				break;
			default:
				?>Unknown type "<?=htmlspecialchars((string)$field["type"])?>"<?php
		}?>
			<?php if(!empty($blank)) {?>
				<span class="blank" title="Blank out this field for all tracks">
					<input
						type="checkbox"
						id="<?=htmlspecialchars($key)?>_blank"
						name="<?=htmlspecialchars($key)?>_blank"
						/>
					<label for="<?=htmlspecialchars($key)?>_blank">Blank</label>
				</span>
			<?php }?>
			<?php if(!empty($missing)) {?>
				<span class="ismissing" title="Search for tracks missing this field">
					<input
						type="checkbox"
						id="<?=htmlspecialchars($key)?>_missing"
						name="<?=htmlspecialchars($key)?>_missing"
						/>
					<label for="<?=htmlspecialchars($key)?>_missing">Missing</label>
				</span>
			<?php }?>
		</span>
