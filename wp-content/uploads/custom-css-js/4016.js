<!-- start Simple Custom CSS and JS -->
 

<script>
document.addEventListener("DOMContentLoaded", function() {
  // Wait until the first script runs
  setTimeout(() => {
    const swiperEl = document.querySelector(".job-cards-container").swiper;
    if (swiperEl) {
      // Example: change slide speed or snap behavior
      swiperEl.params.speed = 500;
      swiperEl.params.loop = false;
      swiperEl.update();
    }
  }, 1000);
});
</script><!-- end Simple Custom CSS and JS -->
