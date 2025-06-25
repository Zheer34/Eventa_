const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');
const roleSelect = document.getElementById('role');
const organizerFields = document.getElementById('organizer-fields');

// Toggle between login and register forms
registerBtn.addEventListener('click', () => {
    container.classList.add('active');
});

loginBtn.addEventListener('click', () => {
    container.classList.remove('active');
});

// Show/hide organizer-specific fields
function toggleOrganizerFields() {
    if (roleSelect.value === 'event_organizer') {
        organizerFields.style.display = 'block';
    } else {
        organizerFields.style.display = 'none';
    }
}