document.getElementById("registrationForm").addEventListener("submit", function(event) {
    event.preventDefault();
    
    const firstName = document.getElementById("firstName");
    const lastName = document.getElementById("floatingLastName");
    const email = document.getElementById("floatingEmail");
    const password = document.getElementById("floatingPassword");
    const confirmPassword = document.getElementById("floatingConfirmPassword");
    const agreeTerms = document.getElementById("agreeTerms");
    const errorMessage = document.getElementById("errorMessage");

    errorMessage.textContent = "";
    [firstName, lastName, email, password, confirmPassword].forEach(field => field.classList.remove("border-danger"));

    if (!firstName.value.trim() || !lastName.value.trim() || !email.value.trim() || !password.value.trim() || !confirmPassword.value.trim()) {
      errorMessage.textContent = "Proszę wypełnić wszystkie pola.";
      [firstName, lastName, email, password, confirmPassword].forEach(field => {
        if (!field.value.trim()) field.classList.add("border-danger");
      });
      return;
    }

    if (password.value !== confirmPassword.value) {
      errorMessage.textContent = "Hasła nie są zgodne.";
      password.classList.add("border-danger");
      confirmPassword.classList.add("border-danger");
      return;
    }

    if (!agreeTerms.checked) {
      errorMessage.textContent = "Musisz zaakceptować regulamin, aby kontynuować.";
      return;
    }

    errorMessage.textContent = "Rejestracja zakończona pomyślnie!";
    errorMessage.classList.remove("text-danger");
    errorMessage.classList.add("text-success");
    this.submit();
  });