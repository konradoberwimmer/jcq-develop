function addItem(addright) 
{
	var listscalebody = document.getElementById("listitembody");
	var newTR = document.createElement("tr");
	var newTDord = document.createElement("td");
	var newTDtextleft = document.createElement("td");
	if (addright==true) var newTDtextright = document.createElement("td");
	var newTDvarname = document.createElement("td");
	var newTDmandatory = document.createElement("td");
	var newTDdelete = document.createElement("td");
	//create field for order
	var newINPUTitemord = document.createElement("input");
	var newINPUTitemordTYPE = document.createAttribute("type");
	newINPUTitemordTYPE.nodeValue = "text";
	newINPUTitemord.setAttributeNode(newINPUTitemordTYPE);
	var newINPUTitemordNAME = document.createAttribute("name");
	newINPUTitemordNAME.nodeValue = "itemord[]";
	newINPUTitemord.setAttributeNode(newINPUTitemordNAME);
	var newINPUTitemordVALUE = document.createAttribute("value");
	//TODO find lowest available value > 0
	newINPUTitemordVALUE.nodeValue = "0";
	newINPUTitemord.setAttributeNode(newINPUTitemordVALUE);
	//create hidden field with id
	var newINPUTitemid = document.createElement("input");
	var newINPUTitemidTYPE = document.createAttribute("type");
	newINPUTitemidTYPE.nodeValue = "hidden";
	newINPUTitemid.setAttributeNode(newINPUTitemidTYPE);
	var newINPUTitemidNAME = document.createAttribute("name");
	newINPUTitemidNAME.nodeValue = "itemids[]";
	newINPUTitemid.setAttributeNode(newINPUTitemidNAME);
	var newINPUTitemidVALUE = document.createAttribute("value");
	newINPUTitemidVALUE.nodeValue = "0";
	newINPUTitemid.setAttributeNode(newINPUTitemidVALUE);
	//create field with textleft
	var newINPUTitemtextleft = document.createElement("input");
	var newINPUTitemtextleftTYPE = document.createAttribute("type");
	newINPUTitemtextleftTYPE.nodeValue = "text";
	newINPUTitemtextleft.setAttributeNode(newINPUTitemtextleftTYPE);
	var newINPUTitemtextleftNAME = document.createAttribute("name");
	newINPUTitemtextleftNAME.nodeValue = "itemtextleft[]";
	newINPUTitemtextleft.setAttributeNode(newINPUTitemtextleftNAME);
	var newINPUTitemtextleftVALUE = document.createAttribute("value");
	newINPUTitemtextleftVALUE.nodeValue = "";
	newINPUTitemtextleft.setAttributeNode(newINPUTitemtextleftVALUE);
	var newINPUTitemtextleftSIZE = document.createAttribute("size");
	if (addright!=true) newINPUTitemtextleftSIZE.nodeValue = "128";
	else newINPUTitemtextleftSIZE.nodeValue = "64";
	newINPUTitemtextleft.setAttributeNode(newINPUTitemtextleftSIZE);
	if (addright==true)
		{
		//create field with textleft
		var newINPUTitemtextright = document.createElement("input");
		var newINPUTitemtextrightTYPE = document.createAttribute("type");
		newINPUTitemtextrightTYPE.nodeValue = "text";
		newINPUTitemtextright.setAttributeNode(newINPUTitemtextrightTYPE);
		var newINPUTitemtextrightNAME = document.createAttribute("name");
		newINPUTitemtextrightNAME.nodeValue = "itemtextright[]";
		newINPUTitemtextright.setAttributeNode(newINPUTitemtextrightNAME);
		var newINPUTitemtextrightVALUE = document.createAttribute("value");
		newINPUTitemtextrightVALUE.nodeValue = "";
		newINPUTitemtextright.setAttributeNode(newINPUTitemtextrightVALUE);
		var newINPUTitemtextrightSIZE = document.createAttribute("size");
		newINPUTitemtextrightSIZE.nodeValue = "64";
		newINPUTitemtextright.setAttributeNode(newINPUTitemtextrightSIZE);
		}
	
	newTDord.appendChild(newINPUTitemord);
	newTDord.appendChild(newINPUTitemid);
	newTR.appendChild(newTDord);
	newTDtextleft.appendChild(newINPUTitemtextleft);
	if (addright==true) newTDtextright.appendChild(newINPUTitemtextright);
	newTR.appendChild(newTDtextleft);
	if (addright==true) newTR.appendChild(newTDtextright);
	newTR.appendChild(newTDvarname);
	newTR.appendChild(newTDmandatory);
	newTR.appendChild(newTDdelete);
	listscalebody.appendChild(newTR);
}