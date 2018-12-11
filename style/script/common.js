/*kunbei 2017-04-14*/
/*封装同意的下来刷新，传递回调函数*/
function refreshHeader(callback)
{
	api.setRefreshHeaderInfo({
	            visible : true,
	            loadingImg : 'widget://image/appicon.png',
	            bgColor : '#ccc',
	            textColor : '#000',
	            textDown : '当前数据最新...',
	            textUp : '当前数据最新...',
	            showTime : false
	    }, function(ret, err) {
	    	callback();
	    	
	    }
	 );
}
/*滚动到底部的回掉函数*/
function loadMoreInfo(callback){
     api.addEventListener({
		    name:'scrolltobottom',
		    extra:{
		        threshold:0            //设置距离底部多少距离时触发，默认值为0，数字类型
		    }
		}, function(ret, err){        
		    callback();
		});	
}
/*验证码*/
function load_yzm(obj)
{
	obj.src = openapi.siteurl+"s=member&c=api&m=captcha&width=120&height=40&rd="+Math.random();
} 
/*系统基本参数*/
var cfg = {
	debug : 1,
	siteurl : 'http://appauto5h.adminweb.net',
	mobileurl: 'http://appauto5h.adminweb.net',
	//siteurl : 'http://www.auto5h.com',
	//mobileurl: 'http://m.auto5h.com',
	catid_ershou :1, 
	catid_tehui :2,
	catid_zudai:3,
	catid_zuping:4
}
var openapi = {
	//siteurl:'http://www.auto5h.com/index.php?',
	siteurl:'http://appauto5h.adminweb.net/index.php?',
	data:{
		auth: '7f8e44d972334bd0429371d8460d0fd8',// md5(CI3C0FA7E9061F7F)
		param :  '',
		format:'json',
		c : 'api',
		m : 'data2'	
	}
}
var openapi_query = function(params)
{
	var str = dot = "";
	for(var key in params){
			str+= dot + key+ "="+params[key] ;
			dot = "&";
	}
	return openapi.siteurl + str;
}
var file_load_count = 0;
var _imgs = [];
function count_load_file_nums(){
 
	if(file_load_count==0){ 
		$("img.lazyimg").lazyload({effect:'fadeIn',threshold:'50'});
		var len = _imgs.length; 
		for(var i=0; i<len; i++){
			//$("#_img"+_imgs[i]).lazyload({effect:'fadeIn',threshold:'50'});
			$("#_img"+_imgs[i]).removeClass("lazyimg");			
		} 
	}
}
/*通过文件id获取 url 并赋值给img.src 属性显示图片 */ 
var openapi_getfile = function(fileid, issouce)
{
	file_load_count = file_load_count +1;
	if(issouce){ //调用原图
		openapi['data'].param = "get_file";
		openapi['data'].id  =fileid;
	}else{
		openapi['data'].param = 'function&name=dr_thumb2&p1='+fileid+'&p2=200&p3=175&p4=0';
	}
	//
	var apiurl = openapi_query(openapi['data']);
	//showalert(apiurl);
	api.ajax({url:apiurl, 
			method:"get",
			timeout:10
	}, function(ret){ 
				  if(ret.url){ //请求原图的接口返回
			    			//document.getElementById("_img"+fileid).src = ret.url;
			    			$("#_img"+fileid).attr("data-original", ret.url);
			    	}
			    	if(ret.result){ //请求缩略图接口返回
			    		$("#_img"+fileid).attr("data-original", ret.result);
			    	}
			    	file_load_count =file_load_count -1;
			    	_imgs.push(fileid);
			    	count_load_file_nums(); 
			/*api.imageCache({启用图片缓存
				thumbnail:true,
    			url:ret.url
			}, function(cache, err) {
				if(cache.status==true){
					if(cache.url){
			    	document.getElementById("_img"+fileid).src = cache.url;
			    	}
			    }else{
			    	if(ret.url){
			    	document.getElementById("_img"+fileid).src = ret.url;
			    	}
			    }
			});	*/			 
	 
	});
}
/*接收文本消息，对象要先用JSON.stringify(ret)*/
var showalert = function(msg){
	if(cfg.debug==1){
	 	console.warn(msg); 
	}else{
		//api.alert(msg);
	}
}

var formatdata= function(datastr)
{
	var da = new Date(datastr);
	var year = da.getFullYear()+'年';
    var month = da.getMonth()+1+'月';
    var date = da.getDate()+'日';
    return  year + month+ date;
 
}
var sheetUser = {
"uid":0,
"email":"",
"username":"",
"password":"",
"salt":"",
"name":"",
"phone":"",
"avatar":"",
"money":"0.00",
"freeze":"0.00",
"spend":"0.00",
"score":"0",
"experience":"30",
"adminid":"0",
"groupid":"4",
"levelid":"5",
"overdue":"0",
"regip":"116.1.89.42",
"regtime":"1492135666",
"randcode":"0",
"ismobile":"1",
"complete":"1",
"is_auth":"0",
"tableid":5,
"groupname":"",
"levelname":"\u666e\u901a",
"avatar_url":"\/statics\/admin\/images\/avatar_45.png",
"levelstars":"16",
"allowspace":"0",
"spacedomain":"0",
"bang":0,
"weixin":null,
"mark":"4_5"
};
function fetchMemberInfo(uid)
{
	openapi['data'].param = 'function&name=dr_member_info&p1='+uid; 
	var dataurl = openapi_query(openapi.data);
	api.ajax({url:dataurl,method:"get" }, function(ret, err){
		showalert(JSON.stringify(ret.result));
		_localdata.setItem('$member', JSON.stringify(ret.result) ); 
	}) 
}
/*这个是封转本地存储的功能*/
var _localdata = {
	info :'我是本地存储类',
	version : function(){ return this.info;},
 	setItem : function(key, val) 
	{
		return localStorage.setItem( key, val);
	},
 	getItem : function(key){
		return localStorage.getItem(key);
	},
	removeItem : function(key){
		return 	localStorage.removeItem(key);
	},
	clearall : function(){
		return   localStorage.clear(); 
	}
} 
/*用于判断没有登陆跳转到登陆窗口*/
function _noLoginDumptoLoginWin()
{
 
	api.openWin({
        name: 'my_frame_login',
        url: './login_header.html',
        bounces: false,
		delay:200
    });       
	 
}
function setUserLoginStatus(){

	var users = "";
	if(_localdata.getItem('$member') ){
		users = JSON.parse(_localdata.getItem('$member') || '[]');
	}
	if(users==""){	
		 _localdata.setItem('$islogin', "no"); 
	}else{
		_localdata.setItem('$islogin', "yes"); 
	}
}
function getUserLoginStatus()
{
	return _localdata.getItem('$islogin'); 
}
function getUserInfo(){	 
	if(_localdata.getItem('$member') ){
		sheetUser = JSON.parse(_localdata.getItem('$member') || '[]');
	}
	return sheetUser;
}
/*从storage 获取会员信息*/
/*汽车发布参数*/
var carAttr = [
{field:'cheling',name:'车龄',select:{
'1年内':'1年内',
'1-3年':'1-3年',
'3-5年':'3-5年',
'5-8年':'5-8年',
'8年以上':'8年以上'
}},
{field:'licheng',name:'里程',select: {
'1万公里内':'1万公里内',
'1-3万公里':'1-3万公里',
'3-6万公里':'3-6万公里',
'6-10万公里':'6-10万公里',
'10-20万公里':'10-20万公里',
'20万公里以上':'20万公里以上'
}},	
{field:'pailiang',name:'排量',select:{
'1.0L以下':'1.0L以下',
'1.1L-1.6L':'1.1L-1.6L',
'1.6L-2.0L':'1.6L-2.0L',
'2.0L-2.5L':'2.0L-2.5L',
'2.5L-3.0L':'2.5L-3.0L',
'3.0L-4.0L':'3.0L-4.0L',
'4.0L以上':'4.0L以上'
}},
{field:'yanse',name:'颜色',select:{
'黑色':'黑色',
'灰色':'灰色',
'银灰色':'银灰色',
'白色':'白色',
'香槟色':'香槟色',
'黄色':'黄色',
'橙色':'橙色',
'红色':'红色',
'粉红色':'粉红色',
'紫色':'紫色',
'蓝色':'蓝色',
'绿色':'绿色',
'咖啡色':'咖啡色',
'多彩色':'多彩色'
}},
{field:'biansuxiang',name:'变速箱',select:{
'自动':'自动',
'手动':'手动'
}},
{field:'zuoweishu',name:'座位',select:{
'2座':'2座',
'4座':'4座',
'5座':'5座',
'7座':'7座',
'7座以上':'7座以上'
}},
{field:'qcchexing',name:'车型',select:{
'微型车':'微型车',
'小型车':'小型车',
'紧凑型':'紧凑型',
'中型车':'中型车',
'中大型':'中大型',
'豪华车':'豪华车',
'跑车':'跑车',
'SUV':'SUV',
'MPV':'MPV',
'CROSS':'CROSS',
'皮卡':'皮卡',
'客车':'客车',
'货车':'货车',
'面包车':'面包车',
'微卡':'微卡'
}}
];
var search_carAttr = [
{field:'cheling',name:'车龄',select:{
'不限制':'不限制',
'1年内':'1年内',
'1-3年':'1-3年',
'3-5年':'3-5年',
'5-8年':'5-8年',
'8年以上':'8年以上'
}},
{field:'licheng',name:'里程',select: {
'不限制':'不限制',
'1万公里内':'1万公里内',
'1-3万公里':'1-3万公里',
'3-6万公里':'3-6万公里',
'6-10万公里':'6-10万公里',
'10-20万公里':'10-20万公里',
'20万公里以上':'20万公里以上'
}},	
{field:'pailiang',name:'排量',select:{
'不限制':'不限制',
'1.0L以下':'1.0L以下',
'1.1L-1.6L':'1.1L-1.6L',
'1.6L-2.0L':'1.6L-2.0L',
'2.0L-2.5L':'2.0L-2.5L',
'2.5L-3.0L':'2.5L-3.0L',
'3.0L-4.0L':'3.0L-4.0L',
'4.0L以上':'4.0L以上'
}},
{field:'yanse',name:'颜色',select:{
'不限制':'不限制',
'黑色':'黑色',
'灰色':'灰色',
'银灰色':'银灰色',
'白色':'白色',
'香槟色':'香槟色',
'黄色':'黄色',
'橙色':'橙色',
'红色':'红色',
'粉红色':'粉红色',
'紫色':'紫色',
'蓝色':'蓝色',
'绿色':'绿色',
'咖啡色':'咖啡色',
'多彩色':'多彩色'
}},
{field:'biansuxiang',name:'变速箱',select:{
'不限制':'不限制',
'自动':'自动',
'手动':'手动'
}},
{field:'zuoweishu',name:'座位',select:{
'不限制':'不限制',
'2座':'2座',
'4座':'4座',
'5座':'5座',
'7座':'7座',
'7座以上':'7座以上'
}},
{field:'qcchexing',name:'车型',select:{
'不限制':'不限制',
'微型车':'微型车',
'小型车':'小型车',
'紧凑型':'紧凑型',
'中型车':'中型车',
'中大型':'中大型',
'豪华车':'豪华车',
'跑车':'跑车',
'SUV':'SUV',
'MPV':'MPV',
'CROSS':'CROSS',
'皮卡':'皮卡',
'客车':'客车',
'货车':'货车',
'面包车':'面包车',
'微卡':'微卡'
}}
];
 
