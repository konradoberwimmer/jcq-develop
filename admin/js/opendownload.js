function addOnload(onloadFunc) {
  if(this.addEventListener)
  {
    this.addEventListener("load", onloadFunc, false);
  } else if(this.attachEvent) 
  {
    this.attachEvent("onload", onloadFunc);
  } else 
  {
    var onloadOld = this.onload;
    this.onload = function() { onloadOld(); onloadFunc(); }
  }
}

function openDownload(root,filename)
{
	var url = root + "/senddl.php?datafile=" + filename;
	window.open(url, '_blank');
}