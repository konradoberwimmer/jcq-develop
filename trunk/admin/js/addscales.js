function addScale() 
{
	var numpredefscalesfield = document.getElementById("numpredefscales");
	var numpredefscales = numpredefscalesfield.value;
	if (numpredefscales=="0")
	{
		alert('Error: JCQ component contains no predefined scales.');
		return false;
	}
	var tmpscaleidfield = document.getElementById("tmpscaleid");
	var tmpscaleid = tmpscaleidfield.value;
	tmpscaleidfield.value = tmpscaleid-1;
	
	var table = document.getElementById("listscalesbody");
	var newTR = table.insertRow(table.rows.length);
	var newTDord = newTR.insertCell(0);
	var newTDid = newTR.insertCell(1);
	var newTDmandatory = newTR.insertCell(2);
	var newTDdelete = newTR.insertCell(3);
	
	//create field for order
	var newINPUTscaleord = document.createElement("input");
	newINPUTscaleord.setAttribute("type","text");
	newINPUTscaleord.setAttribute("name","_scale_"+tmpscaleid+"_ord");
	newINPUTscaleord.setAttribute("value",table.rows.length);
	newINPUTscaleord.setAttribute("class","orderfield");
	newTDord.appendChild(newINPUTscaleord);
		
	//create selector with select box
	var newINPUTscaleselect = document.getElementById("scaleidTEMPLATE").cloneNode(true);
	newINPUTscaleselect.setAttribute("name","_scale_"+tmpscaleid+"_ID");
	newINPUTscaleselect.removeAttribute("ID");
	newINPUTscaleselect.removeAttribute("style");
	newTDid.appendChild(newINPUTscaleselect);

	//create field with mandatory
	var newINPUTscalemandatory = document.createElement("input");
	newINPUTscalemandatory.setAttribute("type","checkbox");
	newINPUTscalemandatory.setAttribute("name","_scale_"+tmpscaleid+"_mandatory");
	newINPUTscalemandatory.setAttribute("value",1);
	newTDmandatory.appendChild(newINPUTscalemandatory);
}