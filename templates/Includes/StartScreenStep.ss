<div class="self-assessment-start self-assessment-step">
	<% with $Top.Controller %>
		<% if $Image %>
			<div class="self-assessment-image">
				<img src="$Image.URL" alt="$Image.Title" />		
			</div>
		<% end_if %>
		<div class="self-assessment-start-content">
			<% if $EstimatedTime %>
				<p class="self-assessment-start-time">
					<svg class="timer-icon" width="14" height="16" xmlns="http://www.w3.org/2000/svg">
						<g fill-rule="nonzero" fill="#4D4D4D">
							<path d="M9.758 9.916H6.206V5.41H7.46v3.242h2.298zM5.725.168h2.194v1.263H5.725zM11.56 3.118l.887-.886 1.444 1.444-.886.886z"/>
							<path d="M6.833 15.832C3.155 15.832.146 12.8.146 9.095c0-3.706 3.01-6.737 6.687-6.737 3.677 0 6.686 3.031 6.686 6.737 0 3.705-3.009 6.737-6.686 6.737zm0-12.21C3.845 3.621 1.4 6.083 1.4 9.094c0 3.01 2.445 5.473 5.433 5.473s5.433-2.463 5.433-5.473S9.82 3.62 6.833 3.62z"/>
						</g>
					</svg>
					$EstimatedTime
				</p>
			<% end_if %>
			<% if $StartTitle %>
				<h2 class="self-assessment-start-title">$StartTitle</h2>
			<% end_if %>
			<% if $StartContent %>
				<div class="self-assessment-start-content">$StartContent</div>
			<% end_if %>
			<button type="button" class="self-assessment-button self-assessment-button--start" data-action="next">Get started</button>
		</div>		
	<% end_with %>
</div>
