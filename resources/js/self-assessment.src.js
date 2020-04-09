document.addEventListener("DOMContentLoaded", function () {
  const assessments = document.querySelectorAll(".element-self-assessment");
  if (assessments.length < 1) return;
  assessments.forEach(function (assessment) {
    const steps = assessment.querySelectorAll(".self-assessment-step");
    // Activate first step
    if (steps.length > 0) {
      steps[0].classList.add("active");
    }
    // Activate assessment
    assessment.classList.add("initialised");
    // Set up listeners
    const nextButtons = assessment.querySelectorAll('[data-action="next"]');
    nextButtons.forEach(function (button) {
      button.addEventListener("click", function (e) {
        const currentStep = parseInt(
          assessment.getAttribute("data-active-step")
        );
        const nextStep = currentStep + 1;
        // Record next step on element
        assessment.setAttribute("data-active-step", nextStep);
        // Display next step
        steps[currentStep].classList.remove("active");
        steps[currentStep].classList.add("activated");
        steps[nextStep].classList.add("active");
        // Increase progress bar
        const progress = assessment.querySelector(
          ".self-assessment-progress progress"
        );
        if (progress) {
          progress.setAttribute("value", nextStep);
        }
      });
    });
  });
});
