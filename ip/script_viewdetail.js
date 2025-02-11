let isAvailable = true;

function toggleStatus() {
    isAvailable = !isAvailable;
    const statusElement = document.getElementById('status');
    const toggleButton = document.getElementById('toggleButton');
    const statusText = statusElement.querySelector('.status-text');

    if (isAvailable) {
        // Change to Available state
        statusElement.classList.remove('unavailable');
        statusElement.classList.add('available');
        statusText.textContent = 'Available';
        toggleButton.textContent = 'Disable';
        toggleButton.classList.remove('activate-button');
        toggleButton.classList.add('disable-button');
    } else {
        // Change to Unavailable state
        statusElement.classList.remove('available');
        statusElement.classList.add('unavailable');
        statusText.textContent = 'Unavailable';
        toggleButton.textContent = 'Activate';
        toggleButton.classList.remove('disable-button');
        toggleButton.classList.add('activate-button');
    }
}