//Password Validation
function passwordValidation(password, passwordMessage) {
  const passwordLower = /[a-z]/;
  const passwordUpper = /[A-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;

  let passwordIsValid = true;
  if (password === "") {
    passwordMessage.textContent = "";
    passwordIsValid = false;
  } else if (!password.match(passwordLower)) {
    passwordMessage.textContent = "Must contain at least one lowercase letter";
    passwordIsValid = false;
  } else if (!password.match(passwordUpper)) {
    passwordMessage.textContent = "Must contain at least one uppercase letter";
    passwordIsValid = false;
  } else if (!password.match(passwordNumber)) {
    passwordMessage.textContent = "Must contain at least one number";
    passwordIsValid = false;
  } else if (!password.match(passwordSpecial)) {
    passwordMessage.textContent = "Must contain at least one special character";
    passwordIsValid = false;
  } else if (password.length < 8 || password.length > 20) {
    passwordMessage.textContent = "Must be 8 to 20 characters";
    passwordIsValid = false;
  } else {
    passwordMessage.textContent = "";
    passwordIsValid = true;
  }

  if (passwordIsValid === true) {
    return true;
  } else {
    return false;
  }
}

//Confirm Password Validation
function passwordMatchValidation(password, confirmPassword, passwordMessage) {
  let passwordMatch = true;
  if (confirmPassword === "") {
    passwordMessage.innerHTML = "";
    passwordMatch = false;
  } else if (confirmPassword !== password) {
    passwordMessage.innerHTML = "Passwords do not match!";
    passwordMatch = false;
  } else {
    passwordMessage.innerHTML = "";
    passwordMatch = true;
  }

  if (passwordMatch === true) {
    return true;
  } else {
    return false;
  }
}

//Check Box
function checkboxChecker(checkbox, message) {
  if (checkbox.checked) {
    message.innerHTML = "";
    return true;
  } else {
    // message.innerHTML = "Please agree to the terms and conditions!";
    message.innerHTML = "";
    return false;
  }
}

// function checkLoginPassword() {
//   const password = document.getElementById("login_password").value;
//   const passwordMessage = document.getElementById("passwordLValidation");
//   const loginButton = document.getElementById("login");

//   const isValid = passwordValidation(password, passwordMessage);

//   if (isValid) {
//     loginButton.disabled = false;
//   }
// }

function validateSignUpForm() {
  const password = document.getElementById("password").value;
  const confirm_password = document.getElementById("confirm_password").value;

  const passwordValidationMessage =
    document.getElementById("passwordValidation");
  const passwordMatchMessage = document.getElementById("passwordMatch");
  const termsErrorMessage = document.getElementById("termsError");

  const checkbox = document.getElementById("terms-condition");

  const isPasswordValid = passwordValidation(
    password,
    passwordValidationMessage
  );
  const isPasswordMatch = passwordMatchValidation(
    password,
    confirm_password,
    passwordMatchMessage
  );
  const isCheckboxChecked = checkboxChecker(checkbox, termsErrorMessage);

  if (isPasswordValid !== true) {
    return "password";
  } else if (isPasswordMatch !== true) {
    return "confirm";
  } else if (isCheckboxChecked === false) {
    return "terms";
  }
}

function isValid(event) {
  const validated = validateSignUpForm();
  // ! added this because it still shows pw validation even though all fields are empty
  const firstName = document.getElementById("firstName");
  const lastName = document.getElementById("lastName");
  const userAddress = document.getElementById("userAddress");
  const email = document.getElementById("email");
  const password = document.getElementById("password");

  if (
    (firstName !== "" ||
      lastName !== "" ||
      userAddress !== "" ||
      email !== "") &&
    password !== ""
  ) {
    console.log("Trulalu");
    switch (validated) {
      case "password":
        event.preventDefault();

        Swal.fire({
          icon: "warning",
          title: "Oops!",
          text: "Your password must include at least one uppercase letter, one lowercase letter, one number, and one special character!",
          confirmButtonText: "OK",
        });
        break;
      case "confirm":
        event.preventDefault();

        Swal.fire({
          icon: "warning",
          title: "Oops!",
          text: "Your passwords don’t match. Please make sure both fields are the same!",
          confirmButtonText: "OK",
        });
        break;
      case "terms":
        event.preventDefault();

        Swal.fire({
          icon: "warning",
          title: "Oops!",
          text: "You must agree to the terms and conditions before continuing!",
          confirmButtonText: "OK",
        }).then(() => {
          const termsModal = document.getElementById("termsModal");
          const modal = new bootstrap.Modal(termsModal);
          modal.show();
        });
        break;
    }
  }
}

function changePasswordValidation() {
  const newPassword = document.getElementById("newPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;
  const passwordMessage = document.getElementById("passwordValidation");
  const passwordMatchMessage = document.getElementById("passwordMatch");
  const changePasswordBtn = document.getElementById("changePassword");

  const isValid = passwordValidation(newPassword, passwordMessage);
  const isMatch = passwordMatchValidation(
    newPassword,
    confirmPassword,
    passwordMatchMessage
  );

  if (isValid === true && isMatch === true) {
    changePasswordBtn.disabled = false;
  }
}

function checkCreateAccountPassword() {
  const password = document.getElementById("password").value;
  const passwordMessage = document.getElementById("passwordValidation");
  const createAccountBtn = document.getElementById("createAccount");

  const isValid = passwordValidation(password, passwordMessage);

  if (isValid) {
    createAccountBtn.disabled = false;
  }
}

function handleTerms(accepted) {
  const termsCheckbox = document.getElementById("terms-condition");
  const termsModal = document.getElementById("termsModal");

  if (termsCheckbox) {
    termsCheckbox.checked = accepted;
  }
  const modalInstance =
    bootstrap.Modal.getInstance(termsModal) || new bootstrap.Modal(termsModal);

  document.activeElement?.blur();

  modalInstance.hide();

  termsModal.addEventListener(
    "hidden.bs.modal",
    () => {
      validateSignUpForm?.();

      document.querySelector("#terms-condition")?.focus();
    },
    { once: true }
  );
}

function AcceptTerms() {
  handleTerms(true);
}

function declineTerms() {
  handleTerms(false);
}
