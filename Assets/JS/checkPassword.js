function checkPassword() {
  const password = document.getElementById("password").value;
  const passwordValidation = document.getElementById("passwordValidation");
  const passwordLetter = /[a-zA-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;
  if (password === "") {
    passwordValidation.innerHTML = "Please enter your password!";
  } else if (!password.match(passwordLetter)) {
    passwordValidation.innerHTML = "Must contain letters";
  } else if (!password.match(passwordSpecial)) {
    passwordValidation.innerHTML =
      "Must contain at least one special character";
  } else if (!password.match(passwordNumber)) {
    passwordValidation.innerHTML = "Must contain at least one number";
  } else if (password.length < 8 || password.length > 20) {
    passwordValidation.innerHTML = "Must be 8 to 20 characters";
  } else {
    passwordValidation.innerHTML = "";
  }
}

function checkPasswordModal() {
  const newPassword = document.getElementById("newPassword").value;
  const passwordValidation = document.getElementById("passwordValidation");
  const passwordLetter = /[a-zA-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;
  if (newPassword === "") {
    passwordValidation.innerHTML = "Please enter your password!";
  } else if (!newPassword.match(passwordLetter)) {
    passwordValidation.innerHTML = "Must contain letters";
  } else if (!newPassword.match(passwordSpecial)) {
    passwordValidation.innerHTML =
      "Must contain at least one special character";
  } else if (!newPassword.match(passwordNumber)) {
    passwordValidation.innerHTML = "Must contain at least one number";
  } else if (newPassword.length < 8 || newPassword.length > 20) {
    passwordValidation.innerHTML = "Must be 8 to 20 characters";
  } else {
    passwordValidation.innerHTML = "";
  }
}
