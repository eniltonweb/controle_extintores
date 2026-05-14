const { JSDOM } = require('jsdom');
const assert = require('assert');
const fs = require('fs');

const code = fs.readFileSync('public/js/app.js', 'utf8');

const dom = new JSDOM(`
<!DOCTYPE html>
<html>
<body>
    <div id="alertPlaceholder"></div>
    <script>
        ${code}
    </script>
</body>
</html>
`, { runScripts: "dangerously" });

const window = dom.window;

// Execute showNotification
window.showNotification('Test message', 'success');

// Validate
const placeholder = window.document.getElementById('alertPlaceholder');
assert.strictEqual(placeholder.children.length, 1, 'One notification should be added');
const alertDiv = placeholder.children[0].children[0];
assert.ok(alertDiv.classList.contains('alert'), 'Should have alert class');
assert.ok(alertDiv.classList.contains('alert-success'), 'Should have alert-success class');
assert.ok(alertDiv.innerHTML.includes('Test message'), 'Should contain the message text');

window.showNotification('Another message');
assert.strictEqual(placeholder.children.length, 2, 'Another notification should be added');
const alertDiv2 = placeholder.children[1].children[0];
assert.ok(alertDiv2.classList.contains('alert-info'), 'Default type should be info');

console.log('All JS tests passed.');
