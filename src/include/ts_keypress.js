function detectKeypress(e) {
  var char_code = 0;

  if (window.event) {
    char_code = window.event.keyCode;
  } else {
    char_code = e.charCode;
  }

  if (char_code > 0) {
    var bar_obj = null;

    if ((char_code >= 48) && (char_code <= 57)) {
      var bar_id = char_code - 48;
      if (bar_id == 0) { bar_id = 10; }
      bar_obj = document.getElementById('bar_'+bar_id);
    } else if ((char_code == 96) || (char_code == 43)) {
      bar_obj = document.getElementById('bar_default');
    }

    if (bar_obj != null) {
      document.location = bar_obj.href;
    }
  }
  return true;
}

document['onkeypress'] = detectKeypress;
