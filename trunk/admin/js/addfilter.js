function lowerAttrib(attrib,idprefix,from,to,textbefore,textafter)
{
	for (var i=from; i<=to; i++)
	{
		var elem = document.getElementById(idprefix+i);
		elem.setAttribute(attrib,textbefore+(i-1)+textafter);
	}
}

function addConjugation(number)
{
	var table = document.getElementById("filtertable").rows[number].cells[1].getElementsByTagName("table")[0];
	var cntconjugationselem = document.getElementById("cntconjugations"+number);
	
	var rowCount = table.rows.length;
	var row = table.insertRow(rowCount);
	cntconjugationselem.setAttribute("value",table.rows.length);
	
    var cellAND = row.insertCell(0);
    if (rowCount>0) cellAND.innerHTML="AND";
    
    var cellVAR = row.insertCell(1);
    var varselector = document.getElementById("varidTEMPLATE").cloneNode(true);
    varselector.setAttribute("style","");
    varselector.setAttribute("id","variable"+number+"_"+rowCount);
    varselector.setAttribute("name","variable"+number+"_"+rowCount);
    cellVAR.appendChild(varselector);
    
    var cellOP = row.insertCell(2);
    var opselector = document.createElement("select");
    opselector.setAttribute("id","operator"+number+"_"+rowCount);
    opselector.setAttribute("name","operator"+number+"_"+rowCount);
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
    valfield.setAttribute("id","val"+number+"_"+rowCount);
    valfield.setAttribute("name","val"+number+"_"+rowCount);
    cellVAL.appendChild(valfield);
    
    var cellRemove = row.insertCell(4);
    if (rowCount>0)
    {
    	var removeANDButton = document.createElement("input");
    	removeANDButton.type = "button";
    	removeANDButton.setAttribute("id","removeANDbutton"+number+"_"+rowCount);
    	removeANDButton.setAttribute("onclick","removeConjugation("+number+","+rowCount+")");
    	removeANDButton.setAttribute("value","Remove AND");
    	cellRemove.appendChild(removeANDButton);
	}
}

function removeConjugation(numberOR,numberAND)
{
	var table = document.getElementById("filtertable").rows[numberOR].cells[1].getElementsByTagName("table")[0];
	var cntconjugationselem = document.getElementById("cntconjugations"+numberOR);
	
	table.deleteRow(numberAND);
	cntconjugationselem.setAttribute("value",table.rows.length);
	
	lowerAttrib("name","variable"+numberOR+"_",numberAND+1,table.rows.length,"variable"+numberOR+"_","");
	lowerAttrib("id","variable"+numberOR+"_",numberAND+1,table.rows.length,"variable"+numberOR+"_","");
	lowerAttrib("name","operator"+numberOR+"_",numberAND+1,table.rows.length,"operator"+numberOR+"_","");
	lowerAttrib("id","operator"+numberOR+"_",numberAND+1,table.rows.length,"operator"+numberOR+"_","");
	lowerAttrib("name","val"+numberOR+"_",numberAND+1,table.rows.length,"val"+numberOR+"_","");
	lowerAttrib("id","val"+numberOR+"_",numberAND+1,table.rows.length,"val"+numberOR+"_","");
	lowerAttrib("onclick","removeANDbutton"+numberOR+"_",numberAND+1,table.rows.length,"removeConjugation("+numberOR+",",")");
	lowerAttrib("id","removeANDbutton"+numberOR+"_",numberAND+1,table.rows.length,"removeConjugation"+numberOR+"_","");
}

function addDisjunction() 
{
	var table = document.getElementById("filtertable");
	var cntdisjunctionselem = document.getElementById("cntdisjunctions");
	
	var rowCount = table.rows.length;
	var row = table.insertRow(rowCount);
	cntdisjunctionselem.setAttribute("value",table.rows.length);
	row.setAttribute("style","border-top: 1px solid grey; border-bottom: 1px solid grey;");
	
    var cellOR = row.insertCell(0);
    if (rowCount>0) cellOR.innerHTML="OR<BR/>";
    
    var cellAND = row.insertCell(1);
    var tableAND = document.createElement("table");
    cellAND.appendChild(tableAND);
    
    var cntconjugations = document.createElement("input");
    cntconjugations.type = "hidden";
    cntconjugations.setAttribute("id","cntconjugations"+rowCount);
    cntconjugations.setAttribute("name","cntconjugations"+rowCount);
    cntconjugations.setAttribute("value","0");
    cellAND.appendChild(cntconjugations);
    
    var addANDButton = document.createElement("input");
    addANDButton.type = "button";
    addANDButton.setAttribute("id","addANDbutton"+rowCount);
    addANDButton.setAttribute("onclick","addConjugation("+rowCount+")");
    addANDButton.setAttribute("value","Add AND");
    cellAND.appendChild(addANDButton);
    
    var cellRemove = row.insertCell(2);
    var removeButton = document.createElement("input");
    removeButton.type = "button";
    removeButton.setAttribute("id","removeORbutton"+rowCount);
    removeButton.setAttribute("onclick","removeDisjunction("+rowCount+")");
    removeButton.setAttribute("value","Remove OR");
    cellRemove.appendChild(removeButton);
    
    addConjugation(rowCount);
}

function removeDisjunction(number) 
{
	var table = document.getElementById("filtertable");
	var cntdisjunctionselem = document.getElementById("cntdisjunctions");
	
	//delete
	table.deleteRow(number);
	cntdisjunctionselem.setAttribute("value",table.rows.length);
	
	//reset the other elements
	for (var i=number; i<table.rows.length; i++)
	{
		var row=table.rows[i];
		var innertable=row.getElementsByTagName("table")[0];
		for (var j=0; j<innertable.rows.length; j++)
		{
			var varselector = document.getElementById("variable"+(i+1)+"_"+j);
			varselector.setAttribute("name","variable"+i+"_"+j);
			varselector.setAttribute("id","variable"+i+"_"+j);
			var operator = document.getElementById("operator"+(i+1)+"_"+j);
			operator.setAttribute("name","operator"+i+"_"+j);
			operator.setAttribute("id","operator"+i+"_"+j);
			var valfield = document.getElementById("val"+(i+1)+"_"+j);
			valfield.setAttribute("name","val"+i+"_"+j);
			valfield.setAttribute("id","val"+i+"_"+j);
			if (j>0)
			{
				var removeANDbutton = document.getElementById("removeANDbutton"+(i+1)+"_"+j);
				removeANDbutton.setAttribute("onclick","removeConjugation("+i+","+j+")");
				removeANDbutton.setAttribute("id","removeANDbutton"+i+"_"+j);
			}
		}
	}
	lowerAttrib("name","cntconjugations",number+1,table.rows.length,"cntconjugations","");
	lowerAttrib("id","cntconjugations",number+1,table.rows.length,"cntconjugations","");
	lowerAttrib("onclick","addANDbutton",number+1,table.rows.length,"addConjugation(",")");
	lowerAttrib("id","addANDbutton",number+1,table.rows.length,"addANDbutton","");
	lowerAttrib("onclick","removeORbutton",number+1,table.rows.length,"removeDisjunction(",")");
	lowerAttrib("id","removeORbutton",number+1,table.rows.length,"removeORbutton","");
	
	//delete the "AND" in the column if the first has been deleted and there are other ones
	if (number==0 && table.rows.length>0) table.rows[0].cells[0].innerHTML="";
}
