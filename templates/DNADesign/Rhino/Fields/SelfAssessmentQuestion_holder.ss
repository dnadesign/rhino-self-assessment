<div class="self-assessment-question self-assessment-step">
	<div class="self-assessment-question-wrapper">
		<% if $Image %>
			<div class="self-assessment-image">
				$Image
			</div>
		<% end_if %>
		<h2 class="self-assessment-question-title">$Title</h2>
		<div class="self-assessment-question-options">
			$Field
			<% if $Last && not $Tidbit %>
				<button type="submit" class="self-assessment-button self-assessment-button--results" data-action="submit">
					<% if $Top.Controller.SubmitButtonText %>
						$Top.Controller.SubmitButtonText
					<% else %>
						Show my results
					<% end_if %>
				</button>
			<% else %>
				<button type="button" class="self-assessment-button self-assessment-button--next" data-action="next">Submit</button>
			<% end_if %>
		</div>
	</div>
</div>

<% if $HasTidbit %>
	<div class="self-assessment-tidbit self-assessment-step">
		<div class="self-assessment-tidbit-wrapper">
			<% if $TidbitImage %>
				<div class="self-assessment-image">
					$TidbitImage
				</div>
			<% end_if %>

			<% if $ResultTheme %>
				<h3 class="self-assessment-tidbit-title">$ResultTheme.Title</h3>
			<% end_if %>

			<div class="self-assessment-tidbit-content">
				<% if $TidbitTitle %><h3>$TidbitTitle</h3><% end_if %>
				$Tidbit
			</div>						
			
			<% if not $Last %>
				<button type="button" class="self-assessment-button self-assessment-button--next" data-action="next">Next</button>
			<% else %>
				<button type="submit" class="self-assessment-button self-assessment-button--results" data-action="submit">
					<% if $Top.Controller.SubmitButtonText %>
						$Top.Controller.SubmitButtonText
					<% else %>
						Show my results
					<% end_if %>
				</button>
			<% end_if %>
		</div>
	</div>
<% end_if %>
