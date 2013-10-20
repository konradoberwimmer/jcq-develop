function addConjugation(number)
{
	var table = document.getElementById("tableAND"+number);
	var cntconjugationselem = document.getElementById("cntconjugations"+number);
	cntconjugationselem.value = cntconjugationselem.value+1;
	
	var rowCount = table.rows.length;
	var row = table.insertRow(rowCount);
	row.setAttribute("id","tableAND"+number+"row"+cntconjugationselem.value)
	
    var cellAND = row.insertCell(0);
    if (rowCount>0) cellAND.innerHTML="AND";
    
    var cellVAR = row.insertCell(1);
    var varselector = document.getElementById("varidTEMPLATE").cloneNode(true);
    varselector.setAttribute("style","width: 250px;");
    varselector.setAttribute("id","variable"+number+"_"+cntconjugationselem.value);
    varselector.setAttribute("name","variable"+number+"_"+cntconjugationselem.value);
    cellVAR.appendChild(varselector);
    
    var cellOP = row.insertCell(2);
    var opselector = document.createElement("select");
    opselector.setAttribute("name","operator"+number+"_"+cntconjugationselem.value);
    for (var i=1; i<=6; i++)
    {
    	var option = document.createElement("option");
    	option.setAttribute("value",i);
    	if (i==1) option.innerHTML="==";
    	if (i==2) option.innerHTML="!=";
    	if (i==3) option.innerHTML="&lt;";
    	if (i==4) option.innerHTML="&lt;=";
    	if (i==5) option.innerHTML="&gt;=";
    	if (i==6) option.innerHTML="&gt;";
    	opselector.appendChild(option);
    }
    cellOP.appendChild(opselector);
    
    var cellVAL = row.insertCell(3);
    var valfield = document.createElement("input");
    valfield.setAttribute("type","text");
    valfield.setAttribute("size","8");
    valfield.setAttribute("name","val"+number+"_"+cntconjugationselem.value);
    cellVAL.appendChild(valfield);
    
    var cellRemove = row.insertCell(4);
    if (rowCount>0)
    {
    	var removeANDButton = document.createElement("input");
    	removeANDButton.type = "button";
    	removeANDButton.setAttribute("onclick","removeConjugation("+number+","+cntconjugationselem.value+")");
    	removeANDButton.setAttribute("value","Remove AND");
    	cellRemove.appendChild(removeANDButton);
	}
}

function removeConjugation(numberOR,numberAND)
{
	var table = document.getElementById("tableAND"+numberOR);
	var row = document.getElementById("tableAND"+numberOR+"row"+numberAND);
	table.removeChild(row);
}

function addDisjunction() 
{
	var table = document.getElementById("filtertable");
	var cntdisjunctionselem = document.getElementById("cntdisjunctions");
	cntdisjunctionselem.value = intval(cntdisjunctionselem.value)+1;
	
	var rowCount = table.rows.length;
	var row = table.insertRow(rowCount);
	row.setAttribute("id","tableORrow"+cntdisjunctionselem.value)
	row.setAttribute("style","border-top: 1px solid grey; border-bottom: 1px solid grey;");
	
    var cellOR = row.insertCell(0);
    if (rowCount>0) cellOR.innerHTML="OR<BR/>";
    
    var cellAND = row.insertCell(1);
    var tableAND = document.createElement("table");
    tableAND.setAttribute("id","tableAND"+cntdisjunctionselem.value)
    cellAND.appendChild(tableAND);
    
    var cntconjugations = document.createElement("input");
    cntconjugations.type = "hidden";
    cntconjugations.setAttribute("id","cntconjugations"+cntdisjunctionselem.value);
    cntconjugations.setAttribute("name","cntconjugations"+cntdisjunctionselem.value);
    cntconjugations.setAttribute("value","0");
    cellAND.appendChild(cntconjugations);
    
    var addANDButton = document.createElement("input");
    addANDButton.type = "button";
    addANDButton.setAttribute("onclick","addConjugation("+cntdisjunctionselem.value+")");
    addANDButton.setAttribute("value","Add AND");
    cellAND.appendChild(addANDButton);
    
    var cellRemove = row.insertCell(2);
    var removeButton = document.createElement("input");
    removeButton.type = "button";
    removeButton.setAttribute("onclick","removeDisjunction("+cntdisjunctionselem.value+")");
    removeButton.setAttribute("value","Remove OR");
    cellRemove.appendChild(removeButton);
    
    addConjugation(cntdisjunctionselem.value);
}

function removeDisjunction(number) 
{
	var table = document.getElementById("filtertable");
	var row = document.getElementById("tableORrow"+number);
	table.removeChild(row);
	if (table.rows.length>0) table.rows[0].cells[0].innerHTML="";
}
