function showAttachToMail() {
  document.getElementById('mail_attach_link').className = 'invis';
  document.getElementById('mail_attach').className = '';
}

function toggleAllMail(selected) {
  var mail_form = document.mail;
  for (var i=0; i < mail_form.length; i++) {
    if (mail_form[i].type == 'checkbox') {
      mail_form[i].checked = selected;
    }
  }
}

function setArtifactEnchant(enc) {
  var artifact_enchant = document.getElementById('artifact_enchant');
  artifact_enchant.value = enc;
}