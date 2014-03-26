function setArtifactEnchant(enc) {
  var artifact_enchant = document.getElementById('artifact_enchant');
  artifact_enchant.value = enc;
}

function showEnchantRecipe(enc) {
  hideEnchantRecipes();
  var recipe_div = document.getElementById('recipe_' + enc);
  recipe_div.className = '';
}