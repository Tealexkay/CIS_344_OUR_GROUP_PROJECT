document.addEventListener('DOMContentLoaded', () => {
    const numericInputs = document.querySelectorAll('input[type="number"]');

    numericInputs.forEach((input) => {
        input.addEventListener('input', () => {
            if (Number(input.value) < 0) {
                input.value = '';
            }
        });
    });
});
