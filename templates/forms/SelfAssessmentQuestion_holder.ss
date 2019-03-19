<div id="$Name" class="field<% if $extraClass %> $extraClass<% end_if %>">
	<% if $Tidbit %>
		<div class="self-assessment-tidbit">
			<% include Progress Max=$TotalQuestionCount %>

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

				<button type="button" class="pure-button pure-button--primary self-assessment-button self-assessment-button--block" data-self-assessment-next-button data-self-assessment-title="$SelfAssessmentTitle">Next</button>
			</div>
		</div>
	<% end_if %>

	<div class="self-assessment-card-padding <% if $Tidbit %>hasTidBit<% end_if %>">
		<div class="self-assessment-card self-assessment-card--inactive pure-g">
			<% include Progress Max=$TotalQuestionCount %>

			<% if $Image %>
				<div class="pure-u-1">
					<div class="self-assessment-image">
						$Image
					</div>
				</div>
			<% end_if %>
			<div class="pure-u-1">
				<div class="pure-g self-assessment-card-wrapper" data-self-assessment-card-wrapper>

					<div class="pure-u-1 pure-u-lg-1-2">
						<h2 class="self-assessment-question-title">
							<span class="self-assessment-question-title--title">$Title</span>
						</h2>
					</div>

					<div class="pure-u-1 pure-u-lg-1-2">
						<div class="self-assessment-question-options">
							$Field

							<button type="button" class="pure-button pure-button--primary self-assessment-button self-assessment-button--block" data-self-assessment-submit-button>Submit</button>

							<button type="submit" class="pure-button pure-button--primary  self-assessment-button self-assessment-button--results" data-self-assessment-results-button data-self-assessment-title="$Top.Controller.Title">
								<% if $Top.Controller.SubmitButtonText %>
									$Top.Controller.SubmitButtonText
								<% else %>
									Show my results
								<% end_if %>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

