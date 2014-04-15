<label for="_meta[priority]">Priority</label>
<input name="_meta[priority]" type="radio" value="1" <?php if(!empty($meta["priority"]) && $meta["priority"] == "1") echo "checked=\"checked\""; ?>>High</input>
<input name="_meta[priority]" type="radio" value="3" <?php if(!empty($meta["priority"]) && $meta["priority"] == "3") echo "checked=\"checked\""; ?>>Medium</input>
<input name="_meta[priority]" type="radio" value="5" <?php if(!empty($meta["priority"]) && $meta["priority"] == "5") echo "checked=\"checked\""; ?>>Low</input>
<input name="_meta[priority]" type="radio" value="0" <?php if(empty($meta["priority"]) || $meta["priority"] == "0") echo "checked=\"checked\""; ?>>Hidden</input>
<br/>
<label for="_meta[website]">Website</label>
<input name="_meta[website]" type="text" placeholder="http://" value="<?php if(!empty($meta['website'])) echo $meta['website']; ?>"/>
<br/>
<label for="_meta[address]">Address</label>
<input name="_meta[address]" type="text" placeholder="Street Address" value="<?php if(!empty($meta['address'])) echo $meta['address']; ?>"/>
<br/>
<label for="_meta[city]">City</label>
<input name="_meta[city]" type="text" value="<?php if(!empty($meta['city'])) echo $meta['city']; ?>"/>
<label for="_meta[state]">State</label>
<select name="_meta[state]"> 
<option value="" <?php if(empty($meta['state'])) echo "selected=\"selected\"" ?>--</option> 
<?php
    $states_arr = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
	foreach ($states_arr as $key=>$value) : 
		echo "<option value=\"" . $key . "\"" . ((!empty($meta['state']) && $meta['state']==$key) ? " selected=\"selected\"" : "") . ">". $value . "</option>";
	endforeach;
?>
</select>
<label for="_meta[zip]">Zip</label>
<input name="_meta[zip]" type="text" />
<label for="_meta[country]">Country</label>
<input name="_meta[counrty]" type="text" value="<?php echo (!empty($meta['country]']) ? $meta['country'] : "United States"); ?>" />

