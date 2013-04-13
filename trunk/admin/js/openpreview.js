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

function openPreview(root,projectID,sessionID)
{
	var url = root + "&tmpl=component&projectID=" + projectID + "&sessionID=" + sessionID;
	var win=window.open(url, '_blank');
	win.focus();
}