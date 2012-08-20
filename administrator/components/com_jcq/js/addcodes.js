function addCode() 
{
	var listscalebody = document.getElementById("listscalebody");
	var newTR = document.createElement("tr");
	var newTDord = document.createElement("td");
	var newTDvalue = document.createElement("td");
	var newTDlabel = document.createElement("td");
	var newTDmissval = document.createElement("td");
	var newTDdelete = document.createElement("td");
	//create field for order
	var newINPUTcodeord = document.createElement("input");
	var newINPUTcodeordTYPE = document.createAttribute("type");
	newINPUTcodeordTYPE.nodeValue = "text";
	newINPUTcodeord.setAttributeNode(newINPUTcodeordTYPE);
	var newINPUTcodeordNAME = document.createAttribute("name");
	newINPUTcodeordNAME.nodeValue = "codeord[]";
	newINPUTcodeord.setAttributeNode(newINPUTcodeordNAME);
	var newINPUTcodeordVALUE = document.createAttribute("value");
	//TODO find lowest available value > 0
	newINPUTcodeordVALUE.nodeValue = "0";
	newINPUTcodeord.setAttributeNode(newINPUTcodeordVALUE);
	//create hidden field with id
	var newINPUTcodeid = document.createElement("input");
	var newINPUTcodeidTYPE = document.createAttribute("type");
	newINPUTcodeidTYPE.nodeValue = "hidden";
	newINPUTcodeid.setAttributeNode(newINPUTcodeidTYPE);
	var newINPUTcodeidNAME = document.createAttribute("name");
	newINPUTcodeidNAME.nodeValue = "codeids[]";
	newINPUTcodeid.setAttributeNode(newINPUTcodeidNAME);
	var newINPUTcodeidVALUE = document.createAttribute("value");
	newINPUTcodeidVALUE.nodeValue = "0";
	newINPUTcodeid.setAttributeNode(newINPUTcodeidVALUE);
	//create field with value
	var newINPUTcodevalue = document.createElement("input");
	var newINPUTcodevalueTYPE = document.createAttribute("type");
	newINPUTcodevalueTYPE.nodeValue = "text";
	newINPUTcodevalue.setAttributeNode(newINPUTcodevalueTYPE);
	var newINPUTcodevalueNAME = document.createAttribute("name");
	newINPUTcodevalueNAME.nodeValue = "codevalue[]";
	newINPUTcodevalue.setAttributeNode(newINPUTcodevalueNAME);
	var newINPUTcodevalueVALUE = document.createAttribute("value");
	newINPUTcodevalueVALUE.nodeValue = "0";
	newINPUTcodevalue.setAttributeNode(newINPUTcodevalueVALUE);
	//create field with label
	var newINPUTcodelabel = document.createElement("input");
	var newINPUTcodelabelTYPE = document.createAttribute("type");
	newINPUTcodelabelTYPE.nodeValue = "text";
	newINPUTcodelabel.setAttributeNode(newINPUTcodelabelTYPE);
	var newINPUTcodelabelNAME = document.createAttribute("name");
	newINPUTcodelabelNAME.nodeValue = "codelabel[]";
	newINPUTcodelabel.setAttributeNode(newINPUTcodelabelNAME);
	var newINPUTcodelabelVALUE = document.createAttribute("value");
	newINPUTcodelabelVALUE.nodeValue = "";
	newINPUTcodelabel.setAttributeNode(newINPUTcodelabelVALUE);
	var newINPUTcodelabelSIZE = document.createAttribute("size");
	newINPUTcodelabelSIZE.nodeValue = "128";
	newINPUTcodelabel.setAttributeNode(newINPUTcodelabelSIZE);
	
	newTDord.appendChild(newINPUTcodeord);
	newTDord.appendChild(newINPUTcodeid);
	newTR.appendChild(newTDord);
	newTDvalue.appendChild(newINPUTcodevalue);
	newTR.appendChild(newTDvalue);
	newTDlabel.appendChild(newINPUTcodelabel);
	newTR.appendChild(newTDlabel);
	newTR.appendChild(newTDmissval);
	newTR.appendChild(newTDdelete);
	listscalebody.appendChild(newTR);
}