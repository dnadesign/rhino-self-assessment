<div class="self-assessment-form">
	<% if $IncludeFormTag %>
	<form $AttributesHTML>
	<% end_if %>
		<% include Progress Max=$Controller.TotalStepCount %>

		<% if $Controller.HasStartStep %>
			<% include StartScreenStep %>
		<% end_if %>

		<% loop $Fields %>
			$FieldHolder
		<% end_loop %>

	<% if $IncludeFormTag %>
	</form>
	<% end_if %>
</div>
