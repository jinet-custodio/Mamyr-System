function checkLoginPassword() {
  const password = document.getElementById("login_password").value;
  const passwordValidation = document.getElementById("passwordLValidation");
        const loginBtn = document.getElementById("login");
        const passwordLetter = /[a-zA-Z]/;
        const passwordNumber = /[0-9]/;
        const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;

        let passwordIsValid = true; 
        if (password === "") {
            passwordValidation.textContent = "Please enter your password!";
             passwordIsValid = false; 
        } else if (!password.match(passwordLetter)) {
            passwordValidation.textContent = "Must contain letters";
              passwordIsValid = false; 
        } else if (!password.match(passwordSpecial)) {
            passwordValidation.textContent = "Must contain at least one special character";
             passwordIsValid = false; 
        } else if (!password.match(passwordNumber)) {
            passwordValidation.textContent = "Must contain at least one number";
             passwordIsValid = false; 
        } else if (password.length < 8 || password.length > 20) {
            passwordValidation.textContent = "Must be 8 to 20 characters";
             passwordIsValid = false; 
        } else {
            passwordValidation.textContent = "";
            passwordIsValid = true; 
        }

        if (passwordIsValid === true) {
            loginBtn.disabled = false;
        } else {
            loginBtn.disabled = true;
        }
}
