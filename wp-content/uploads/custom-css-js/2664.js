<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
/* Default comment here */ 

jQuery(document).on('click', 'a#myPopupBtn', function (e) {
  e.preventDefault();
// 	alert('aaa1');
  switchPopup(2326, 2323);
	
});


function switchPopup(fromId, toId) {
  if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules.popup) {

    // 1️⃣  Simulate an overlay click to close the first popup
    // Elementor adds a wrapper with class elementor-popup-modal and a data-id
      document.body.click();

    // 2️⃣  After a short delay, open the new popup
    setTimeout(function () {
      elementorProFrontend.modules.popup.showPopup({ id: toId });
    }, 300);

  } else {
    console.error('Elementor Popup module not found');
  }
}

jQuery(document).on('click', 'a#signInLink', function (e) {
  e.preventDefault();
// 	alert('aaa1');
  switchChange(2323, 2326);
	
});

function switchChange(fromId, toId) {
  if (typeof elementorProFrontend !== 'undefined' && elementorProFrontend.modules.popup) {

    // 1️⃣  Simulate an overlay click to close the first popup
    // Elementor adds a wrapper with class elementor-popup-modal and a data-id
      document.body.click();

    // 2️⃣  After a short delay, open the new popup
    setTimeout(function () {
      elementorProFrontend.modules.popup.showPopup({ id: toId });
    }, 300);

  } else {
    console.error('Elementor Popup module not found');
  }
}

jQuery(document).on('click', 'a#forgot-password-link', function (e) {
  e.preventDefault();
// 	alert('aaa1');
  switchPopup(2326, 2939);
	arguments
});






</script>
<!-- end Simple Custom CSS and JS -->
