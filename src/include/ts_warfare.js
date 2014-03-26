function getWarfareTotalPower() {
  var a1 = document.getElementById('a1');
  var a2 = document.getElementById('a2');
  var a3 = document.getElementById('a3');
  var a4 = document.getElementById('a4');
  var a5 = document.getElementById('a5');

  var span_power = document.getElementById('warfare_power');
  span_power.innerHTML =
      getWarfarePower(a1.value) +
      getWarfarePower(a2.value) +
      getWarfarePower(a3.value) +
      getWarfarePower(a4.value) +
      getWarfarePower(a5.value);
}

function getWarfarePower(id) {
  var o_collection = document.getElementById('a1').options;
  for (var i = 0; i < o_collection.length; i++) {
    if (o_collection.item(i).value == id) {
      var st = o_collection.item(i).text;
      var power = st.substr(st.indexOf('power ') + 6);
      return parseInt(power);
    }
  }
}
