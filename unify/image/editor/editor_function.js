function uploadEdit(obj) {
	mainForm = obj.form;
	forms = $('attachbody').getElementsByTagName("FORM");
	albumid = $('uploadalbum').value;
	edit_save();
	upload();
}

//提交保存
function edit_save() {
	var p = window.frames['uchome-ifrHtmlEditor'];
	var obj = p.window.frames['HtmlEditor'];
	var status = p.document.getElementById('uchome-editstatus').value;
	if(status == 'code') {
		$('uchome-ttHtmlEditor').value = p.document.getElementById('sourceEditor').value;
	} else if(status == 'text') {
		if(is_ie) {
			obj.document.body.innerText = p.document.getElementById('dvtext').value;
			$('uchome-ttHtmlEditor').value = obj.document.body.innerHTML;
		} else {
			obj.document.body.textContent = p.document.getElementById('dvtext').value;
			var sOutText = obj.document.body.innerHTML;
			$('uchome-ttHtmlEditor').value = sOutText.replace(/\r\n|\n/g,"<br>");
		}
	} else {
		$('uchome-ttHtmlEditor').value = obj.document.body.innerHTML;
	}
	backupContent($('uchome-ttHtmlEditor').value);
}

//获取关键字
function relatekw() {
	edit_save();
	var subject = cnCode($('subject').value);
	var message = cnCode($('uchome-ttHtmlEditor').value);
	if(message) {
		message = message.substr(0, 500);
		message = message.replace(/&/ig, '', message);
	}
	var x = new Ajax();
	x.get('cp.php?ac=relatekw&subjectenc=' + subject + '&messageenc=' + message, function(s){
		$('tag').value = s;
	});
}
	
function backupContent(sHTML) {
	if (sHTML.length > 11) {
		var oArea = $('uchome-ttHtmlEditor');
		try {
			var xmlDoc = oArea.XMLDocument;
			var subNode = xmlDoc.createNode(1, 'subject', '');
			var subValueNode = xmlDoc.createNode(4, "subject", "");
			var msgNode = xmlDoc.createNode(1, 'message', '');
			var msgValueNode = xmlDoc.createNode(4, "message", "");
			var tagNode = xmlDoc.createNode(1, 'tag', '');
			var tagValueNode = xmlDoc.createNode(4, "tag", "");
				
			delmsg = xmlDoc.selectNodes("//subject");
   			delmsg.removeAll();
   			delmsg = xmlDoc.selectNodes("//message");
   			delmsg.removeAll();
   			delmsg = xmlDoc.selectNodes("//tag");
   			delmsg.removeAll();
			
			msgValueNode.nodeValue = sHTML;
			subValueNode.nodeValue = $('subject').value;
			tagValueNode.nodeValue = $('tag').value;
				
			subNode.appendChild(subValueNode);
			msgNode.appendChild(msgValueNode);
			tagNode.appendChild(tagValueNode);

			root = xmlDoc.documentElement;
				
			root.appendChild(msgNode);
			root.appendChild(subNode);
			root.appendChild(tagNode);
			oArea.save('UCHome');
		} catch (e) {
			if(window.sessionStorage) {
				try {
					sessionStorage.setItem('message', sHTML);
					if($('subject') !=null ) sessionStorage.setItem('subject', $('subject').value);
					if($('tag') !=null ) sessionStorage.setItem('tag', $('tag').value);
				} catch(e) {}
			}
				
		}
	}
}

//向编辑器插入代码
function edit_insert(html) {
	var p = window.frames['uchome-ifrHtmlEditor'];
	var obj = p.window.frames['HtmlEditor'];
	var status = p.document.getElementById('uchome-editstatus').value;
	if(status != 'html') {
		alert('本操作只在多媒体编辑模式下才有效');
		return;
	}
	obj.focus();
	if(window.Event){
		obj.document.execCommand('insertHTML', false, html);
	} else {
		obj.focus();
		var f = obj.document.selection.createRange();
		f.pasteHTML(html);
		f.collapse(false);
		f.select();
	}
}

//插入图片
function insertImage(image) {
	edit_insert('<p><img src="'+image+'"></p>');
}