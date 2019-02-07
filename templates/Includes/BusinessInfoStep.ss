<div class="self-assessment-actions-column">
	<h2 class="self-assessment-actions-title">
		$Top.Controller.BusinessScreenTitle
	</h2>

	$Top.Controller.BusinessScreenContent
</div>

<div class="self-assessment-actions-column">
	<div class="self-assessment-businfofields">
		<div>
			<% loop $Top.Controller.BusinessInformationFields %>
				$FieldHolder
			<% end_loop %>
		</div>

		<button type="submit" class="pure-button self-assessment-button" data-self-assessment-results-button data-self-assessment-title="$Top.Controller.Title">
			<% if $Top.Controller.SubmitButtonText %>
				$Top.Controller.SubmitButtonText
			<% else %>
				Show my results
			<% end_if %>
		</button>
	</div>
</div>
