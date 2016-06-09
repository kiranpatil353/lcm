function location_validate(event,id) 
	{
		event.preventDefault();
		form = document.getElementById("delfrm"+id);
		form.submit();

	}