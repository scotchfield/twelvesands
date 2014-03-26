var recv_http_request = false;
var refresh_timer;

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

function receiveDuel() {
  getRecvRequest();

  if (recv_http_request) {
    if ((recv_http_request.readyState == 4) ||
        (recv_http_request.readyState == 0)) {
      var req_time = new Date();
      var URL = 'duelasync.php';

      recv_http_request.open("GET", URL, true);
      recv_http_request.onreadystatechange = refreshDuel; 
      recv_http_request.send(null);
    }
  }
}

function refreshDuel(http_request) {
  if ((recv_http_request) && (recv_http_request.readyState == 4)) {
    var state = recv_http_request.responseText;

    if (state == '1') {
      self.location = 'duel.php';
    }

    recv_http_request = null;
    clearTimeout(refresh_timer);
    refresh_timer = setTimeout('receiveDuel();', 5000);
  }
}

receiveDuel();