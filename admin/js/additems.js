function addItem() 
{
	var listscalebody = document.getElementById("listitembody");
	var newTR = document.createElement("tr");
	var newTDord = document.createElement("td");
	var newTDtextleft = document.createElement("td");
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
	newINPUTitemtextleftSIZE.nodeValue = "128";
	newINPUTitemtextleft.setAttributeNode(newINPUTitemtextleftSIZE);
	
	newTDord.appendChild(newINPUTitemord);
	newTDord.appendChild(newINPUTitemid);
	newTR.appendChild(newTDord);
	newTDtextleft.appendChild(newINPUTitemtextleft);
	newTR.appendChild(newTDtextleft);
	newTR.appendChild(newTDvarname);
	newTR.appendChild(newTDmandatory);
	newTR.appendChild(newTDdelete);
	listscalebody.appendChild(newTR);
}