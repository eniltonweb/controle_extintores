const fs = require('fs');
const path = require('path');

describe('validateForm', () => {
    let mockEvent;

    beforeAll(() => {
        const jsCode = fs.readFileSync(path.resolve(__dirname, '../../public/js/app.js'), 'utf8');
        const script = document.createElement('script');
        script.textContent = jsCode;
        document.body.appendChild(script);
    });

    beforeEach(() => {
        document.body.innerHTML = `
            <form id="testForm">
                <input type="text" id="testInput" required>
                <button type="submit">Submit</button>
            </form>
        `;

        mockEvent = {
            preventDefault: jest.fn(),
            stopPropagation: jest.fn()
        };

        window.event = mockEvent;
    });

    test('should prevent default and stop propagation when form is invalid', () => {
        const form = document.getElementById('testForm');
        window.validateForm('testForm');
        expect(mockEvent.preventDefault).toHaveBeenCalled();
        expect(mockEvent.stopPropagation).toHaveBeenCalled();
        expect(form.classList.contains('was-validated')).toBe(true);
    });

    test('should not prevent default when form is valid', () => {
        const form = document.getElementById('testForm');
        const input = document.getElementById('testInput');
        input.value = 'test';
        window.validateForm('testForm');
        expect(mockEvent.preventDefault).not.toHaveBeenCalled();
        expect(mockEvent.stopPropagation).not.toHaveBeenCalled();
        expect(form.classList.contains('was-validated')).toBe(true);
    });
});
