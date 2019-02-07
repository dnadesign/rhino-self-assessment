<% include StartScreenStep %>

<div class="self-assessment-form">
	<% include Progress Max=$Top.Controller.TotalQuestionCount %>
	
	<% if $IncludeFormTag %>
	<form $AttributesHTML>
	<% end_if %>

		<% loop $Fields %>
			$FieldHolder
		<% end_loop %>

		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-16-24 pure-push-md-4-24">
				<div class="self-assessment-actions self-assessment-card self-assessment-card--inactive">
					<% include BusinessInfoStep Actions=$Actions %>
				</div>
			</div>
		</div>

	<% if $IncludeFormTag %>
	</form>
	<% end_if %>
</div>

