function addImportfile() 
{
	var table = document.getElementById("importstable");
	if (table.rows.length==0)
	{
		var header = table.insertRow(0);
		var headcellORD = document.createElement("th");
		headcellORD.innerHTML = "Order";
		header.appendChild(headcellORD);
		var headcellFILENAME = document.createElement("th");
		headcellFILENAME.innerHTML = "Filename";
		header.appendChild(headcellFILENAME);
		var headcellDELETE = document.createElement("th");
		headcellDELETE.innerHTML = "Delete";
		header.appendChild(headcellDELETE);
	}
	
	var rowCount = table.rows.length;
	var row = table.insertRow(rowCount);

	var cellID = row.insertCell(0);
	var inputID = document.createElement("input");
	inputID.setAttribute("type","hidden");
	inputID.setAttribute("name","importids[]");
	inputID.setAttribute("value","0");
	cellID.appendChild(inputID);
	var inputOrd = document.createElement("input");
	inputOrd.setAttribute("type","text");
	inputOrd.setAttribute("name","importord[]");
	inputOrd.setAttribute("value","0");
	cellID.appendChild(inputOrd);
	
	var cellFilename = row.insertCell(1);
	var inputFilename = document.createElement("input");
	inputFilename.setAttribute("type","text");
	inputFilename.setAttribute("name","importfilename[]");
	inputFilename.setAttribute("value","");
	cellFilename.appendChild(inputFilename);
	
	var cellDelete = row.insertCell(2);
	var cellEdit = row.insertCell(3);
}
