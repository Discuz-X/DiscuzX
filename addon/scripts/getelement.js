//UTF-8字符
///////////////////////////////常用/////////////////////////////////////////////
//缩简常用页面元素检索函数的长度
function EleId(id,element){
	if(element==undefined){
		element=document;
	}
	return element.getElementById(id);
};
function EleTN(tagName,element){
	if(element==undefined){
		element=document;
	}
	return element.getElementsByTagName(tagName);
};
function EleN(name,element){
	if(element==undefined){
		element=document;
	}
	return element.getElementsByName(name);
};
function _insertBefore(newNode, node) {
	return node.parentNode.insertBefore(newNode, node);
};
function _appendChild(child, parent) {
	return parent.appendChild(child);
};
function _prependChild(child, parent) {
	if(parent.firstChild)
		return _insertBefore(child, parent.firstChild);
	else 
		return _appendChild(child, parent);
};
function _replaceChild(otherChild, originalChild) {
	try{
		return originalChild.parentNode.replaceChild(otherChild, originalChild);
	}catch(e) {
		alert(e.message);
	}
};
function _removeChild(child) {
	return child.parentNode.removeChild(child);
};
