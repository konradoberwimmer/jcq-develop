function addDisjunction() 
{
	var table = document.getElementById("filtertable");
	var cntdisjunctionselem = document.getElementById("cntdisjunctions");
	cntdisjunctionselem.setAttribute("value",cntdisjunctionselem.getAttribute("value")+1);
	var cntdisjunctions = cntdisjunctionselem.getAttribute("value");
	
	var parser = new DOMParser();
	var rowtoadd = "<tr><td>";
	if (cntdisjunctions>1) rowtoadd = rowtoadd + "AND";
	rowtoadd = rowtoadd + "</td><td>OR</td><td>VAR</td><td>OP</td><td>VAL</td></tr>";
	var newrow = parser.parseFromString(rowtoadd, "text/xml");
	table.appendChild(newrow.childNodes[0]);
	

}