document.addEventListener("DOMContentLoaded", () => {
    // Get all sections
    const customerLogin = document.getElementById("customer-login");
    const adminLogin = document.getElementById("admin-login");
    const registerSection = document.getElementById("register-section");

    // function to hide all forms
    function hideAllForms() {
        customerLogin.classList.add("hidden");
        adminLogin.classList.add("hidden");
        registerSection.classList.add("hidden");
    }

    // 1. Click Create Account to Show Register
    document.getElementById("link-to-register").addEventListener("click", (e) => {
        //preventDefault is to stop the page to move to another page 
        e.preventDefault();
        //use the function to hide all the forms
        hideAllForms();
        //after hide everything this code remove the hidden
        registerSection.classList.remove("hidden");
    });

    // 2. Click Log In as Admin to Show Admin Login
    document.getElementById("link-to-admin").addEventListener("click", (e) => {
        e.preventDefault();
        hideAllForms();
        adminLogin.classList.remove("hidden");
    });

    // 3. Click "Back to Customer Login" (from Admin)
    document.getElementById("link-to-customer").addEventListener("click", (e) => {
        e.preventDefault();
        hideAllForms();
        customerLogin.classList.remove("hidden");
    });

    // 4. Click "Back to Log In" (from Register)
    document.getElementById("link-to-login").addEventListener("click", (e) => {
        e.preventDefault();
        hideAllForms();
        customerLogin.classList.remove("hidden");
    });
});