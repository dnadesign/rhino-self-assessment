<div class="self-assessment-start">

	<div class="self-assessment-start-image self-assessment-start-image--left">
		<% if $Top.Controller.Image %>
			<% with $Top.Controller %>
				<img src="$Image.Fill(680, 260).URL" alt="$Title" />
			<% end_with %>
		<% end_if %>
	</div>

	<div class="self-assessment-card">

		<div class="self-assessment-start-wrapper">
			<div class="self-assessment-start-content-wrapper">
				<% if $Top.Controller.EstimatedTime %>
					<p class="self-assessment-start-time">
						$Top.Controller.EstimatedTime
					</p>
				<% end_if %>

				<h2 class="self-assessment-start-title">$Top.Controller.StartTitle</h2>

				<div class="self-assessment-start-content">
					$Top.Controller.StartContent
				</div>
			</div>

			<button class="pure-button pure-button--primary self-assessment-start-button" data-self-assessment-start-button data-self-assessment-title="$Top.Controller.Title">Get started</button>
		</div>

		<div class="self-assessment-start-image" <% if $Top.Controller.Image %><% with $Top.Controller.Image %>style="background-image: url('{$URL}')"<% end_with %><% end_if %>></div>
	</div>
</div>
