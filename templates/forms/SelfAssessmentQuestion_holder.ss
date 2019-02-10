<div id="$Name" class="field<% if $extraClass %> $extraClass<% end_if %>">
	<% if $Tidbit %>
		<div class="self-assessment-tidbit">
			<div class="self-assessment-tidbit-wrapper">
				<% if $ResultTheme %>
					<h3 class="self-assessment-tidbit-title">$ResultTheme.Title</h3>
				<% end_if %>
				
				<% if $TidbitImage %>
					<% with $TidbitImage %>
						<div class="self-assessment-tidbit-image">
							<img src="$URL" alt="$Title" />
						</div>
					<% end_with %>
				<% end_if %>				
				<div class="self-assessment-tidbit-content">
					<% if $TidbitTitle %><h3>$TidbitTitle</h3><% end_if %>
					$Tidbit
				</div>
				
				<button type="button" class="pure-button self-assessment-tidbit-button" data-self-assessment-next-button data-self-assessment-title="$SelfAssessmentTitle">Next</button>
			</div>
		</div>
	<% end_if %>

	<div class="pure-g">
		<div class="pure-u-1 pure-push-md-4-24 pure-u-md-16-24">
			<div class="self-assessment-card self-assessment-card--inactive">
				<div class="pure-g">
					<div class="pure-u-1 pure-u-lg-1-2">
						<h2 class="self-assessment-question-title">
							$Title
						</h2>
					</div>
					
					<div class="pure-u-1 pure-u-lg-1-2">

						<div class="self-assessment-image">
							$Image
						</div>

						<div class="self-assessment-question-options">
							$Field
							<button type="button" class="pure-button self-assessment-button self-assessment-button--block" data-self-assessment-submit-button>Submit</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

