<fieldset $AttributesHTML>
	<% loop $Options %>
		<input id="$ID" class="self-assessment-optioninput" name="$Name" type="radio" value="$Value.ATT"<% if $isChecked %> checked<% end_if %> required/>
		<label for="$ID" class="self-assessment-optionlabel">$Title</label>
	<% end_loop %>
</fieldset>
