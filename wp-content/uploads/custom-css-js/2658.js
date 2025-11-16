<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
jQuery(document).ready(function($) {
  $(document).on('click', '#open-second-popup', function(e) {
    e.preventDefault();

    // Close Popup A
    elementorProFrontend.modules.popup.closePopup({ id: 2326 });

    // Open Popup B
    elementorProFrontend.modules.popup.showPopup({ id: 2323 });
  });
});
</script>
<!-- end Simple Custom CSS and JS -->
