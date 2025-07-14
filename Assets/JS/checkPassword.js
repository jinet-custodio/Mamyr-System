function checkPassword() {
  const password = document.getElementById("password").value;
  const passwordValidation = document.getElementById("passwordValidation");
    const signUpButton = document.getElementById("signUp");
  const passwordLetter = /[a-zA-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;
  if (password === "") {
    passwordValidation.innerHTML = "Please enter your password!";
     signUpButton.disabled = true;
  } else if (!password.match(passwordLetter)) {
    passwordValidation.innerHTML = "Must contain letters";
     signUpButton.disabled = true;
  } else if (!password.match(passwordSpecial)) {
    passwordValidation.innerHTML =
      "Must contain at least one special character";
     signUpButton.disabled = true;
  } else if (!password.match(passwordNumber)) {
    passwordValidation.innerHTML = "Must contain at least one number";
     signUpButton.disabled = true;
  } else if (password.length < 8 || password.length > 20) {
    passwordValidation.innerHTML = "Must be 8 to 20 characters";
     signUpButton.disabled = true;
  } else {
    passwordValidation.innerHTML = "";
     signUpButton.disabled = false;
  }
}

function checkPasswordModal() {
  const newPassword = document.getElementById("newPassword").value;
  const passwordValidation = document.getElementById("passwordValidation");
  const changePasswordBtn = document.getElementById("changePassword");
  const passwordLetter = /[a-zA-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;
  if (newPassword === "") {
    passwordValidation.innerHTML = "Please enter your password!";
     changePasswordBtn.disabled = true;
  } else if (!newPassword.match(passwordLetter)) {
    passwordValidation.innerHTML = "Must contain letters";
     changePasswordBtn.disabled = true;
  } else if (!newPassword.match(passwordSpecial)) {
    passwordValidation.innerHTML =
      "Must contain at least one special character";
     changePasswordBtn.disabled = true;
  } else if (!newPassword.match(passwordNumber)) {
    passwordValidation.innerHTML = "Must contain at least one number";
     changePasswordBtn.disabled = true;
  } else if (newPassword.length < 8 || newPassword.length > 20) {
    passwordValidation.innerHTML = "Must be 8 to 20 characters";
     changePasswordBtn.disabled = true;
  } else {
    passwordValidation.innerHTML = "";
    changePasswordBtn.disabled = false;
  }
}
