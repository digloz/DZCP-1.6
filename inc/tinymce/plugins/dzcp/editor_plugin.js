!function(){tinymce.create("tinymce.plugins.DZCP",{init:function(e,t){e.addCommand("mceSmileys",function(){e.windowManager.open({file:t+"/smileys.php",width:500+parseInt(e.getLang("dzcp.delta_width",0)),height:400+parseInt(e.getLang("dzcp.delta_height",0)),inline:1,resizable:1,scrollbars:1},{plugin_url:t})}),e.addCommand("mceDZCPUser",function(){e.windowManager.open({file:t+"/users.php",width:280+parseInt(e.getLang("dzcp.delta_width",0)),height:400+parseInt(e.getLang("dzcp.delta_height",0)),inline:1,resizable:1,scrollbars:1},{plugin_url:t})}),e.addCommand("mceFlags",function(){e.windowManager.open({file:t+"/flags.php",width:400+parseInt(e.getLang("dzcp.delta_width",0)),height:400+parseInt(e.getLang("dzcp.delta_height",0)),inline:1},{plugin_url:t})}),e.addCommand("mcePastePHP",function(){e.windowManager.open({file:t+"/pastephp.htm",width:500+parseInt(e.getLang("dzcp.delta_width",0)),height:450+parseInt(e.getLang("dzcp.delta_height",0)),inline:1},{plugin_url:t})}),e.addCommand("mceClipMe",function(){e.windowManager.open({file:t+"/clip.php",width:500+parseInt(e.getLang("dzcp.delta_width",0)),height:450+parseInt(e.getLang("dzcp.delta_height",0)),inline:1},{plugin_url:t})}),e.addCommand("mceYoutube",function(){e.windowManager.open({file:t+"/youtube.php",width:500+parseInt(e.getLang("dzcp.delta_width",0)),height:90+parseInt(e.getLang("dzcp.delta_height",0)),inline:1,resizable:1,scrollbars:1},{plugin_url:t})}),e.addButton("smileys",{title:"dzcp.desc",cmd:"mceSmileys",image:t+"/images/smilies.gif"}),e.addButton("dzcpuser",{title:"dzcp.users",cmd:"mceDZCPUser",image:t+"/images/users.gif"}),e.addButton("flags",{title:"dzcp.fldesc",cmd:"mceFlags",image:t+"/images/flags.gif"}),e.addButton("pastephp",{title:"dzcp.php_desc",cmd:"mcePastePHP",image:t+"/images/pastephp.gif"}),e.addButton("clip",{title:"dzcp.clip",cmd:"mceClipMe",image:t+"/images/clip.gif"}),e.addButton("youtube",{title:"dzcp.youtube",cmd:"mceYoutube",image:t+"/images/youtube.gif"})},getInfo:function(){return{longname:"Plugins for DZCP",author:'Frank "deV!L" Herrmann',authorurl:"http://www.dzcp.de",infourl:"",version:"1.5"}}}),tinymce.PluginManager.add("dzcp",tinymce.plugins.DZCP)}(tinymce);