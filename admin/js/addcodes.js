function addCode() 
{
	var table = document.getElementById("listscalebody");
	var newTR = table.insertRow(table.rows.length);
	var newTDord = newTR.insertCell(0);
	var newTDvalue = newTR.insertCell(1);
	var newTDlabel = newTR.insertCell(2);
	var newTDmissval = newTR.insertCell(3);
	var newTDdelete = newTR.insertCell(4);
	
	//create field for order
	var newINPUTcodeord = document.createElement("input");
	newINPUTcodeord.setAttribute("type","text");
	newINPUTcodeord.setAttribute("name","codeord[]");
	newINPUTcodeord.setAttribute("value",table.rows.length);
	newTDord.appendChild(newINPUTcodeord);
	
	//create hidden field with id
	var newINPUTcodeid = document.createElement("input");
	newINPUTcodeid.setAttribute("type","hidden");
	newINPUTcodeid.setAttribute("name","codeids[]");
	newINPUTcodeid.setAttribute("value","0");
	newTDord.appendChild(newINPUTcodeid);
	
	//create field with code value
	var newINPUTcodevalue = document.createElement("input");
	newINPUTcodevalue.setAttribute("type","text");
	newINPUTcodevalue.setAttribute("name","codevalue[]");
	newINPUTcodevalue.setAttribute("value",table.rows.length);
	newTDvalue.appendChild(newINPUTcodevalue);
	
	//create field with label
	var newINPUTcodelabel = document.createElement("input");
	newINPUTcodelabel.setAttribute("type","text");
	newINPUTcodelabel.setAttribute("name","codelabel[]");
	newINPUTcodelabel.setAttribute("size","128");
	newINPUTcodelabel.setAttribute("value","");
	newTDlabel.appendChild(newINPUTcodelabel);
}