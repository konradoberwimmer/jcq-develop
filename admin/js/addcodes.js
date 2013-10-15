function addCode() 
{
	var tmpcodeidfield = document.getElementById("tmpcodeid");
	var tmpcodeid = tmpcodeidfield.value;
	tmpcodeidfield.value = tmpcodeid-1;
	var scaleidfield = document.getElementById("scaleid");
	var scaleid = scaleidfield.value;
	
	var table = document.getElementById("listscalebody");
	var newTR = table.insertRow(table.rows.length);
	var newTDord = newTR.insertCell(0);
	var newTDvalue = newTR.insertCell(1);
	var newTDlabel = newTR.insertCell(2);
	var newTDmissval = newTR.insertCell(3);
	var newTDdelete = newTR.insertCell(4);
	var newTDaddrmtf = newTR.insertCell(5);
		
	//create field with temporary ID
	var newINPUTcodeid = document.createElement("input");
	newINPUTcodeid.setAttribute("type","hidden");
	newINPUTcodeid.setAttribute("name","_code_"+tmpcodeid+"_ID");
	newINPUTcodeid.setAttribute("value",tmpcodeid);
	newTDord.appendChild(newINPUTcodeid);
	
	//create field with scaleID
	var newINPUTcodescaleid = document.createElement("input");
	newINPUTcodescaleid.setAttribute("type","hidden");
	newINPUTcodescaleid.setAttribute("name","_code_"+tmpcodeid+"_scaleID");
	newINPUTcodescaleid.setAttribute("value",scaleid);
	newTDord.appendChild(newINPUTcodescaleid);
	
	//create field for order
	var newINPUTcodeord = document.createElement("input");
	newINPUTcodeord.setAttribute("class","orderfield");
	newINPUTcodeord.setAttribute("type","text");
	newINPUTcodeord.setAttribute("name","_code_"+tmpcodeid+"_ord");
	newINPUTcodeord.setAttribute("value",table.rows.length);
	newTDord.appendChild(newINPUTcodeord);

	//create field with code value
	var newINPUTcodevalue = document.createElement("input");
	newINPUTcodevalue.setAttribute("class","valuefield");
	newINPUTcodevalue.setAttribute("type","text");
	newINPUTcodevalue.setAttribute("name","_code_"+tmpcodeid+"_code");
	newINPUTcodevalue.setAttribute("value",table.rows.length);
	newTDvalue.appendChild(newINPUTcodevalue);
	
	//create field with label
	var newINPUTcodelabel = document.createElement("input");
	newINPUTcodelabel.setAttribute("type","text");
	newINPUTcodelabel.setAttribute("name","_code_"+tmpcodeid+"_label");
	newINPUTcodelabel.setAttribute("size","128");
	newINPUTcodelabel.setAttribute("value","");
	newTDlabel.appendChild(newINPUTcodelabel);
	
	//create field with missing value
	var newINPUTcodemissval = document.createElement("input");
	newINPUTcodemissval.setAttribute("type","checkbox");
	newINPUTcodemissval.setAttribute("name","_code_"+tmpcodeid+"_missval");
	newINPUTcodemissval.setAttribute("value","1");
	newTDmissval.appendChild(newINPUTcodemissval);
}