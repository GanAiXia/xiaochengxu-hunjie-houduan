// 不加入事件,直接调整，更快
var docEle = document.documentElement;
var width = docEle.clientWidth;
           
if(width>450) width=450; //最大不许超过 450
width && (docEle.style.fontSize = 20 * (width / 320) + "px");



//桂房
$.guifun = {};

//判断是否为空
$.guifun.isNull = function (str){
    
    if ((!str) || (typeof(str)=="undefined") || (str==null) || (str==0) ){ 
        return true;
    }else{
        return false;
    }
    
}

/**
 * 弹窗校验
 * @param {type} id
 * @param {type} type | isNull = 是否为空
 * @returns {Boolean}
 */
$.guifun.checkForm = function(id,type){

   var item = $('#'+id).val();
   
   if(type=='isNull' || type=='isnull') {
       
       if (item.length==0){
       return true;
    }
       
   }
   
   return false;
}

//跳转连接
$.guifun.goto = function (url){
    
    location.href = url;
    
}


$.guifun.Number = function (num){
    
    if(!num){
        num = 0;
    }
    
    return Number(num);
    
}

//检测用户Token
$.guifun.memberCheckToken = function(ajaxUrl,successFunAdd,errorFunAdd){
    
    var username = localStorage.getItem('guifun_api_username'); 
    var token = localStorage.getItem('guifun_api_token');
    
    if($.guifun.isNull(username) || $.guifun.isNull(token)){
        errorFunAdd();
        return;
    }
    
    $.ajax({
        type: 'GET', //type GET ,POST
        url: ajaxUrl+'/params/username='+username+'|token='+token,
        
        success: function(data) {
            
            if (successFunAdd != null) {
                successFunAdd(data);
            }

        },
        error: function(data) {

            if (errorFunAdd != null) {
                errorFunAdd(data);
            }

        },
        timeout: function(data) {

            if (errorFunAdd != null) {
                errorFunAdd(data);
            }
        }

    });
    
}

/**
 * 保存token
 * @param {type} username
 * @param {type} token
 * @returns {undefined}
 */
$.guifun.saveToken = function(username,token){
    localStorage.setItem('guifun_api_username',username); 
    localStorage.setItem('guifun_api_token',token);
}


//ajax data 开始----------------------------------------------------

function guifun_data(divID,ajaxType,ajaxUrl,params){
    
        this.data = null;                          //载入的数据
	this.ajaxUrl = ajaxUrl;	//AJAX URL
        this.ajaxType; //GET POST
	this.divID = divID;
        this.loadClass = "loading"; //加载class
        this.isload = false;

	if(params!=null) {
		this.params = params;
	}else{
		this.params = {};
	}

}

 //设置参数值
 guifun_data.prototype.setParams = function(name,val){
        
 	this.params[name] = val;

 }
 
 
 //取得参数值
 guifun_data.prototype.getParams = function(name){
     
     return this.params[name];
 }
 
 
 //设置loadClass
// guifun_data.prototype.setLoadClass = function(className){
//     
//     this.loadClass = className;
// }
 

//加载数据
 guifun_data.prototype.getData = function(successFunAdd,errorFunAdd){ //append = 1 追加数据 ,


 	//加载提示，以后换成加载图标
        //var loadingDiv = this.prefix+this.name+"loading";
        //var loadingDiv = "loadingDiv_js_auto";

 	var that = this;
        if(that.isload){
            return;
        }
        
        that.isload=true;

         
 	//读取成功
 	var successFun = function(data){
            
           //追加处理
            if(successFunAdd){
                successFunAdd(data);
            }
        
 	}
        

 	//读取失败
 	var errorFun = function(data){
            
                if(errorFunAdd){
                    errorFunAdd();
                }
 	}


 	var timeoutFun = function(data){
            
 	}

	 
        //(this.ajaxUrl,"GET",this.params,successFun,errorFun,timeoutFun,this.useCache);
        $.ajax({
        type: this.ajaxType, //type GET ,POST
        data: this.params, //参数
        url: this.ajaxUrl,
        success: function(data) {
            that.isload=false;
            if (successFunAdd != null) {
                successFunAdd(data);
            }

        },
        error: function(data) {
            that.isload=false;
            if (errorFunAdd != null) {
                errorFunAdd(data);
            }

        },
        timeout: function(data) {
            that.isload=false;
            if (errorFunAdd != null) {
                errorFunAdd(data);
            }
        }

    });

    }

    //ajax data 结束----------------------------------------------------
    
    
    






/**
 *  bootstrap 效果版 
 */

/**
 * 弹窗校验 如果校验通过 返回 对话框 的值，否者反回 false 
 * @param {type} id
 * @returns {Boolean}
 */
$.guifun.checkFormIsNull = function(id,title){

   var item = $('#'+id).val();
   
    if (item.length==0){
        
       swal({
            type: 'warning',
            title: title,
       });
          
    return false;

   }else{
       return item;
   }

}



//ajax data 开始----------------------------------------------------
function guifun_ajax(ajaxType,ajaxUrl,params){
    
        this.data = null;                          //载入的数据
	this.ajaxUrl = ajaxUrl;	//AJAX URL
        this.ajaxType = ajaxType; //GET POST
        this.isload = false;

	if(params!=null) {
		this.params = params;
	}else{
		this.params = {};
	}

}

 //设置参数值
 guifun_ajax.prototype.setParams = function(name,val){
        
 	this.params[name] = val;

 }
 
 
 //取得参数值
 guifun_ajax.prototype.getParams = function(name){
     
     return this.params[name];
 }
 


//加载数据
 guifun_ajax.prototype.getData = function(successFunAdd,errorFunAdd){ //append = 1 追加数据 ,

 	var that = this;
        if(that.isload){
            return;
        }
        
        that.isload=true;

        $.ajax({
        type: this.ajaxType, //type GET ,POST
        data: this.params, //参数
        url: this.ajaxUrl,
        
        success: function(data) {
            that.isload=false;
            
            if(successFunAdd){
                successFunAdd(data);
            }

        },
        error: function(data) {
            that.isload=false;
             if(errorFunAdd){
                    errorFunAdd(data);
             }

        },
        timeout: function(data) {
            that.isload=false;
        }

    });

    }
    
    //切换用户面版    
    function changeDropdownMenu(menuButtonID,menuPanelID){
        
        var isopen = $("#"+menuButtonID).hasClass('open');

        
        if(isopen){

            
            $("#"+menuButtonID).removeClass("open");
            setTimeout(function(){
                $("#"+menuPanelID).css("display","none");
            },200)
            
             
        }else{
           
            $("#"+menuPanelID).css("display","block");
             setTimeout(function(){
                $("#"+menuButtonID).addClass("open");
            },200)
            
        }
    }



