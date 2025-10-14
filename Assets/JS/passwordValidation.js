//Password Validation
function passwordValidation(password, passwordMessage) {
  const passwordLetter = /[a-zA-Z]/;
  const passwordNumber = /[0-9]/;
  const passwordSpecial = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;

  let passwordIsValid = true;
  if (password === "") {
    passwordMessage.textContent = "";
    passwordIsValid = false;
  } else if (!password.match(passwordLetter)) {
    passwordMessage.textContent = "Must contain letters";
    passwordIsValid = false;
  } else if (!password.match(passwordSpecial)) {
    passwordMessage.textContent = "Must contain at least one special character";
    passwordIsValid = false;
  } else if (!password.match(passwordNumber)) {
    passwordMessage.textContent = "Must contain at least one number";
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

function checkLoginPassword() {
  const password = document.getElementById("login_password").value;
  const passwordMessage = document.getElementById("passwordLValidation");
  const loginButton = document.getElementById("login");

  const isValid = passwordValidation(password, passwordMessage);

  if (isValid) {
    loginButton.disabled = false;
  }
}

function validateSignUpForm() {
  const password = document.getElementById("password").value;
  const confirm_password = document.getElementById("confirm_password").value;

  const passwordValidationMessage =
    document.getElementById("passwordValidation");
  const passwordMatchMessage = document.getElementById("passwordMatch");
  const termsErrorMessage = document.getElementById("termsError");

  const checkbox = document.getElementById("terms-condition");
  const signUpButton = document.getElementById("signUp");

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

  if (
    isCheckboxChecked === false &&
    isPasswordValid === true &&
    isPasswordMatch === true
  ) {
    termsErrorMessage.innerHTML = "Please agree to the terms and conditions!";
    signUpButton.disabled = true;
  } else {
    signUpButton.disabled = !(
      isPasswordValid &&
      isPasswordMatch &&
      isCheckboxChecked
    );

    // console.log(
    //   "checkBox " + isCheckboxChecked,
    //   "match " + isPasswordMatch,
    //   "pass " + isPasswordValid
    // );
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

  // Get or create a Bootstrap modal instance
  const modalInstance =
    bootstrap.Modal.getInstance(termsModal) || new bootstrap.Modal(termsModal);

  // 🔹 Move focus out of modal before hiding to avoid aria-hidden warnings
  document.activeElement?.blur();

  // 🔹 Hide modal normally — don't dispose yet
  modalInstance.hide();

  // 🔹 Wait until Bootstrap fully hides the modal
  termsModal.addEventListener(
    "hidden.bs.modal",
    () => {
      // Revalidate form after modal is truly hidden
      validateSignUpForm?.();

      // Return focus somewhere meaningful
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
