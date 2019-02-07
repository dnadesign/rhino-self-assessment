<fieldset $AttributesHTML>
	<% loop $Options %>
		<input id="$ID" name="$Name" type="radio" value="$Value.ATT"<% if $isChecked %> checked<% end_if %>/>
		<label for="$ID" class="self-assessment-questionlabel">$Title</label>	
	<% end_loop %>
</fieldset>