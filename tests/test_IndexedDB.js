const fs = require('fs');
const vm = require('vm');
const assert = require('assert');

async function runTests() {
    let fetchArgs = null;
    let fetchResponse = null;
    let fetchError = null;
    let dbClearCalled = false;
    let consoleLogs = [];
    let consoleErrors = [];

    function createContext(mockDbData) {
        dbClearCalled = false;
        fetchArgs = null;
        consoleLogs = [];
        consoleErrors = [];

        const context = {
            window: { addEventListener: () => {} },
            navigator: { onLine: true },
            indexedDB: { open: () => ({}) },
            console: {
                log: (msg) => consoleLogs.push(msg),
                error: (msg) => consoleErrors.push(msg)
            },
            fetch: function(url, options) {
                fetchArgs = { url, options };
                if (fetchError) return Promise.reject(fetchError);
                return Promise.resolve({
                    json: () => Promise.resolve(fetchResponse)
                });
            },
            setTimeout: setTimeout
        };
        vm.createContext(context);

        const code = fs.readFileSync('public/js/IndexedDB.js', 'utf8');
        vm.runInContext(code, context);

        vm.runInContext(`
            request.onsuccess({
                target: {
                    result: {
                        transaction: () => {
                            return {
                                objectStore: () => {
                                    return {
                                        getAll: () => {
                                            const req = {};
                                            setTimeout(() => {
                                                if (req.onsuccess) req.onsuccess({ target: { result: ${JSON.stringify(mockDbData)} } });
                                            }, 0);
                                            return req;
                                        },
                                        clear: () => {
                                            if (typeof onDbClear === 'function') onDbClear();
                                        }
                                    };
                                }
                            };
                        }
                    }
                }
            });
        `, context);

        context.onDbClear = () => { dbClearCalled = true; };

        return context;
    }

    console.log("Running IndexedDB tests...");

    // Test 1: Successful sync
    let context = createContext([{ id: 1, info: "test_data" }]);
    fetchResponse = { success: true };
    fetchError = null;

    vm.runInContext('syncData();', context);
    await new Promise(r => setTimeout(r, 50));

    assert.ok(fetchArgs, "Fetch should be called");
    assert.strictEqual(fetchArgs.url, '/salvar_inspecao.php');
    assert.strictEqual(JSON.parse(fetchArgs.options.body)[0].id, 1);
    assert.ok(dbClearCalled, "DB clear should be called on success");
    assert.ok(consoleLogs.includes('Sincronização bem-sucedida e dados locais limpos.'));
    console.log("✓ Test 1 passed: Successful sync");

    // Test 2: Server failure
    context = createContext([{ id: 2, info: "test_data_2" }]);
    fetchResponse = { success: false };
    fetchError = null;

    vm.runInContext('syncData();', context);
    await new Promise(r => setTimeout(r, 50));

    assert.ok(fetchArgs, "Fetch should be called");
    assert.strictEqual(dbClearCalled, false, "DB clear should NOT be called on server error");
    assert.ok(consoleErrors.includes('Falha ao sincronizar dados com o servidor.'));
    console.log("✓ Test 2 passed: Server failure handled");

    // Test 3: Network error (fetch exception)
    context = createContext([{ id: 3, info: "test_data_3" }]);
    fetchError = new Error("Network offline");

    vm.runInContext('syncData();', context);
    await new Promise(r => setTimeout(r, 50));

    assert.ok(fetchArgs, "Fetch should be called");
    assert.strictEqual(dbClearCalled, false, "DB clear should NOT be called on network error");
    assert.ok(consoleErrors.some(msg => msg === 'Erro na sincronização:'));
    console.log("✓ Test 3 passed: Network error handled");

    // Test 4: Empty DB
    context = createContext([]);

    vm.runInContext('syncData();', context);
    await new Promise(r => setTimeout(r, 50));

    assert.strictEqual(fetchArgs, null, "Fetch should NOT be called if empty");
    assert.strictEqual(dbClearCalled, false, "DB clear should NOT be called if empty");
    console.log("✓ Test 4 passed: Empty DB handled");

    console.log("All IndexedDB tests passed successfully!");
}

runTests().catch(err => {
    console.error("Test failed:", err);
    process.exit(1);
});
