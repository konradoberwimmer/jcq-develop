function addItem(addright) 
{
	var tmpitemidfield = document.getElementById("tmpitemid");
	var tmpitemid = tmpitemidfield.value;
	tmpitemidfield.value = tmpitemid-1;
	var questionidfield = document.getElementById("questionid");
	var questionid = questionidfield.value;
	
	var table = document.getElementById("listitembody");
	var newTR = table.insertRow(table.rows.length);
	var newTDord = newTR.insertCell(0);
	var newTDtextleft = newTR.insertCell(1);
	if (addright==true) var newTDtextright = newTR.insertCell(2);
	var newTDvarname = newTR.insertCell(newTR.cells.length);
	var newTDmandatory = newTR.insertCell(newTR.cells.length);
	var newTDdelete = newTR.insertCell(newTR.cells.length);
	
	//create hidden field with id
	var newINPUTitemid = document.createElement("input");
	newINPUTitemid.setAttribute("type","hidden");
	newINPUTitemid.setAttribute("name","_item_"+tmpitemid+"_ID");
	newINPUTitemid.setAttribute("value",tmpitemid);
	newTDord.appendChild(newINPUTitemid);
	
	//create hidden field with questionid
	var newINPUTitemquestionid = document.createElement("input");
	newINPUTitemquestionid.setAttribute("type","hidden");
	newINPUTitemquestionid.setAttribute("name","_item_"+tmpitemid+"_questionID");
	newINPUTitemquestionid.setAttribute("value",questionid);
	newTDord.appendChild(newINPUTitemquestionid);
	
	//create hidden field with datatype
	var newINPUTitemdatatype = document.createElement("input");
	newINPUTitemdatatype.setAttribute("type","hidden");
	newINPUTitemdatatype.setAttribute("name","_item_"+tmpitemid+"_datatype");
	newINPUTitemdatatype.setAttribute("value",1);
	newTDord.appendChild(newINPUTitemdatatype);
	
	//create field for order
	var newINPUTitemord = document.createElement("input");
	newINPUTitemord.setAttribute("class","orderfield");
	newINPUTitemord.setAttribute("type","text");
	newINPUTitemord.setAttribute("name","_item_"+tmpitemid+"_ord");
	newINPUTitemord.setAttribute("value",table.rows.length);
	newTDord.appendChild(newINPUTitemord);
			
	//create field with textleft
	var newINPUTitemtextleft = document.createElement("input");
	newINPUTitemtextleft.setAttribute("type","text");
	newINPUTitemtextleft.setAttribute("name","_item_"+tmpitemid+"_textleft");
	newINPUTitemtextleft.setAttribute("value","");
	if (addright!=true) newINPUTitemtextleft.setAttribute("size","128");
	else newINPUTitemtextleft.setAttribute("size","64");
	newTDtextleft.appendChild(newINPUTitemtextleft);
	
	if (addright==true)
	{
		//create field with textleft
		var newINPUTitemtextright = document.createElement("input");
		newINPUTitemtextright.setAttribute("type","text");
		newINPUTitemtextright.setAttribute("name","_item_"+tmpitemid+"_textright");
		newINPUTitemtextright.setAttribute("value","");
		newINPUTitemtextright.setAttribute("size","64");
		newTDtextright.appendChild(newINPUTitemtextright);
	}
	
	//create field with varname
	var newINPUTitemvarname = document.createElement("input");
	newINPUTitemvarname.setAttribute("type","text");
	newINPUTitemvarname.setAttribute("name","_item_"+tmpitemid+"_varname");
	newINPUTitemvarname.setAttribute("value","");
	newTDvarname.appendChild(newINPUTitemvarname);
}