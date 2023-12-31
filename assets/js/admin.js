jQuery(document).ready(function($) {
  $('.wc-progress-bar-discount-add-threshold').click(function(e) {
      e.preventDefault();
      var newThreshold = '<div class="wc-progress-bar-discount-threshold">' +
          '<input type="number" name="wc_progress_bar_discount_thresholds_amount[]" value=""> € ' +
          '<input type="number" name="wc_progress_bar_discount_thresholds_discount[]" value=""> % Rabatt ' +
          '<input type="checkbox" name="wc_progress_bar_discount_thresholds_free_shipping[]"> Kostenloser Versand' +
          '</div>';
      $(this).before(newThreshold);
  });

  // Progress bar functionality
  document.addEventListener('DOMContentLoaded', (event) => {
    // Get the cart total
    const cartTotal = 75; // This should be dynamically updated based on your cart total

    // Calculate the progress percentage
    const progressPercentage = (cartTotal / 100) * 100;

    // Get the progress bar element
    const progressBar = document.querySelector('.progress-bar::before');

    // Update the width of the progress bar
    progressBar.style.width = `${progressPercentage}%`;

    // Create threshold circles
    const thresholds = [50, 75, 100];
    const progressBarElement = document.querySelector('.progress-bar');

    thresholds.forEach((threshold) => {
      const thresholdCircle = document.createElement('div');
      thresholdCircle.classList.add('threshold');
      thresholdCircle.style.left = `${threshold}%`;
      progressBarElement.appendChild(thresholdCircle);
    });
  });
});
