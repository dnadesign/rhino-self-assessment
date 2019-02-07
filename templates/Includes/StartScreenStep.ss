<div class="self-assessment-start">
    <div class="container">
        <div class="pure-g">
            <div class="pure-u-1 pure-u-md-16-24 pure-push-md-4-24">
                <div class="self-assessment-card">
                    <div class="self-assessment-start-wrapper">
                        <% if $Top.Controller.EstimatedTime %>
                            <p class="self-assessment-start-time">
                                $Top.Controller.EstimatedTime
                            </p>
                        <% end_if %>

                        <h1 class="self-assessment-start-title">
                            $Top.Controller.StartTitle
                        </h1>

                        <div class="self-assessment-start-content">
                            $Top.Controller.StartContent

                            <div class="self-assessment-start-image self-assessment-start-image--left">
                                <% if $Top.Controller.Image %>
                                    <% with $Top.Controller.Image %>
                                        <img src="$URL" alt="$Title" />
                                    <% end_with %>
                                <% end_if %>
                            </div>
                        </div>
                        
                        <button class="pure-button self-assessment-button self-assessment-start-button" data-self-assessment-start-button data-self-assessment-title="$Top.Controller.Title">Get started</button>
                    </div>


                    <div class="self-assessment-start-image self-assessment-start-image--right">
                        <% if $Top.Controller.Image %>
                            <% with $Top.Controller.Image %>
                                <img src="$URL" alt="$Title" />
                            <% end_with %>
                        <% end_if %>
                    </div>
                </div>
            </div>
        </div> 
    </div>
</div>