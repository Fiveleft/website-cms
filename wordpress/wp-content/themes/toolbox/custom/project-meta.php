<div>
	<label for="_meta[subtitle]">Subtitle</label>
	<input name="_meta[subtitle]" type="text" value="<?php if(!empty($meta['subtitle'])) echo $meta['subtitle']; ?>"/>
</div>
<div>
	<label for="_meta[priority]">Priority</label>
	<input name="_meta[priority]" type="radio" value="1" <?php if(!empty($meta["priority"]) && $meta["priority"] == "1") echo "checked=\"checked\""; ?>>High</input>
	<input name="_meta[priority]" type="radio" value="3" <?php if(!empty($meta["priority"]) && $meta["priority"] == "3") echo "checked=\"checked\""; ?>>Medium</input>
	<input name="_meta[priority]" type="radio" value="5" <?php if(!empty($meta["priority"]) && $meta["priority"] == "5") echo "checked=\"checked\""; ?>>Low</input>
	<input name="_meta[priority]" type="radio" value="0" <?php if(empty($meta["priority"]) || $meta["priority"] == "0") echo "checked=\"checked\""; ?>>Hidden</input>
	<br/>
	<label for="_meta[website]">Website</label>
	<input name="_meta[website]" type="text" placeholder="http://" value="<?php if(!empty($meta['website'])) echo $meta['website']; ?>"/>
	<br/>
	<label for="_meta[launchdate]">Date Launched</label>
	<input name="_meta[launchdate]" type="date" placeholder="" value="<?php if(!empty($meta['launchdate'])) echo $meta['launchdate']; ?>"/>
	<br/>
</div>