<?php
	/**
	 * Renders a single form field
	 * Parameters:
	 * $key - the unquie key of the field
	 * $value - the current value of the field (set to null if not yet set)
	 * $field - details about the form (taken from formfields.php for the given $key)
	 */


	$class = "key-label";
	if (mb_strlen($key) > 12) {
		$class .= " long-key";
	} elseif (mb_strlen($key) > 9) {
		$class .= " medium-key";
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
					value="<?=htmlspecialchars($value)?>" />
				<?php
				break;
			case "range":
				?>
				<input
					type="range" 
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"
					value="<?=htmlspecialchars($value)?>"
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
						value="<?=htmlspecialchars($value)?>"
						min="<?=htmlspecialchars(array_key_first($field["values"]))?>"
						max="<?=htmlspecialchars(array_key_last($field["values"]))?>"
						step="1"
						list="<?=htmlspecialchars($key)?>_datalist"
						<?=is_null($value) ? "disabled" : ""?>
						/>
					<datalist
						id="<?=htmlspecialchars($key)?>_datalist"><?php
						foreach ($field["values"] as $optionValue => $label) { ?>
							<option value="<?=htmlspecialchars($optionValue)?>" label="<?=htmlspecialchars($label)?>"></option><?php
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
				<select id="<?=htmlspecialchars($key)?>" name="<?=htmlspecialchars($key)?>">
						<option></option><?php 
					foreach ($field["values"] as $selectkey => $values) {
						if (is_array($values)) {
							$groupname = $selectkey;
							$options = $values;
					?> 
					<optgroup label="<?=htmlspecialchars($groupname)?>"><?php
						} else {
							$groupname = null;
							$options = [$selectkey => $values];
						}
						foreach ($options as $option => $label) {
					?> 
						<option value="<?=htmlspecialchars($option)?>"<?=(strval($option) === $value)?" selected":""?>>
							<?=htmlspecialchars($label)?> 
						</option><?php
						}
						if ($groupname) {
					?> 
					</optgroup>
					<?php
						}
					}?> 
				</select><?php
				break;
			case "textarea":
				?>
				<textarea
					id="<?=htmlspecialchars($key)?>"
					name="<?=htmlspecialchars($key)?>"><?=htmlspecialchars($value)?></textarea>
				<?php
				break;
			default:
				?>Unknown type "<?=$field["type"]?>"<?php
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
		</span>