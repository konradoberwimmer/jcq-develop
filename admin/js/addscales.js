function addScale() 
{
	var listscalebody = document.getElementById("listscalesbody");
	var newTR = document.createElement("tr");
	var newTDord = document.createElement("td");
	var newTDid = document.createElement("td");
	var newTDdelete = document.createElement("td");
	
	//create field for order
	var newINPUTscaleord = document.createElement("input");
	var newINPUTscaleordTYPE = document.createAttribute("type");
	newINPUTscaleordTYPE.nodeValue = "text";
	newINPUTscaleord.setAttributeNode(newINPUTscaleordTYPE);
	var newINPUTscaleordNAME = document.createAttribute("name");
	newINPUTscaleordNAME.nodeValue = "scaleord[]";
	newINPUTscaleord.setAttributeNode(newINPUTscaleordNAME);
	var newINPUTscaleordVALUE = document.createAttribute("value");
	//TODO find lowest available value > 0
	newINPUTscaleordVALUE.nodeValue = "0";
	newINPUTscaleord.setAttributeNode(newINPUTscaleordVALUE);
	
	//create selector with ids
	var newINPUTscaleid = document.getElementById("scaleidTEMPLATE").cloneNode(true);
	var newINPUTscaleidNAME = document.createAttribute("name");
	newINPUTscaleidNAME.nodeValue = "scaleids[]";
	newINPUTscaleid.setAttributeNode(newINPUTscaleidNAME);
	newINPUTscaleid.removeAttribute("ID");
	newINPUTscaleid.removeAttribute("style");
	
	newTDord.appendChild(newINPUTscaleord);
	newTR.appendChild(newTDord);
	newTDid.appendChild(newINPUTscaleid);
	newTR.appendChild(newTDid);
	newTR.appendChild(newTDdelete);
	listscalebody.appendChild(newTR);
}