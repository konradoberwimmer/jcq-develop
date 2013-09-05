function changeAllTokenstate() 
{
	var table = document.getElementById("tokentable");
	var chkboxes = table.getElementsByTagName("input");
	var allchkbox = document.getElementById("token_all");
	for (var i=0; i<chkboxes.length; i++)
	{
		if (chkboxes[i].getAttribute('type')=='checkbox' && chkboxes[i].getAttribute('name')!='token_all') chkboxes[i].checked=allchkbox.checked;
	}
}