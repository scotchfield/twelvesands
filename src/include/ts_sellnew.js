function toggleAllSell(selected) {
  var sell_form = document.sell;
  for (var i=0; i < sell_form.length; i++) {
    if (sell_form[i].type == 'checkbox') {
      sell_form[i].checked = selected;
    }
  }
}
