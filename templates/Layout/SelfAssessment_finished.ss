<div class="self-assessment panel panel--spacing">

	<div class="self-assessment-results<% if $HasEmailedResults %> self-assessment--saved<% end_if %>">
		<div class="container">
			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-4-24"></div>
				<div class="pure-u-1 pure-u-md-16-24">
					<div class="pure-g">
						<div class="pure-u-md-1-2">
							<div class="self-assessment-results-content">
								$ResultIntro
								<button type="button" class="pure-button self-assessment-button" data-self-assessment-save-button data-self-assessment-title="$Title">Save my results</button>
							</div>
						</div>

						<div class="pure-u-md-1-2">
							<div class="self-assessment-results-image">
								<% if $Image %>
									<img src="$Image.URL" alt="$Image.Title" />
								<% else %>
									&nbsp;
								<% end_if %>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-4-24">&nbsp;</div>
				<div class="pure-u-1 pure-u-md-16-24">
					<% loop $ResultThemes %>
						<div class="self-assessment-card self-assessment-card--theme">
							<h2 class="self-assessment-theme-title">$Title</h2>

							<div class="self-assessment-theme-content">
								<% loop $AdviceForCurrentSubmission %>
									<div class="self-assessment-theme-advice">
										<div class="self-assessment-rating">
											<% include Rating Rating=$Rating %>
										</div>
										
										$Advice

									</div>
								<% end_loop %>
							</div>
						</div>
					<% end_loop %>
				</div>
			</div>

			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-4-24">&nbsp;</div>
				<div class="pure-u-1 pure-u-md-16-24">
					<div class="pure-g">
						<div class="pure-u-md-1-2">
							<div class="self-assessment-results-content">
								<button type="button" class="pure-button self-assessment-button" data-self-assessment-save-button data-self-assessment-title="$Title">Save my results</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	$ElementArea
</div>

<div id="self-assessment-email-signup-form" class="modal">
	<div class="self-assessment-results-modal-wrapper">
		<div class="container">
			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-16-24 pure-push-md-4-24 pure-u-lg-14-24 pure-push-lg-5-24">
				
					<div class="modal-dialog" role="document">

						<div class="modal-content">
							<button aria-controls="self-assessment-email-signup-form" class="modal-close" data-dismiss="modal">close</button>

							<div class="modal-header">
								<h3 class="modal-title">$EmailModalTitle</h3>
							</div>
							
							<div class="modal-body" tabindex="0">
								$EmailModalContent
								$EmailSignupForm
							</div>
							
							<div class="modal-footer">
								<a href="#" data-dismiss="modal">cancel</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="self-assessment-email-signup-form-reminder" class="modal">
	<div class="self-assessment-results-modal-wrapper">
		<div class="container">
			<div class="pure-g">
				<div class="pure-u-1 pure-u-md-16-24 pure-push-md-4-24 pure-u-lg-14-24 pure-push-lg-5-24">				
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<button aria-controls="self-assessment-email-signup-form" class="modal-close" data-dismiss="modal">close</button>

							<div class="modal-header">
								<h3 class="modal-title">$EmailReminderModalTitle</h3>
							</div>
							
							<div class="modal-body" tabindex="0">
								$EmailReminderModalContent
								$EmailSignupForm
							</div>
							
							<div class="modal-footer">
								<a href="#">continue</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
