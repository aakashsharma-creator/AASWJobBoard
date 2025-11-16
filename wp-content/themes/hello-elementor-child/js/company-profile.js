jQuery(document).ready(function($) {
	
	//$('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').prop('disabled', true);
	
	var companyName = $('#company_profile').val();
	//console.log('company_name: '+companyName);
	
	$.ajax({
		url: companyAjax.ajax_url,
		type: "POST",
		data: {
			action: "get_company_data",
			company_name: companyName,
			security: companyAjax.nonce
		},
		success: function(response) {
			if (response.success) {
				$("input[name='_company_website']").val(response.data.website);
				$("input[name='_company_location']").val(response.data.address);
				$("input[name='_company_contact']").val(response.data.contact);
				$("input[name='_company_email']").val(response.data.email);
				$("textarea#_company_description").html(response.data.description);  
				//console.log(response.data.description);  
			}
		}
	});
	
    var $show_company_info = $("#show_company_info");

    var selectedVal = $show_company_info.val();
	
    if (selectedVal=='yes') {
        //console.log("Company already selected: " + selectedVal);
		$('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').show();
    }else{
		// Hide fields 
		$('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').hide();
	}

    // On change
    $("#show_company_info").on("change", function() {
        var entryId = $(this).val();
		//console.log('RE: '+entryId);
		
		if (entryId == 'yes') {
			$('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').show();
        } else {
			/*$("input[name='company_profile']").val("");
			$("input[name='_company_website']").val("");
			$("input[name='_company_location']").val("");
			$("input[name='_company_contact']").val("");
			$("input[name='_company_email']").val("");*/
			//console.log('No value set');
            // Reset + hide if none
            $('.fieldset-company_profile, .fieldset-_company_website, .fieldset-_company_location, .fieldset-_company_contact, .fieldset-_company_email, .fieldset-_company_description').hide();
        }
        
    });
});
