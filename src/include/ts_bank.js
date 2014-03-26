function toggleAllBank(selected) {
  var bank_form = document.bank;
  for (var i=0; i < bank_form.length; i++) {
    if (bank_form[i].type == 'checkbox') {
      bank_form[i].checked = selected;
    }
  }
}
