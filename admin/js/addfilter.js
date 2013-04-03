function addDisjunction() 
{
	var table = document.getElementById("filtertable");
	var cntdisjunctionselem = document.getElementById("cntdisjunctions");
	cntdisjunctionselem.setAttribute("value",Number(cntdisjunctionselem.getAttribute("value"))+1);
	
	var rowCount = table.rows.length;
    var row = table.insertRow(rowCount);
    row.setAttribute("style","border-top: 1px solid grey; border-bottom: 1px solid grey;");
	
    var cellOR = row.insertCell(0);
    if (rowCount>0) cellOR.innerHTML="OR<BR/>";
    
    var cellAND = row.insertCell(1);
    var tableAND = document.createElement("table");
    var tableANDrow = tableAND.insertRow(0);
    var cellANDAND = tableANDrow.insertCell(0);
    var cellANDVAR = tableANDrow.insertCell(1);
    cellANDVAR.innerHTML = "VAR";
    var cellANDOP = tableANDrow.insertCell(2);
    cellANDOP.innerHTML = "OP";
    var cellANDVAL = tableANDrow.insertCell(3);
    cellANDVAL.innerHTML = "VAL";
    tableANDrow.insertCell(4);
    cellAND.appendChild(tableAND);
    
    var cntconjugations = document.createElement("input");
    cntconjugations.type = "hidden";
    cntconjugations.setAttribute("id","cntconjugations"+rowCount);
    cntconjugations.setAttribute("name","cntconjugations"+rowCount);
    cntconjugations.setAttribute("value","1");
    cellAND.appendChild(cntconjugations);
    
    var addANDButton = document.createElement("input");
    addANDButton.type = "button";
    addANDButton.setAttribute("onclick","addConjugation("+rowCount+")");
    addANDButton.setAttribute("value","Add AND");
    cellAND.appendChild(addANDButton);
    
    var cellRemove = row.insertCell(2);
    var removeButton = document.createElement("input");
    removeButton.type = "button";
    removeButton.setAttribute("onclick","removeDisjunction("+rowCount+")");
    removeButton.setAttribute("value","Remove OR");
    cellRemove.appendChild(removeButton);
}

function removeDisjunction(number) 
{
	var table = document.getElementById("filtertable");
	var cntdisjunctionselem = document.getElementById("cntdisjunctions");
	cntdisjunctionselem.setAttribute("value",Number(cntdisjunctionselem.getAttribute("value"))-1);
	
	table.deleteRow(number);
	
	//delete the "AND" in the column if the first has been deleted and there are other ones
	if (number==0 && table.rows.length>0) table.rows[0].cells[0].innerHTML="";

	//reset the other buttons
	for(var i=0; i<table.rows.length; i++)
	{
		var row = table.rows[i];
		var removeButton = row.cells[2].getElementsByTagName("input")[0];
		removeButton.setAttribute("onclick","removeDisjunction("+i+")");
		var cellANDbuttons = row.cells[1].getElementsByTagName("input");
		for (var j=0; j<cellANDbuttons.length; j++)
		{
			if (cellANDbuttons[j].getAttribute("value")=="Add AND") cellANDbuttons[j].setAttribute("onclick","addConjugation("+i+")");
			if (cellANDbuttons[j].getAttribute("type")=="hidden")
			{ 
				cellANDbuttons[j].setAttribute("id","cntconjugations"+j);
				cellANDbuttons[j].setAttribute("name","cntconjugations"+j);
			}
		}
		var cellANDtable = row.cells[1].getElementsByTagName("table")[0];
		for (var j=1; j<cellANDtable.rows.length; j++)
		{
			var cellANDbutton = cellANDtable.rows[j].cells[4].getElementsByTagName("input")[0];
			cellANDbutton.setAttribute("onclick","removeConjugation("+i+","+j+")");
		}		
	}
}

function addConjugation(number)
{
	var table = document.getElementById("filtertable").rows[number].cells[1].getElementsByTagName("table")[0];
	var cntconjugationselem = document.getElementById("cntconjugations"+number);
	cntconjugationselem.setAttribute("value",Number(cntconjugationselem.getAttribute("value"))+1);
	
	var rowCount = table.rows.length;
    var row = table.insertRow(rowCount);
    
    var cellAND = row.insertCell(0);
    if (rowCount>0) cellAND.innerHTML="AND<BR/>";
    
    var cellVAR = row.insertCell(1);
    cellVAR.innerHTML = "VAR";
    var cellOP = row.insertCell(2);
    cellOP.innerHTML = "OP";
    var cellVAL = row.insertCell(3);
    cellVAL.innerHTML = "VAL";
    var cellRemove = row.insertCell(4);
    var removeANDButton = document.createElement("input");
    removeANDButton.type = "button";
    removeANDButton.setAttribute("onclick","removeConjugation("+number+","+rowCount+")");
    removeANDButton.setAttribute("value","Remove AND");
    cellRemove.appendChild(removeANDButton);
    
}

function removeConjugation(numberOR,numberAND)
{
	var table = document.getElementById("filtertable").rows[numberOR].cells[1].getElementsByTagName("table")[0];
	var cntconjugationselem = document.getElementById("cntconjugations"+numberOR);
	cntconjugationselem.setAttribute("value",Number(cntconjugationselem.getAttribute("value"))-1);
	
	table.deleteRow(numberAND);
	
	for(var i=1; i<table.rows.length; i++)
	{
		var row = table.rows[i];
		var removeButton = row.cells[4].getElementsByTagName("input")[0];
		removeButton.setAttribute("onclick","removeConjugation("+numberOR+","+i+")");
	}
}