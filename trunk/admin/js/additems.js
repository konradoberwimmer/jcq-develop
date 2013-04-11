function addItem(addright) 
{
	var table = document.getElementById("listitembody");
	var newTR = table.insertRow(table.rows.length);
	var newTDord = newTR.insertCell(0);
	var newTDtextleft = newTR.insertCell(1);
	if (addright==true) var newTDtextright = newTR.insertCell(2);
	var newTDvarname = newTR.insertCell(newTR.cells.length);
	var newTDmandatory = newTR.insertCell(newTR.cells.length);
	var newTDdelete = newTR.insertCell(newTR.cells.length);
	
	//create field for order
	var newINPUTitemord = document.createElement("input");
	newINPUTitemord.setAttribute("type","text");
	newINPUTitemord.setAttribute("name","itemord[]");
	newINPUTitemord.setAttribute("value",table.rows.length);
	newTDord.appendChild(newINPUTitemord);
			
	//create hidden field with id
	var newINPUTitemid = document.createElement("input");
	newINPUTitemid.setAttribute("type","hidden");
	newINPUTitemid.setAttribute("name","itemids[]");
	newINPUTitemid.setAttribute("value","0");
	newTDord.appendChild(newINPUTitemid);
	
	//create field with textleft
	var newINPUTitemtextleft = document.createElement("input");
	newINPUTitemtextleft.setAttribute("type","text");
	newINPUTitemtextleft.setAttribute("name","itemtextleft[]");
	newINPUTitemtextleft.setAttribute("value","");
	if (addright!=true) newINPUTitemtextleft.setAttribute("size","128");
	else newINPUTitemtextleft.setAttribute("size","64");
	newTDtextleft.appendChild(newINPUTitemtextleft);
	
	if (addright==true)
	{
		//create field with textleft
		var newINPUTitemtextright = document.createElement("input");
		newINPUTitemtextright.setAttribute("type","text");
		newINPUTitemtextright.setAttribute("name","itemtextright[]");
		newINPUTitemtextright.setAttribute("value","");
		newINPUTitemtextright.setAttribute("size","64");
		newTDtextright.appendChild(newINPUTitemtextright);
	}
}