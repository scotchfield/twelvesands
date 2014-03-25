<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head><title>Twelve Sands Chat</title>
<link rel="stylesheet" type="text/css" href="css/site.css" />

<style type="text/css">
.container_chat {
  border: 1px solid black;
  left: 2px;
  position: absolute;
  top: 2px;
}

.chat_div {
  height: 300px;
  overflow: auto;
}

.container_chatbox {
  border: 1px solid black;
  padding-top: 5px;
  text-align: center;
}
</style>

<script language="javascript" type="text/javascript">
var recv_http_request = false;
var send_http_request = false;
var last_id = 0;
var refresh_timer;

function getFocus() {
    document.chatform.m.focus();
}

function getRecvRequest() {
    if (!recv_http_request) {
        try {
            recv_http_request = new XMLHttpRequest();
        } catch (e) {
            try {
                recv_http_request = ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                recv_http_request = new ActiveXObject("Microsoft.XMLHTTP");
            }
        }
    }

    return recv_http_request;
}

function getSendRequest() {
    if (send_http_request) { return; }

    try {
        send_http_request = new XMLHttpRequest();
    } catch (e) {
        try {
            send_http_request = ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            send_http_request = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
}

function receiveChat() {
    getRecvRequest();

    if (recv_http_request) {
      if ((recv_http_request.readyState == 4) ||
          (recv_http_request.readyState == 0)) {
          var req_time = new Date();
          var URL = 'chatasync.php?t=' + last_id + '&d=' + req_time.getTime();

          recv_http_request.open("GET", URL, true);
          recv_http_request.onreadystatechange = refreshChat; 
          recv_http_request.send(null);
        }
    }
}

function sendChat() {
    getSendRequest();
    if (!send_http_request) { return; }

    if (document.getElementById('chatform_m').value.length > 0) {
        send_http_request.open('POST', 'chatasync.php');
        send_http_request.onreadystatechange = refreshChat;
        var m_param = 'm=' + escape(document.getElementById('chatform_m').value);
        var m_param = m_param + '&c=0';

        send_http_request.setRequestHeader("Content-type",
                                      "application/x-www-form-urlencoded");
        send_http_request.setRequestHeader("Content-length", m_param.length);
        send_http_request.setRequestHeader("Connection", "close");
        send_http_request.send(m_param);

        document.getElementById('chatform_m').value = '';

        clearTimeout(refresh_timer);
        refresh_timer = setTimeout('receiveChat();', 500);
    }

    return true;
}

function refreshChat(http_request) {
    if (typeof(recv_http_request) == 'undefined') {
        return;
    }

    if ((recv_http_request) && (recv_http_request.readyState == 4)) {
        var chat_div = document.getElementById('chat_div');
        var xml = recv_http_request.responseXML;

        var c_type = recv_http_request.getResponseHeader("Content-Type");
        if ((c_type) && (c_type.indexOf('text/xml') < 0)) {
            top.location = '/';
        }

        if (xml) {
            var msg_elements = xml.getElementsByTagName("msg");
            var msg_n = msg_elements.length

            for (i=msg_n-1; i>=0; i--) {
                var m  = msg_elements[i].getElementsByTagName("message");
                chat_div.innerHTML = chat_div.innerHTML + '<br>' +
                    m[0].firstChild.nodeValue;

                if (parseInt(msg_elements[i].getAttribute('id')) > last_id) {
                    last_id = parseInt(msg_elements[i].getAttribute('id'));
                }
            }

            if (msg_n > 0) {
                var chat_div = document.getElementById('chat_div');
                chat_div.scrollTop = chat_div.scrollHeight;
            }
        }

        recv_http_request = null;
        clearTimeout(refresh_timer);
        refresh_timer = setTimeout('receiveChat();', 5000);
    }
}

function handleKeystroke(e) {
    var keyPressed;

    if (document.all) {
        keyPressed = e.keyCode;
    } else {
        keyPressed = e.which;
    }

    if (keyPressed == 13) {
        var chat_div = document.getElementById('chat_div');
        var st = document.getElementById('chatform_m').value.toLowerCase();

        if (st == '/help') {
            chat_div.innerHTML = chat_div.innerHTML + '<br>' +
                '<b>System</b>: Twelve Sands Chat Help!<br>' +
                '/exit - exits chat, returns you to the updates frame.<br>' +
                '/help - brings up this help message.<br>' +
                '/who - lists all the current users in the channel.';
            document.getElementById('chatform_m').value = '';
        } else if (st == '/exit') {
            document.location = 'chat_start.html';
        } else {
            sendChat();
        }
    }

    return true;
}

function setSize() {
    var chat_div = document.getElementById('chat_div');
    var chat_box = document.getElementById('container_chatbox');
    var h;
    if (self['innerHeight']) {
        h = self.innerHeight - chat_box.clientHeight;
    } else {
        h = frames.frameElement.clientHeight - chat_box.offsetHeight;
    }
    chat_div.style.height = String(h - 8) + 'px';
}

function initChat() {
    setSize();
    receiveChat();
    getFocus();
}
</script>
</head>
<body class="empty" onload="initChat();" onresize="setSize();">

<div id="container_chat" class="container_chat">
  <div id="chat_div" class="chat_div">
<p align="center"><b>Twelve Sands Chat</b></p>
  </div>
  <div id="container_chatbox" class="container_chatbox">
    <form name="chatform" id="chatform" method="get" onsubmit="return false;">
    <input type="text" id="chatform_m" name="m" style="width: 220px;"
           onkeypress="return handleKeystroke(event)" />
    <input type="button" onclick="sendChat();" value="Chat" />
    </form>
  </div>
</div>

</body></html>
