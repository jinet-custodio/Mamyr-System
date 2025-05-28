function checkPasswordMatch() {
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm_password").value;
  const passwordMatchMessage = document.getElementById("passwordMatch");
  // const signUpButton = document.getElementById("signUp");
  if (confirmPassword === "") {
    passwordMatchMessage.innerHTML = "";
  } else if (password !== confirmPassword) {
    passwordMatchMessage.innerHTML = "Passwords do not match!";
  } else {
    passwordMatchMessage.innerHTML = "";
  }
}
// this is the code to check if the password and confirm password ay same lang then pag oo makaka sign up pag hindi yung button ng sign up is hindi pwede iclick
