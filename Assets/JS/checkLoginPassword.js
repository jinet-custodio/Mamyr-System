function checkLoginPassword() {
  const password = document.getElementById("login_password").value;
  const passwordValidation = document.getElementById("passwordLValidation");
  const loginBtn = document.getElementById("login");
  const passwordLetter = /[a-zA-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;

  if (password === "") {
    passwordValidation.innerHTML = "Please enter your password!";
    loginBtn.disabled = true;
  } else if (!password.match(passwordLetter)) {
    passwordValidation.innerHTML = "Must contain letters";
    loginBtn.disabled = true;
  } else if (!password.match(passwordSpecial)) {
    passwordValidation.innerHTML =
      "Must contain at least one special character";
    loginBtn.disabled = true;
  } else if (!password.match(passwordNumber)) {
    passwordValidation.innerHTML = "Must contain at least one number";
    loginBtn.disabled = true;
  } else if (password.length < 8 || password.length > 20) {
    passwordValidation.innerHTML = "Must be 8 to 20 characters";
    loginBtn.disabled = true;
  } else {
    passwordValidation.innerHTML = "";
    loginBtn.disabled = false;
  }
}
