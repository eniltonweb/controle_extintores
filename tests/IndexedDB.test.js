const fs = require('fs');
const path = require('path');

describe('saveInspectionOffline', () => {
    let mockAddRequest;
    let mockObjectStore;
    let mockTransaction;
    let mockDB;
    let exposed;
    let consoleLogMock;
    let consoleErrorMock;

    beforeEach(() => {
        consoleLogMock = jest.fn();
        consoleErrorMock = jest.fn();

        mockAddRequest = { onsuccess: null, onerror: null };
        mockObjectStore = { add: jest.fn().mockReturnValue(mockAddRequest) };
        mockTransaction = { objectStore: jest.fn().mockReturnValue(mockObjectStore) };
        mockDB = { transaction: jest.fn().mockReturnValue(mockTransaction) };

        const consoleMock = { log: consoleLogMock, error: consoleErrorMock };
        const windowMock = { addEventListener: jest.fn() };
        const navigatorMock = { onLine: true };
        const indexedDBMock = { open: jest.fn().mockReturnValue({ onerror: null, onsuccess: null, onupgradeneeded: null }) };

        const code = fs.readFileSync(path.resolve(__dirname, '../public/js/IndexedDB.js'), 'utf8');
        const modifiedCode = code.replace('let db;', 'var db;');

        const scriptFunc = new Function('window', 'navigator', 'indexedDB', 'console', 'fetch', modifiedCode + ';\nreturn { saveInspectionOffline: saveInspectionOffline, setDB: function(newDB) { db = newDB; } };');
        exposed = scriptFunc(windowMock, navigatorMock, indexedDBMock, consoleMock, jest.fn());
    });

    afterEach(() => { jest.restoreAllMocks(); });

    it('should save data when db is available', () => {
        exposed.setDB(mockDB);
        const testData = { id: 1, test: "data" };
        exposed.saveInspectionOffline(testData);

        expect(mockDB.transaction).toHaveBeenCalledWith(["inspections"], "readwrite");
        expect(mockTransaction.objectStore).toHaveBeenCalledWith("inspections");
        expect(mockObjectStore.add).toHaveBeenCalledWith(testData);

        mockAddRequest.onsuccess();
        expect(consoleLogMock).toHaveBeenCalledWith("Inspeção salva localmente.");
    });

    it('should log error when add operation fails', () => {
        exposed.setDB(mockDB);
        const testData = { id: 2, test: "error_data" };
        exposed.saveInspectionOffline(testData);

        const mockEvent = { target: { errorCode: "MockError" } };
        mockAddRequest.onerror(mockEvent);
        expect(consoleErrorMock).toHaveBeenCalledWith("Erro ao salvar inspeção localmente:", "MockError");
    });

    it('should log error when db is not available', () => {
        exposed.setDB(null);
        const testData = { id: 3, test: "no_db" };
        exposed.saveInspectionOffline(testData);

        expect(consoleErrorMock).toHaveBeenCalledWith("Banco de dados não está disponível.");
    });
});
