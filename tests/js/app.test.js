const fs = require('fs');
const path = require('path');
const { JSDOM } = require('jsdom');

const appJsCode = fs.readFileSync(path.resolve(__dirname, '../../public/js/app.js'), 'utf8');

describe('validateForm', () => {
    let window;
    let document;

    beforeEach(() => {
        const dom = new JSDOM(`
            <!DOCTYPE html>
            <html>
            <head></head>
            <body>
                <form id="testForm">
                    <input type="text" id="testInput" required>
                    <button type="submit">Submit</button>
                </form>
            </body>
            </html>
        `, { runScripts: 'dangerously' });
        window = dom.window;
        document = window.document;

        // Mock preventDefault and stopPropagation on global event
        window.event = {
            preventDefault: jest.fn(),
            stopPropagation: jest.fn()
        };

        // Add the script to the dom
        const script = document.createElement('script');
        script.textContent = appJsCode;
        document.body.appendChild(script);
    });

    it('should add was-validated class to valid form', () => {
        const form = document.getElementById('testForm');
        const input = document.getElementById('testInput');
        input.value = 'test';

        // Ensure form is valid
        expect(form.checkValidity()).toBe(true);

        window.validateForm('testForm');

        expect(form.classList.contains('was-validated')).toBe(true);
        expect(window.event.preventDefault).not.toHaveBeenCalled();
        expect(window.event.stopPropagation).not.toHaveBeenCalled();
    });

    it('should prevent default and add was-validated class to invalid form', () => {
        const form = document.getElementById('testForm');

        // Ensure form is invalid (input is required but empty)
        expect(form.checkValidity()).toBe(false);

        window.validateForm('testForm');

        expect(form.classList.contains('was-validated')).toBe(true);
        expect(window.event.preventDefault).toHaveBeenCalled();
        expect(window.event.stopPropagation).toHaveBeenCalled();
    });
});
