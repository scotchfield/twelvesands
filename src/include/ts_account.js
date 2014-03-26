function S_FormSubmit()
{
  var account_form = document.forms.account_form;
  if (account_form.pa.value.length < 6) {
    alert('Password is too short!');
    return false;
  }
  if (account_form.pa.value != account_form.pb.value) {
    alert('Passwords do not match!');
    return false;
  }
  var pass = hex_md5(account_form.pa.value);
  account_form.i.value = pass;
  account_form.pa.value = '';
  account_form.pb.value = '';
  account_form.submit();
}
