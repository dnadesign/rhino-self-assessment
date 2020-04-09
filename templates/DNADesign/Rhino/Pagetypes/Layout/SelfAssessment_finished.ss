<div class="self-assessment-results">
    <div class="container">
        <h1 class="self-assessment-results-title">$ResultPageTitle</h1>
        <div class="self-assessment-results-content">$ResultIntro</div>

        <div class="self-assessment-results-themes">
            <% loop $ResultThemes %>
                <div class="self-assessment-theme">
                    <h2 class="self-assessment-theme-title">$Title</h2>
                    <div class="self-assessment-theme-content">
                        <% loop $AdviceForCurrentSubmission %>
                            <div class="self-assessment-theme-advice">
                                <div class="self-assessment-rating">
                                    <% include Rating Rating=$Rating %>
                                </div>
                                <p>$Question</p>
                                $Advice
                            </div>
                        <% end_loop %>
                    </div>
                </div>
            <% end_loop %>
        </div>

        <div class="self-assessment-results-form">
            <% if $EmailModalTitle %><h3 class="self-assessment-results-form-title">$EmailModalTitle</h3><% end_if %>
            <div class="self-assessment-results-form-content">$EmailModalContent</div>
            <% if $EmailSent %><p class="self-assessment-message">A link has been sent to your email address.</p><% end_if %>
            $EmailSignupForm
        </div>
    </div>
</div>
