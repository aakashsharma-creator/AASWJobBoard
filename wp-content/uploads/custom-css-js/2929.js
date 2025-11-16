<!-- start Simple Custom CSS and JS -->
<script type="text/javascript">
/* Default comment here */ 


document.addEventListener("click", function(e) {
  if (e.target.classList.contains("open-login-popup")) {
    e.preventDefault();
	  alert('aaa');
    elementorProFrontend.modules.popup.showPopup({ id: 2323 });
  }
});
</script>
<!-- end Simple Custom CSS and JS -->
