DO.Subscribe(['app:ready', 'ajax:success'], function (e, $) {

  $(".ElementSelfAssessment").each(function () {

    var item = $(this),
      progress = 0,
      body = $('html, body'),
      header = $('header.header'),
      container = item.find('.self-assessment-container'),
      startBtn = item.find("[data-self-assessment-start-button]"),
      submitQuestionBtn = item.find("[data-self-assessment-submit-button]"),
      nextQuestionBtn = item.find("[data-self-assessment-next-button]"),
      form = item.find(".self-assessment-form"),
      formEl = form.find('form'),
      actions = item.find(".self-assessment-actions"),
      questions = item.find($('[id^="EditableTextField"]')),
      progressBar = item.find('progress'),
      isMobile = $('html').hasClass('touch');

    function init() {
      attachEvents();
    }

    function getOptions() {
      return questions
        .eq(progress)
        .find('[id^="UserForm_Form_EditableTextField"] input[type="radio"]');
    }

    function getTidbit() {
      return questions
        .eq(progress)
        .find('.self-assessment-tidbit');
    }

    function attachEvents() {
      startBtn.on("click", showForm);
      submitQuestionBtn.on("click", submitQuestion);
      nextQuestionBtn.on("click", nextQuestion);
      toggleSubmitQuestionBtn(true);
    }

    function showForm() {
      item
        .find('.self-assessment-start .self-assessment-card')
        .addClass('self-assessment-card--leaving');

        setTimeout(function () {
          item
            .find('.self-assessment-start')
            .hide()

          item
            .find('.self-assessment-form')
            .addClass('self-assessment-form--active');

          item
            .find('.self-assessment-container')
            .removeClass('.self-assessment-container--initial');

          updateScrollPosition();
          showQuestionCard(0);
        }, 600);
    }

    function toggleSubmitQuestionBtn(value) {
      submitQuestionBtn
        .attr("aria-disabled", value)
        .toggleClass("disabled", value);

      var options = getOptions();

      options.one("change", function () {
        submitQuestionBtn
          .attr("aria-disabled", false)
          .toggleClass("disabled", false);
      });
    }

    function submitQuestion(e) {
      var options = getOptions();

      questions
        .eq(progress)
        .find('.self-assessment-card')
        .addClass('self-assessment-card--leaving');

      updateProgressBar(progress + 2);

      var tidbit = getTidbit();

      if (tidbit.length > 0) {
        tidbit.addClass('self-assessment-tidbit--active');
        adjustTidbitHeight(tidbit);
      } else {
        nextQuestion();
      }

      updateScrollPosition();
    }

    function showQuestionCard(id) {
      questions
        .eq(id)
        .show()
        .find('.self-assessment-card')
        .removeClass('self-assessment-card--inactive');
    }

    function nextQuestion() {
      // Check if reached the end

      if (questions.length !== progress + 1) {
        var tidbit = getTidbit();
        tidbit.removeClass('self-assessment-tidbit--active');

        var nextCard = questions
          .eq(progress + 1)
          .find('.self-assessment-card');

        adjustQuestionHeight(nextCard);
        nextCard.removeClass('self-assessment-card--inactive');

        progress++;
        toggleSubmitQuestionBtn(true);
      } else {
        actions.removeClass('self-assessment-card--inactive');
      }
    }

    function adjustTidbitHeight(el) {
      formEl.height(el.find('.self-assessment-tidbit-wrapper').outerHeight());
    }

    // Always make <form> as tall as the card
    function adjustQuestionHeight(el) {
      formEl.height(el.outerHeight());
    }

    function updateProgressBar(value) {
      progressBar.attr('value', value);
    }

    function updateScrollPosition() {
      if (isMobile) {
        body.stop().animate({
          scrollTop: container.offset().top - header.outerHeight()
        }, 600);
      }
    }

    init();
  });

  /**
  * Results page modal logic
  */
  var assessmentResults = $('.self-assessment-results'),
      hasSeenModal = (assessmentResults.hasClass('self-assessment--saved') || Cookies.get("hasSeenModal"));

  $('[data-self-assessment-save-button]').on('click', function () {
    $('#self-assessment-email-signup-form').modal();
    setHasSeenModal();
  })

  $(".self-assessment-theme-advice").on('click', 'a', function (e) {

    if (!hasSeenModal) {
      e.preventDefault();

      $('#self-assessment-email-signup-form-reminder').find('a').attr('href', e.target.href);

      $('#self-assessment-email-signup-form-reminder').find('input[name="RedirectURL"]').val(e.target.href);

      $('#self-assessment-email-signup-form-reminder').modal();

      setHasSeenModal();
    }
    
  });

  function setHasSeenModal() {
    hasSeenModal = true;
    Cookies.set('hasSeenModal', true);
  }
});
