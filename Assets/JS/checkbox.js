function checkBox() {
  const checkbox = document.getElementById("terms");
  const signUpButton = document.getElementById("signUp");
  const termsErrorMessage = document.getElementById("termsError");
  if (checkbox.checked) {
    signUpButton.disabled = false;
    termsErrorMessage.innerHTML = "";
  } else {
    termsErrorMessage.innerHTML = "Please agree to the terms and conditions!";
    signUpButton.disabled = true;
  }
}
