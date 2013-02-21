/**
 * 未读消息js For DZ/DX
 * @author yaoying
 * @version $Id: xwb_unreadctr.js 728 2011-05-12 03:34:47Z yaoying $
 */
if(!window.xwbUnreadCtr){
	window.xwbUnreadCtr = {
		uid: 0,
		allsum: 0,
		data: {
			followers: 0,
			dm: 0,
			mentions: 0,
			comments: 0
		},
		
		init: function(uid){
			this.setuid(uid);
			if(this.uid < 1){
				return false;
			}
			for(name in this.data){
				this.data[name] = this.getNumInCookie(name);
				if(this.data[name] > 0){
					this.changeDisplay(name, this.data[name]);
					this.allsum += this.data[name];
				}
			}
			this.changeDisplay('allsum', this.allsum);
		},
		
		setuid: function(uid){
			uid = parseInt(uid);
			this.uid = isNaN(uid) ? 0 : uid;
		},
		
		getNumInCookie: function(name){
			if(this.uid < 1){
				return 0;
			}
			var num = parseInt(getcookie(this.getId(name)));
			return isNaN(num) ? 0 : num;
		},
		
		getId: function(name){
			return 'xwb_' + name + '_' + this.uid;
		},
		
		changeDisplay: function(name, num){
			try{
				var id = this.getId(name);
				if(num < 1){
					document.getElementById(id + '_container').style.display = 'none';
				}else{
					document.getElementById(id).innerHTML = num;
					document.getElementById(id + '_container').style.display = '';
				}
			}catch(e){
				if(window.__debug){
					alert('Change Xweibo Unread Warning:' + e.name + ':' + e.message);
				}
			}
		},
		
		clearCookie: function(name){
			setcookie(this.getId(name), 0, 300);
		},
		
		hideContainer: function(name){
			this.clearCookie(name);
			var num = this.data[name];
			num = isNaN(num) ? 0 : num;
			this.allsum -= num;
			this.changeDisplay(name, 0);
			this.changeDisplay('allsum', this.allsum);
		}
	};
}