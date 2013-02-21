if(!window.xwbOw){
	window.xwbOw = {
		show: function(owUid, owName, profileUrl, showAttention){
			var fatherDom = document.getElementById('chart');
			var p = document.createElement('p');
			p.id = 'xwbofficewb';
			//p.className = 'z';
			if(showAttention && showAttention == 1){
				p.innerHTML = this.tplAttentionButton(owUid) + this.tplOw(owName, profileUrl);
				
			}else{
				p.innerHTML = this.tplOw(owName, profileUrl);
			}
			fatherDom.appendChild(p);
		},
		
		tplOw: function(owName, profileUrl){
			return [
			        	'<span class="pipe">|</span>',
			        		'<a href="',
			        		profileUrl,
			        		'" target="_blank" class="userweiboicon">',
			        		owName,
			        		'</a>'
			       ].join('');
		},
		
		tplAttentionButton: function(owUid){
			return [
		        	'<a href="xwb.php?m=xwbSiteInterface.attention&att_id=',
		        	owUid,
		        	'" target="_blank" class="addfollow-btn"></a>'
		       ].join('');
		}
	};
}