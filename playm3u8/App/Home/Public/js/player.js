PlayM3u8.ssl = 'https:' == document.location.protocol ? true : false;

function play(cip) {
    if (PlayM3u8.refres == 1) {
        if (PlayM3u8.js_encryption) {
            PlayM3u8.get.str = encodeURIComponent(sign(PlayM3u8.get.str))
        }
    }
    PlayM3u8.poster = "//i3.letvimg.com/lc04_live/201705/05/23/01/1493996499035new.gif";
    if (PlayM3u8.get.type == 'bilibili') {
        $.getScript("//data.bilibili.com/v/web/web_cm_event?log_name=pv&page_ref=http%253A%252F%252Fwww.bilibili.com%252F&resource_id=29&src_id=&is_cm_loc=&is_cm=&event=&ts=" + new Date().getTime())
    }
    $.post("index.php", {
        data: PlayM3u8.get.str,
        cip: cip,
        refres: PlayM3u8.refres
    }, function(data) {
        if (data['code'] == 200) {
            if (data.ext == PlayM3u8.prefix + 'qq') {
                var get = getQuery(data.url);
                $.ajax({
                    url: "//h5vv.video.qq.com/getinfo?charge=0&vid=" + get.vid + "&defaultfmt=auto&otype=json&guid=" + get.guid + "&platform=" + get.platform + "&defnpayver=1&appVer="+get.appVer+"&sdtfrom=" + get.sdtfrom + "&host=v.qq.com&ehost=https%3A%2F%2Fv.qq.com%2Fx%2Fcover%2Fnuijxf6k13t6z9b%2Fl0023olk3g4.html&_0=" + get._0 + "&defn=mp4&fhdswitch=0&show1080p=1&isHLS=0&newplatform=" + get.sdtfrom + "&defsrc=1&_1=" + get._1 + "&_2=" + get._2,
                    dataType: 'jsonp',
                    jsonpCallback: "txplayerJsonpCallBack_getkey_" + parseInt(Math.random() * 800000 + 80000),
                    success: function(getinfo) {
                        if (!getinfo.exem) {
                            var url = getinfo.vl.vi[0].ul.ui[0].url;
                            var reg = /^http(s)?:\/\/(.*?)\//;
                            url.replace(reg, 'Host/');
                            if(reg.exec(url)[2].indexOf(".qq.com") < 0){
                                var urlcdn = getinfo.vl.vi[0].ul.ui[1].url;
                            } else {
                                var urlcdn = getinfo.vl.vi[0].ul.ui[0].url;
                            }
                            if(PlayM3u8.isiPad == false){
                               $.ajax({
                                    url: data.url + '&filename=' + getinfo.vl.vi[0].lnk + '.mp4',
                                    dataType: 'jsonp',
                                    jsonpCallback: "txplayerJsonpCallBack_getkey_" + parseInt(Math.random() * 800000 + 80000),
                                    success: function(json) {
                                        var get = getQuery(data.url);
                                        data.ext = 'xml';
                                        data.url = window.document.location.pathname + '?a=setxml&url=' + BASE64.en(urlcdn + json.filename + '?sdtfrom=' + get.sdtfrom + '&guid=' + get.guid + '&vkey=' + json.key)
                                        ckplayer_(data)
                                    }
                                })
                            } else {
                                get.infohost = decodeURIComponent(get.infohost);
                                data.url = data.url.substring(data.url.indexOf(".qq.com")+15, data.url.length);
                                $.getScript(get.infohost +"?"+ data.url + '&filename=' + getinfo.vl.vi[0].lnk + '.mp4&urlcdn='+encodeURIComponent(urlcdn));
                            }
                        } else {
                            message('亲，解析失败或资源不存在。')
                        }
                    }
                })
            } else if (data.ext == PlayM3u8.prefix + 'bilibili') {
                if (!!window.ActiveXObject || "ActiveXObject" in window) {
                    message('亲，不支持IE浏览器播放，请更换其他浏览器。');
                    return
                }
                $.ajax({
                    url: data.url,
                    dataType: 'jsonp',
                    jsonpCallback: "callbackfunction",
                    success: function(json) {
                        if (json.code != 40000) {
                            data.url = json.durl[0].url;
                            PlayM3u8.poster = json.img.replace('https:', '');
                            if (PlayM3u8.isiPad) {
                                data.ext = 'h5';
                                ckplayer_(data)
                            } else {
                                dkplayer_(data)
                            }
                        } else {
                            message('亲，解析失败或资源不存在。')
                        }
                    }
                })
            } else if (data.ext == PlayM3u8.prefix + 'iqiyi') {
                $.ajax({
                    url: data.url.replace('http:', ''),
                    dataType: 'html',
                    success: function(json) {
                        json = eval("("+json.substring(17,json.length - 15)+")");
                        if (json.code == 'A00000') {
                            if (PlayM3u8.isiPad) {
                                data.url = json.data.m3u;
                                data.ext = 'h5';
                                ckplayer_(data)
                            } else {
                                var array = {};
                                for (var i = json.data.vidl.length - 1; i >= 0; i--) {
                                    if (json.data.vidl[i].fileFormat != "H265") {
                                        array[json.data.vidl[i].vd] = json.data.vidl[i]
                                    }
                                };
                                if (array[4] != undefined) {
                                    data.url = array[4].m3u
                                } else if (array[3] != undefined) {
                                    data.url = array[3].m3u
                                } else if (array[2] != undefined) {
                                    data.url = array[2].m3u
                                } else if (array[1] != undefined) {
                                    data.url = array[1].m3u
                                } else if (array[96] != undefined) {
                                    data.url = array[96].m3u
                                }
                                $.ajax({
                                    url: data.url.replace('http:', ''),
                                    success: function(m3u) {
                                        $.post("index.php?a=m3u8", {
                                            m3u: BASE64.en(m3u),
                                            url: data.url,
                                            type: PlayM3u8.get.type
                                        }, function(str) {
                                            data.url = str.url;
                                            data.ext = 'xml';
                                            ckplayer_(data)
                                        }, "json")
                                    }
                                })
                            }
                        } else {
                            message('亲，解析失败或资源不存在。')
                        }
                    }
                })
            } else if(data.ext == PlayM3u8.prefix + 'youku'){
                var get = getQuery(data.url);
                data.ccode = get.ccode;
                data.site  = "youku";
                data.stype = get.stype;
                data.vid   = get.vid;
                data.weparser_js_url  = BASE64.de(decodeURIComponent(get.js_url));
                data.weparser_swf_url = BASE64.de(decodeURIComponent(get.swf_url));
                if(PlayM3u8.isiPad){
                    weParserParams = data;
                    var weParserJS = document.createElement("script");
                    weParserJS.type = "text/javascript";
                    weParserJS.src = data.weparser_js_url;
                    document.getElementsByTagName("head")[0].appendChild(weParserJS);
                } else {
                    data.ext = 'xml';
                    data.url = window.document.location.pathname+'?a=setswf&data='+BASE64.en($.param(data));
                    ckplayer_(data);
                }
            } else if(data.ext == PlayM3u8.prefix + 'youku_ac'){
                var get = getQuery(data.url);
                data.playtype = 'mp4';
                data.site  = "acfun";
                data.stype = 3;
                data.vid   = get.vid;
                data.sign  = get.sign;
                data.weparser_js_url  = BASE64.de(decodeURIComponent(get.js_url));
                data.weparser_swf_url = BASE64.de(decodeURIComponent(get.swf_url));
                if(PlayM3u8.isiPad){
                    weParserParams = data;
                    var weParserJS = document.createElement("script");
                    weParserJS.type = "text/javascript";
                    weParserJS.src = data.weparser_js_url;
                    document.getElementsByTagName("head")[0].appendChild(weParserJS);
                } else {
                    data.ext = 'xml';
                    data.url = window.document.location.pathname+'?a=setswf&data='+BASE64.en($.param(data));
                    ckplayer_(data);
                }
            } else {
                ckplayer_(data)
            }
        } else if (data.code == 403) {
            message(data.msg)
        } else {
            if (PlayM3u8.refres <= 3) {
                if (PlayM3u8.refres == 1) {
                    message(PlayM3u8.start_msg + " .")
                } else if (PlayM3u8.refres == 2) {
                    message(PlayM3u8.start_msg + " . .")
                } else if (PlayM3u8.refres == 3) {
                    message(PlayM3u8.start_msg + " . . .")
                }
                PlayM3u8.refres++;
                player()
            } else {
                message(data.msg)
            }
        }
    }, "json")
}

function message(msg) {
    $('#loading').hide();
    $('#a1').hide();
    $('#error').show();
    $('#error').html(msg + '<br><br><img border="0"<img src="App/Home/Public/player/logo/load.gif" />')
}

function player() {
    var cip = "";
    play(cip);
    /*
    if (PlayM3u8.get.type == 'iqiyi' || PlayM3u8.get.type == 'iqiyi_vip') {
        $.post("//data.video.iqiyi.com/v.mp4", function(json) {
            var uip = json.match(/http:\/\/([^\"]*)\/v.mp4/);
            play(uip[1])
        })
    } else {
        play(cip)
    }
    */
}

function dkplayer_(data) {
    var dp = new DPlayer({
        element: document.getElementById('a1'),
        autoplay: PlayM3u8.auto_play || false,
        theme: '#FADFA3',
        loop: true,
        screenshot: false,
        video: {
            url: data.url,
            pic: data.img ? data.img : PlayM3u8.poster,
            type: data.ext ? data.ext : 'auto',
        }
    });
    $('#loading').hide();
    $('#a1').show()
}

function dplayer_error() {
    if (PlayM3u8.refres < 3) {
        PlayM3u8.refres++;
        // player()
    } else {
        if (PlayM3u8.ssl) {
            // message('啊哦！资源播放失败或刷新页面重试。')
        } else {
            // message('播放失败,可能有防盗链,可尝试用https请求.')
        }
    }
}

function video_error() {
    var video = document.getElementById("myVideo");
    if (video.error != null) {
        if (video.error.code == 4) {}
    }
}

function ckplayer_(data) {
    if (data.ext == 'iframe') {
        $('#a1').html('<iframe width="' + PlayM3u8.width_height[0] + '" height="' + PlayM3u8.width_height[1] + '" allowTransparency="true" frameborder="0" scrolling="no" allowfullscreen="true" src="' + data.url + '"></iframe>')
    } else if (data.ext == 'h5' || data.ext == 'hls') {
        if (data.img != null && data.img != '') {
            PlayM3u8.poster = data.img
        }
        if (PlayM3u8.isiPad == false) {
            PlayM3u8.poster = '';
            dkplayer_(data)
        } else {
            if (PlayM3u8.auto_play) {
                $('#a1').html('<video id="myVideo" poster="' + PlayM3u8.poster + '" src="' + data.url + '" controls="controls" autoplay="autoplay" width="' + PlayM3u8.width_height[0] + '" height="' + PlayM3u8.width_height[1] + '"></video>')
            } else {
                $('#a1').html('<video id="myVideo" poster="' + PlayM3u8.poster + '" src="' + data.url + '" controls="controls" width="' + PlayM3u8.width_height[0] + '" height="' + PlayM3u8.width_height[1] + '"></video>')
            }
            setInterval('video_error()', 1000)
        }
    } else if (data.ext == 'xml') {
        var flashvars = {
            f: data.url,
            s: 2,
            h: PlayM3u8.flashvars_h,
            p: PlayM3u8.auto_play ? 1 : 0,
            c: 0,
            e: 5,
            my_url: encodeURIComponent(PlayM3u8.qrcode_url),
            m: PlayM3u8.ad_m,
            d: PlayM3u8.ad_d,
            u: PlayM3u8.ad_u,
            l: PlayM3u8.ad_l,
            r: PlayM3u8.ad_r,
            t: PlayM3u8.ad_t,
            my_title: PlayM3u8.play_title
        };
        if(data.k != null && data.n != null){
            flashvars.k = data.k.join("|");
            flashvars.n = data.n.join("|");
        }
        var params = {
            bgcolor: '#FFF',
            allowFullScreen: true,
            allowScriptAccess: 'always',
            wmode: 'transparent'
        };
        CKobject.embedSWF('App/Home/Public/player/skin/ckplayer' + PlayM3u8.player_skin + '/ckplayer.swf', 'a1', 'ckplayer_a1', PlayM3u8.width_height[0], PlayM3u8.width_height[1], flashvars, params)
    }
    if (data.ext == "url_list") {
        Subsection_Play({
            urls: JSON.parse(data.url),
            image: PlayM3u8.poster
        })
    } else {
        $('#loading').hide();
        $('#a1').show()
    }
}

function getQuery(url) {
    if (typeof url !== 'string') {
        return null
    }
    var query = url.match(/[^\?]+\?([^#]*)/, '$1');
    if (!query || !query[1]) {
        return null
    }
    var kv = query[1].split('&');
    var map = {};
    for (var i = 0, len = kv.length; i < len; i++) {
        var result = kv[i].split('=');
        var key = result[0],
            value = result[1];
        map[key] = value || (typeof value == 'string' ? null : true)
    }
    return map
}

function Subsection_Play(data) {
    var Video;
    var playData = [];
    var playIdx  = 0;
    playData = data;
    if (data.urls.length > 1) {
        message("特殊原因，视频为分段播放<br/>几分钟一段播放完一段会自动播放下一段");
        setTimeout(function(){
            $('#error').hide();
            $('#a1').show();
            Video.play();
        }, 2000);
    }
    if (data.urls.length == 0) {
        message("视频源为空！");
        return;
    }
    Video = document.createElement('video');
    Video.id = 'videoPlayer';
    Video.style.width = '100%';
    Video.style.height = '100%';
    Video.controls = 'controls';
    Video.poster = data.image;
    a1.appendChild(Video);
    var playLink = playData.urls[0].url;
    Video.src = playLink;
    if (playData.urls.length == 1) {return;}
    Video.onended = function() {
        playIdx++;
        if (playIdx < playData.urls.length) {
            Video.poster = "";
            Video.src = playData.urls[playIdx].url;
            Video.play();
        } else {
            playIdx = 0;
            Video.src = playData.urls[playIdx].url;
        }
    }
    Video.onseeking = function() {
        if (Video.currentTime < 2 && playIdx > 0) {
            playIdx--;
            Video.src = playData.urls[playIdx].url;
            Video.play();
            setTimeout('Video.currentTime = playData.urls[playIdx].seconds - 5;', 500);
        } else if (Video.currentTime > playData.urls[playIdx].seconds - 2 && playIdx < playData.urls.length - 1) {
            playIdx++;
            Video.src = playData.urls[playIdx].url;
            Video.play();
        }
    }
}

(function(){

    var BASE64_MAPPING = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    var __BASE64 = {
        // 解密
        de:function (r) {
            var o = String(r).replace(/=+$/, "");
            if (o.length % 4 == 1)throw new t("'atob' failed: The string to be decoded is not correctly encoded.");
            for (var n, a, i = 0, c = 0, d = ""; a = o.charAt(c++); ~a && (n = i % 4 ? 64 * n + a : a, i++ % 4) ? d += String.fromCharCode(255 & n >> (-2 * i & 6)) : 0)a = BASE64_MAPPING.indexOf(a);
            return d
        },
        // 加密
        en:function (r) {
            for (var o, n, a = String(r), i = 0, c = BASE64_MAPPING, d = ""; a.charAt(0 | i) || (c = "=", i % 1); d += c.charAt(63 & o >> 8 - i % 1 * 8)) {
                if (n = a.charCodeAt(i += .75), n > 255)throw new t("'btoa' failed: The string to be encoded contains characters outside of the Latin1 range.");
                o = o << 8 | n
            }
            return d
        }
    };
    window.BASE64 = __BASE64;
})();

if(location.href.indexOf('#')>0 && location.href.indexOf('bilibili')){
    location.href = location.href.replace('#','_');
}else{
    player();
}