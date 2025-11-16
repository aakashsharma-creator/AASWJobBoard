<!-- start Simple Custom CSS and JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find the logout menu link
    const logoutLink = document.querySelector('a[href="#logout-popup"]');
    if (logoutLink && typeof elementorPro !== 'undefined') {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            elementorPro.frontend.modules.popup.showPopup({ id: 4567 }); // Replace 4567 with your popup ID
        });
    }
});
</script>
<!-- end Simple Custom CSS and JS -->
